/*
   +----------------------------------------------------------------------+
   | Copyright (c) The PHP Group                                          |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | https://www.php.net/license/3_01.txt                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Shane Caraveo <shane@php.net>                               |
   |          Wez Furlong <wez@thebrainroom.com>                          |
   +----------------------------------------------------------------------+
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "SAPI.h"

#include "zend_variables.h"
#include "ext/standard/php_string.h"
#include "ext/standard/info.h"
#include "ext/standard/file.h"

#ifdef HAVE_LIBXML

#include <libxml/parser.h>
#include <libxml/parserInternals.h>
#include <libxml/tree.h>
#include <libxml/uri.h>
#include <libxml/xmlerror.h>
#include <libxml/xmlsave.h>
#ifdef LIBXML_SCHEMAS_ENABLED
#include <libxml/relaxng.h>
#include <libxml/xmlschemas.h>
#endif

#include "php_libxml.h"

#define PHP_LIBXML_LOADED_VERSION ((char *)xmlParserVersion)
#define PHP_LIBXML_ERROR 0
#define PHP_LIBXML_CTX_ERROR 1
#define PHP_LIBXML_CTX_WARNING 2

#include "libxml_arginfo.h"

/* a true global for initialization */
static int _php_libxml_initialized = 0;
static int _php_libxml_per_request_initialization = 1;
static xmlExternalEntityLoader _php_libxml_default_entity_loader;

typedef struct _php_libxml_func_handler {
	php_libxml_export_node export_func;
} php_libxml_func_handler;

static HashTable php_libxml_exports;

static ZEND_DECLARE_MODULE_GLOBALS(libxml)
static PHP_GINIT_FUNCTION(libxml);

static zend_class_entry *libxmlerror_class_entry;

/* {{{ dynamically loadable module stuff */
#ifdef COMPILE_DL_LIBXML
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif
ZEND_GET_MODULE(libxml)
#endif /* COMPILE_DL_LIBXML */
/* }}} */

/* {{{ function prototypes */
static PHP_MINIT_FUNCTION(libxml);
static PHP_RINIT_FUNCTION(libxml);
static PHP_RSHUTDOWN_FUNCTION(libxml);
static PHP_MSHUTDOWN_FUNCTION(libxml);
static PHP_MINFO_FUNCTION(libxml);
static zend_result php_libxml_post_deactivate(void);

/* }}} */

zend_module_entry libxml_module_entry = {
	STANDARD_MODULE_HEADER,
	"libxml",                /* extension name */
	ext_functions,           /* extension function list */
	PHP_MINIT(libxml),       /* extension-wide startup function */
	PHP_MSHUTDOWN(libxml),   /* extension-wide shutdown function */
	PHP_RINIT(libxml),       /* per-request startup function */
	PHP_RSHUTDOWN(libxml),   /* per-request shutdown function */
	PHP_MINFO(libxml),       /* information function */
	PHP_LIBXML_VERSION,
	PHP_MODULE_GLOBALS(libxml), /* globals descriptor */
	PHP_GINIT(libxml),          /* globals ctor */
	NULL,                       /* globals dtor */
	php_libxml_post_deactivate, /* post deactivate */
	STANDARD_MODULE_PROPERTIES_EX
};

/* }}} */

/* {{{ internal functions for interoperability */
static int php_libxml_clear_object(php_libxml_node_object *object)
{
	if (object->properties) {
		object->properties = NULL;
	}
	php_libxml_decrement_node_ptr(object);
	return php_libxml_decrement_doc_ref(object);
}

static int php_libxml_unregister_node(xmlNodePtr nodep)
{
	php_libxml_node_object *wrapper;

	php_libxml_node_ptr *nodeptr = nodep->_private;

	if (nodeptr != NULL) {
		wrapper = nodeptr->_private;
		if (wrapper) {
			php_libxml_clear_object(wrapper);
		} else {
			if (nodeptr->node != NULL && nodeptr->node->type != XML_DOCUMENT_NODE) {
				nodeptr->node->_private = NULL;
			}
			nodeptr->node = NULL;
		}
	}

	return -1;
}

static void php_libxml_node_free(xmlNodePtr node)
{
	if(node) {
		if (node->_private != NULL) {
			((php_libxml_node_ptr *) node->_private)->node = NULL;
		}
		switch (node->type) {
			case XML_ATTRIBUTE_NODE:
				xmlFreeProp((xmlAttrPtr) node);
				break;
			case XML_ENTITY_DECL:
			case XML_ELEMENT_DECL:
			case XML_ATTRIBUTE_DECL:
				break;
			case XML_NOTATION_NODE:
				/* These require special handling */
				if (node->name != NULL) {
					xmlFree((char *) node->name);
				}
				if (((xmlEntityPtr) node)->ExternalID != NULL) {
					xmlFree((char *) ((xmlEntityPtr) node)->ExternalID);
				}
				if (((xmlEntityPtr) node)->SystemID != NULL) {
					xmlFree((char *) ((xmlEntityPtr) node)->SystemID);
				}
				xmlFree(node);
				break;
			case XML_NAMESPACE_DECL:
				if (node->ns) {
					xmlFreeNs(node->ns);
					node->ns = NULL;
				}
				node->type = XML_ELEMENT_NODE;
				ZEND_FALLTHROUGH;
			default:
				xmlFreeNode(node);
		}
	}
}

PHP_LIBXML_API void php_libxml_node_free_list(xmlNodePtr node)
{
	xmlNodePtr curnode;

	if (node != NULL) {
		curnode = node;
		while (curnode != NULL) {
			node = curnode;
			switch (node->type) {
				/* Skip property freeing for the following types */
				case XML_NOTATION_NODE:
				case XML_ENTITY_DECL:
					break;
				case XML_ENTITY_REF_NODE:
					php_libxml_node_free_list((xmlNodePtr) node->properties);
					break;
				case XML_ATTRIBUTE_NODE:
					if ((node->doc != NULL) && (((xmlAttrPtr) node)->atype == XML_ATTRIBUTE_ID)) {
						xmlRemoveID(node->doc, (xmlAttrPtr) node);
					}
					ZEND_FALLTHROUGH;
				case XML_ATTRIBUTE_DECL:
				case XML_DTD_NODE:
				case XML_DOCUMENT_TYPE_NODE:
				case XML_NAMESPACE_DECL:
				case XML_TEXT_NODE:
					php_libxml_node_free_list(node->children);
					break;
				default:
					php_libxml_node_free_list(node->children);
					php_libxml_node_free_list((xmlNodePtr) node->properties);
			}

			curnode = node->next;
			xmlUnlinkNode(node);
			if (php_libxml_unregister_node(node) == 0) {
				node->doc = NULL;
			}
			php_libxml_node_free(node);
		}
	}
}

/* }}} */

/* {{{ startup, shutdown and info functions */
static PHP_GINIT_FUNCTION(libxml)
{
#if defined(COMPILE_DL_LIBXML) && defined(ZTS)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif
	ZVAL_UNDEF(&libxml_globals->stream_context);
	libxml_globals->error_buffer.s = NULL;
	libxml_globals->error_list = NULL;
	ZVAL_NULL(&libxml_globals->entity_loader.callback);
	libxml_globals->entity_loader.fci.size = 0;
	libxml_globals->entity_loader_disabled = 0;
}

/* Channel libxml file io layer through the PHP streams subsystem.
 * This allows use of ftps:// and https:// urls */

static void *php_libxml_streams_IO_open_wrapper(const char *filename, const char *mode, const int read_only)
{
	php_stream_statbuf ssbuf;
	php_stream_context *context = NULL;
	php_stream_wrapper *wrapper = NULL;
	char *resolved_path;
	const char *path_to_open = NULL;
	void *ret_val = NULL;
	int isescaped=0;
	xmlURI *uri;

	if (strstr(filename, "%00")) {
		php_error_docref(NULL, E_WARNING, "URI must not contain percent-encoded NUL bytes");
		return NULL;
	}

	uri = xmlParseURI(filename);
	if (uri && (uri->scheme == NULL ||
			(xmlStrncmp(BAD_CAST uri->scheme, BAD_CAST "file", 4) == 0))) {
		resolved_path = xmlURIUnescapeString(filename, 0, NULL);
		isescaped = 1;
#if LIBXML_VERSION >= 20902 && defined(PHP_WIN32)
		/* Libxml 2.9.2 prefixes local paths with file:/ instead of file://,
			thus the php stream wrapper will fail on a valid case. For this
			reason the prefix is rather better cut off. */
		{
			size_t pre_len = sizeof("file:/") - 1;

			if (strncasecmp(resolved_path, "file:/", pre_len) == 0
				&& '/' != resolved_path[pre_len]) {
				xmlChar *tmp = xmlStrdup(resolved_path + pre_len);
				xmlFree(resolved_path);
				resolved_path = tmp;
			}
		}
#endif
	} else {
		resolved_path = (char *)filename;
	}

	if (uri) {
		xmlFreeURI(uri);
	}

	if (resolved_path == NULL) {
		return NULL;
	}

	/* logic copied from _php_stream_stat, but we only want to fail
	   if the wrapper supports stat, otherwise, figure it out from
	   the open.  This logic is only to support hiding warnings
	   that the streams layer puts out at times, but for libxml we
	   may try to open files that don't exist, but it is not a failure
	   in xml processing (eg. DTD files)  */
	wrapper = php_stream_locate_url_wrapper(resolved_path, &path_to_open, 0);
	if (wrapper && read_only && wrapper->wops->url_stat) {
		if (wrapper->wops->url_stat(wrapper, path_to_open, PHP_STREAM_URL_STAT_QUIET, &ssbuf, NULL) == -1) {
			if (isescaped) {
				xmlFree(resolved_path);
			}
			return NULL;
		}
	}

	context = php_stream_context_from_zval(Z_ISUNDEF(LIBXML(stream_context))? NULL : &LIBXML(stream_context), 0);

	ret_val = php_stream_open_wrapper_ex(path_to_open, (char *)mode, REPORT_ERRORS, NULL, context);
	if (ret_val) {
		/* Prevent from closing this by fclose() */
		((php_stream*)ret_val)->flags |= PHP_STREAM_FLAG_NO_FCLOSE;
	}
	if (isescaped) {
		xmlFree(resolved_path);
	}
	return ret_val;
}

static void *php_libxml_streams_IO_open_read_wrapper(const char *filename)
{
	return php_libxml_streams_IO_open_wrapper(filename, "rb", 1);
}

static void *php_libxml_streams_IO_open_write_wrapper(const char *filename)
{
	return php_libxml_streams_IO_open_wrapper(filename, "wb", 0);
}

static int php_libxml_streams_IO_read(void *context, char *buffer, int len)
{
	return php_stream_read((php_stream*)context, buffer, len);
}

static int php_libxml_streams_IO_write(void *context, const char *buffer, int len)
{
	return php_stream_write((php_stream*)context, buffer, len);
}

static int php_libxml_streams_IO_close(void *context)
{
	return php_stream_close((php_stream*)context);
}

static xmlParserInputBufferPtr
php_libxml_input_buffer_create_filename(const char *URI, xmlCharEncoding enc)
{
	xmlParserInputBufferPtr ret;
	void *context = NULL;

	if (LIBXML(entity_loader_disabled)) {
		return NULL;
	}

	if (URI == NULL)
		return(NULL);

	context = php_libxml_streams_IO_open_read_wrapper(URI);

	if (context == NULL) {
		return(NULL);
	}

	/* Check if there's been an external transport protocol with an encoding information */
	if (enc == XML_CHAR_ENCODING_NONE) {
		php_stream *s  = (php_stream *) context;

		if (Z_TYPE(s->wrapperdata) == IS_ARRAY) {
			zval *header;

			/* Scan backwards: The header array might contain the headers for multiple responses, if
			 * a redirect was followed.
			 */
			ZEND_HASH_REVERSE_FOREACH_VAL_IND(Z_ARRVAL(s->wrapperdata), header) {
				const char buf[] = "Content-Type:";
				if (Z_TYPE_P(header) == IS_STRING) {
					/* If no colon is found in the header, we assume it's the HTTP status line and bail out. */
					char *colon = memchr(Z_STRVAL_P(header), ':', Z_STRLEN_P(header));
					char *space = memchr(Z_STRVAL_P(header), ' ', Z_STRLEN_P(header));
					if (colon == NULL || space < colon) {
						break;
					}

					if (!zend_binary_strncasecmp(Z_STRVAL_P(header), Z_STRLEN_P(header), buf, sizeof(buf)-1, sizeof(buf)-1)) {
						char needle[] = "charset=";
						char *haystack = estrndup(Z_STRVAL_P(header), Z_STRLEN_P(header));
						char *encoding = php_stristr(haystack, needle, Z_STRLEN_P(header), sizeof("charset=")-1);

						if (encoding) {
							char *end;

							encoding += sizeof("charset=")-1;
							if (*encoding == '"') {
								encoding++;
							}
							end = strchr(encoding, ';');
							if (end == NULL) {
								end = encoding + strlen(encoding);
							}
							end--; /* end == encoding-1 isn't a buffer underrun */
							while (*end == ' ' || *end == '\t') {
								end--;
							}
							if (*end == '"') {
								end--;
							}
							if (encoding >= end) continue;
							*(end+1) = '\0';
							enc = xmlParseCharEncoding(encoding);
							if (enc <= XML_CHAR_ENCODING_NONE) {
								enc = XML_CHAR_ENCODING_NONE;
							}
						}
						efree(haystack);
						break; /* found content-type */
					}
				}
			} ZEND_HASH_FOREACH_END();
		}
	}

	/* Allocate the Input buffer front-end. */
	ret = xmlAllocParserInputBuffer(enc);
	if (ret != NULL) {
		ret->context = context;
		ret->readcallback = php_libxml_streams_IO_read;
		ret->closecallback = php_libxml_streams_IO_close;
	} else
		php_libxml_streams_IO_close(context);

	return(ret);
}

static xmlOutputBufferPtr
php_libxml_output_buffer_create_filename(const char *URI,
                              xmlCharEncodingHandlerPtr encoder,
                              int compression)
{
	ZEND_IGNORE_VALUE(compression);

	xmlOutputBufferPtr ret;
	xmlURIPtr puri;
	void *context = NULL;
	char *unescaped = NULL;

	if (URI == NULL)
		return(NULL);

	if (strstr(URI, "%00")) {
		php_error_docref(NULL, E_WARNING, "URI must not contain percent-encoded NUL bytes");
		return NULL;
	}

	puri = xmlParseURI(URI);
	if (puri != NULL) {
		if (puri->scheme != NULL)
			unescaped = xmlURIUnescapeString(URI, 0, NULL);
		xmlFreeURI(puri);
	}

	if (unescaped != NULL) {
		context = php_libxml_streams_IO_open_write_wrapper(unescaped);
		xmlFree(unescaped);
	}

	/* try with a non-escaped URI this may be a strange filename */
	if (context == NULL) {
		context = php_libxml_streams_IO_open_write_wrapper(URI);
	}

	if (context == NULL) {
		return(NULL);
	}

	/* Allocate the Output buffer front-end. */
	ret = xmlAllocOutputBuffer(encoder);
	if (ret != NULL) {
		ret->context = context;
		ret->writecallback = php_libxml_streams_IO_write;
		ret->closecallback = php_libxml_streams_IO_close;
	}

	return(ret);
}

static void _php_libxml_free_error(void *ptr)
{
	/* This will free the libxml alloc'd memory */
	xmlResetError((xmlErrorPtr) ptr);
}

#if LIBXML_VERSION >= 21200
static void _php_list_set_error_structure(const xmlError *error, const char *msg)
#else
static void _php_list_set_error_structure(xmlError *error, const char *msg)
#endif
{
	xmlError error_copy;
	int ret;


	memset(&error_copy, 0, sizeof(xmlError));

	if (error) {
		ret = xmlCopyError(error, &error_copy);
	} else {
		error_copy.domain = 0;
		error_copy.code = XML_ERR_INTERNAL_ERROR;
		error_copy.level = XML_ERR_ERROR;
		error_copy.line = 0;
		error_copy.node = NULL;
		error_copy.int1 = 0;
		error_copy.int2 = 0;
		error_copy.ctxt = NULL;
		error_copy.message = (char*)xmlStrdup((xmlChar*)msg);
		error_copy.file = NULL;
		error_copy.str1 = NULL;
		error_copy.str2 = NULL;
		error_copy.str3 = NULL;
		ret = 0;
	}

	if (ret == 0) {
		zend_llist_add_element(LIBXML(error_list), &error_copy);
	}
}

static void php_libxml_ctx_error_level(int level, void *ctx, const char *msg)
{
	xmlParserCtxtPtr parser;

	parser = (xmlParserCtxtPtr) ctx;

	if (parser != NULL && parser->input != NULL) {
		if (parser->input->filename) {
			php_error_docref(NULL, level, "%s in %s, line: %d", msg, parser->input->filename, parser->input->line);
		} else {
			php_error_docref(NULL, level, "%s in Entity, line: %d", msg, parser->input->line);
		}
	} else {
		php_error_docref(NULL, E_WARNING, "%s", msg);
	}
}

void php_libxml_issue_error(int level, const char *msg)
{
	if (LIBXML(error_list)) {
		_php_list_set_error_structure(NULL, msg);
	} else {
		php_error_docref(NULL, level, "%s", msg);
	}
}

static void php_libxml_internal_error_handler(int error_type, void *ctx, const char **msg, va_list ap)
{
	char *buf;
	int len, len_iter, output = 0;

	len = vspprintf(&buf, 0, *msg, ap);
	len_iter = len;

	/* remove any trailing \n */
	while (len_iter && buf[--len_iter] == '\n') {
		buf[len_iter] = '\0';
		output = 1;
	}

	smart_str_appendl(&LIBXML(error_buffer), buf, len);

	efree(buf);

	if (output == 1) {
		if (LIBXML(error_list)) {
			_php_list_set_error_structure(NULL, ZSTR_VAL(LIBXML(error_buffer).s));
		} else if (!EG(exception)) {
			/* Don't throw additional notices/warnings if an exception has already been thrown. */
			switch (error_type) {
				case PHP_LIBXML_CTX_ERROR:
					php_libxml_ctx_error_level(E_WARNING, ctx, ZSTR_VAL(LIBXML(error_buffer).s));
					break;
				case PHP_LIBXML_CTX_WARNING:
					php_libxml_ctx_error_level(E_NOTICE, ctx, ZSTR_VAL(LIBXML(error_buffer).s));
					break;
				default:
					php_error_docref(NULL, E_WARNING, "%s", ZSTR_VAL(LIBXML(error_buffer).s));
			}
		}
		smart_str_free(&LIBXML(error_buffer));
	}
}

static xmlParserInputPtr _php_libxml_external_entity_loader(const char *URL,
		const char *ID, xmlParserCtxtPtr context)
{
	xmlParserInputPtr	ret			= NULL;
	const char			*resource	= NULL;
	zval 				*ctxzv, retval;
	zval				params[3];
	int					status;
	zend_fcall_info		*fci;

	fci = &LIBXML(entity_loader).fci;

	if (fci->size == 0) {
		/* no custom user-land callback set up; delegate to original loader */
		return _php_libxml_default_entity_loader(URL, ID, context);
	}

	if (ID != NULL) {
		ZVAL_STRING(&params[0], ID);
	} else {
		ZVAL_NULL(&params[0]);
	}
	if (URL != NULL) {
		ZVAL_STRING(&params[1], URL);
	} else {
		ZVAL_NULL(&params[1]);
	}
	ctxzv = &params[2];
	array_init_size(ctxzv, 4);

#define ADD_NULL_OR_STRING_KEY(memb) \
	if (context->memb == NULL) { \
		add_assoc_null_ex(ctxzv, #memb, sizeof(#memb) - 1); \
	} else { \
		add_assoc_string_ex(ctxzv, #memb, sizeof(#memb) - 1, \
				(char *)context->memb); \
	}

	ADD_NULL_OR_STRING_KEY(directory)
	ADD_NULL_OR_STRING_KEY(intSubName)
	ADD_NULL_OR_STRING_KEY(extSubURI)
	ADD_NULL_OR_STRING_KEY(extSubSystem)

#undef ADD_NULL_OR_STRING_KEY

	fci->retval	= &retval;
	fci->params	= params;
	fci->param_count = sizeof(params)/sizeof(*params);

	status = zend_call_function(fci, &LIBXML(entity_loader).fcc);
	if (status != SUCCESS || Z_ISUNDEF(retval)) {
		php_libxml_ctx_error(context,
				"Call to user entity loader callback '%s' has failed",
				Z_STRVAL(fci->function_name));
	} else {
		/*
		retval_ptr = *fci->retval_ptr_ptr;
		if (retval_ptr == NULL) {
			php_libxml_ctx_error(context,
					"Call to user entity loader callback '%s' has failed; "
					"probably it has thrown an exception",
					fci->function_name);
		} else */ if (Z_TYPE(retval) == IS_STRING) {
is_string:
			resource = Z_STRVAL(retval);
		} else if (Z_TYPE(retval) == IS_RESOURCE) {
			php_stream *stream;
			php_stream_from_zval_no_verify(stream, &retval);
			if (stream == NULL) {
				php_libxml_ctx_error(context,
						"The user entity loader callback '%s' has returned a "
						"resource, but it is not a stream",
						Z_STRVAL(fci->function_name));
			} else {
				/* TODO: allow storing the encoding in the stream context? */
				xmlCharEncoding enc = XML_CHAR_ENCODING_NONE;
				xmlParserInputBufferPtr pib = xmlAllocParserInputBuffer(enc);
				if (pib == NULL) {
					php_libxml_ctx_error(context, "Could not allocate parser "
							"input buffer");
				} else {
					/* make stream not being closed when the zval is freed */
					GC_ADDREF(stream->res);
					pib->context = stream;
					pib->readcallback = php_libxml_streams_IO_read;
					pib->closecallback = php_libxml_streams_IO_close;

					ret = xmlNewIOInputStream(context, pib, enc);
					if (ret == NULL) {
						xmlFreeParserInputBuffer(pib);
					}
				}
			}
		} else if (Z_TYPE(retval) != IS_NULL) {
			/* retval not string nor resource nor null; convert to string */
			if (try_convert_to_string(&retval)) {
				goto is_string;
			}
		} /* else is null; don't try anything */
	}

	if (ret == NULL) {
		if (resource == NULL) {
			if (ID == NULL) {
				ID = "NULL";
			}
			php_libxml_ctx_error(context,
					"Failed to load external entity \"%s\"\n", ID);
		} else {
			/* we got the resource in the form of a string; open it */
			ret = xmlNewInputFromFile(context, resource);
		}
	}

	zval_ptr_dtor(&params[0]);
	zval_ptr_dtor(&params[1]);
	zval_ptr_dtor(&params[2]);
	zval_ptr_dtor(&retval);
	return ret;
}

static xmlParserInputPtr _php_libxml_pre_ext_ent_loader(const char *URL,
		const char *ID, xmlParserCtxtPtr context)
{

	/* Check whether we're running in a PHP context, since the entity loader
	 * we've defined is an application level (true global) setting.
	 * If we are, we also want to check whether we've finished activating
	 * the modules (RINIT phase). Using our external entity loader during a
	 * RINIT should not be problem per se (though during MINIT it is, because
	 * we don't even have a resource list by then), but then whether one
	 * extension would be using the custom external entity loader or not
	 * could depend on extension loading order
	 * (if _php_libxml_per_request_initialization */
	if (xmlGenericError == php_libxml_error_handler && PG(modules_activated)) {
		return _php_libxml_external_entity_loader(URL, ID, context);
	} else {
		return _php_libxml_default_entity_loader(URL, ID, context);
	}
}

PHP_LIBXML_API void php_libxml_ctx_error(void *ctx, const char *msg, ...)
{
	va_list args;
	va_start(args, msg);
	php_libxml_internal_error_handler(PHP_LIBXML_CTX_ERROR, ctx, &msg, args);
	va_end(args);
}

PHP_LIBXML_API void php_libxml_ctx_warning(void *ctx, const char *msg, ...)
{
	va_list args;
	va_start(args, msg);
	php_libxml_internal_error_handler(PHP_LIBXML_CTX_WARNING, ctx, &msg, args);
	va_end(args);
}

#if LIBXML_VERSION >= 21200
PHP_LIBXML_API void php_libxml_structured_error_handler(void *userData, const xmlError *error)
#else
PHP_LIBXML_API void php_libxml_structured_error_handler(void *userData, xmlErrorPtr error)
#endif
{
	_php_list_set_error_structure(error, NULL);

	return;
}

PHP_LIBXML_API void php_libxml_error_handler(void *ctx, const char *msg, ...)
{
	va_list args;
	va_start(args, msg);
	php_libxml_internal_error_handler(PHP_LIBXML_ERROR, ctx, &msg, args);
	va_end(args);
}

static void php_libxml_exports_dtor(zval *zv)
{
	free(Z_PTR_P(zv));
}

PHP_LIBXML_API void php_libxml_initialize(void)
{
	if (!_php_libxml_initialized) {
		/* we should be the only one's to ever init!! */
		ZEND_IGNORE_LEAKS_BEGIN();
		xmlInitParser();
		ZEND_IGNORE_LEAKS_END();

		_php_libxml_default_entity_loader = xmlGetExternalEntityLoader();
		xmlSetExternalEntityLoader(_php_libxml_pre_ext_ent_loader);

		zend_hash_init(&php_libxml_exports, 0, NULL, php_libxml_exports_dtor, 1);

		_php_libxml_initialized = 1;
	}
}

PHP_LIBXML_API void php_libxml_shutdown(void)
{
	if (_php_libxml_initialized) {
#if defined(LIBXML_SCHEMAS_ENABLED) && LIBXML_VERSION < 21000
		xmlRelaxNGCleanupTypes();
#endif
		/* xmlCleanupParser(); */
		zend_hash_destroy(&php_libxml_exports);

		xmlSetExternalEntityLoader(_php_libxml_default_entity_loader);
		_php_libxml_initialized = 0;
	}
}

PHP_LIBXML_API void php_libxml_switch_context(zval *context, zval *oldcontext)
{
	if (oldcontext) {
		ZVAL_COPY_VALUE(oldcontext, &LIBXML(stream_context));
	}
	if (context) {
		ZVAL_COPY_VALUE(&LIBXML(stream_context), context);
	}
}

static PHP_MINIT_FUNCTION(libxml)
{
	php_libxml_initialize();

	register_libxml_symbols(module_number);

	libxmlerror_class_entry = register_class_LibXMLError();

	if (sapi_module.name) {
		static const char * const supported_sapis[] = {
			"cgi-fcgi",
			"litespeed",
			NULL
		};
		const char * const *sapi_name;

		for (sapi_name = supported_sapis; *sapi_name; sapi_name++) {
			if (strcmp(sapi_module.name, *sapi_name) == 0) {
				_php_libxml_per_request_initialization = 0;
				break;
			}
		}
	}

	if (!_php_libxml_per_request_initialization) {
		/* report errors via handler rather than stderr */
		xmlSetGenericErrorFunc(NULL, php_libxml_error_handler);
		xmlParserInputBufferCreateFilenameDefault(php_libxml_input_buffer_create_filename);
		xmlOutputBufferCreateFilenameDefault(php_libxml_output_buffer_create_filename);
	}

	return SUCCESS;
}


static PHP_RINIT_FUNCTION(libxml)
{
	if (_php_libxml_per_request_initialization) {
		/* report errors via handler rather than stderr */
		xmlSetGenericErrorFunc(NULL, php_libxml_error_handler);
		xmlParserInputBufferCreateFilenameDefault(php_libxml_input_buffer_create_filename);
		xmlOutputBufferCreateFilenameDefault(php_libxml_output_buffer_create_filename);
	}

	/* Enable the entity loader by default. This ensures that
	 * other threads/requests that might have disabled the loader
	 * do not affect the current request.
	 */
	LIBXML(entity_loader_disabled) = 0;

	return SUCCESS;
}

static PHP_RSHUTDOWN_FUNCTION(libxml)
{
	LIBXML(entity_loader).fci.size = 0;
	zval_ptr_dtor_nogc(&LIBXML(entity_loader).callback);
	ZVAL_NULL(&LIBXML(entity_loader).callback);

	return SUCCESS;
}

static PHP_MSHUTDOWN_FUNCTION(libxml)
{
	if (!_php_libxml_per_request_initialization) {
		xmlSetGenericErrorFunc(NULL, NULL);

		xmlParserInputBufferCreateFilenameDefault(NULL);
		xmlOutputBufferCreateFilenameDefault(NULL);
	}
	php_libxml_shutdown();

	return SUCCESS;
}

static zend_result php_libxml_post_deactivate(void)
{
	/* reset libxml generic error handling */
	if (_php_libxml_per_request_initialization) {
		xmlSetGenericErrorFunc(NULL, NULL);

		xmlParserInputBufferCreateFilenameDefault(NULL);
		xmlOutputBufferCreateFilenameDefault(NULL);
	}
	xmlSetStructuredErrorFunc(NULL, NULL);

	/* the steam_context resource will be released by resource list destructor */
	ZVAL_UNDEF(&LIBXML(stream_context));
	smart_str_free(&LIBXML(error_buffer));
	if (LIBXML(error_list)) {
		zend_llist_destroy(LIBXML(error_list));
		efree(LIBXML(error_list));
		LIBXML(error_list) = NULL;
	}
	xmlResetLastError();

	return SUCCESS;
}


static PHP_MINFO_FUNCTION(libxml)
{
	php_info_print_table_start();
	php_info_print_table_row(2, "libXML support", "active");
	php_info_print_table_row(2, "libXML Compiled Version", LIBXML_DOTTED_VERSION);
	php_info_print_table_row(2, "libXML Loaded Version", (char *)xmlParserVersion);
	php_info_print_table_row(2, "libXML streams", "enabled");
	php_info_print_table_end();
}
/* }}} */

/* {{{ Set the streams context for the next libxml document load or write */
PHP_FUNCTION(libxml_set_streams_context)
{
	zval *arg;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_RESOURCE(arg)
	ZEND_PARSE_PARAMETERS_END();

	if (!Z_ISUNDEF(LIBXML(stream_context))) {
		zval_ptr_dtor(&LIBXML(stream_context));
		ZVAL_UNDEF(&LIBXML(stream_context));
	}
	ZVAL_COPY(&LIBXML(stream_context), arg);
}
/* }}} */

/* {{{ Disable libxml errors and allow user to fetch error information as needed */
PHP_FUNCTION(libxml_use_internal_errors)
{
	xmlStructuredErrorFunc current_handler;
	bool use_errors, use_errors_is_null = 1, retval;

	ZEND_PARSE_PARAMETERS_START(0, 1)
		Z_PARAM_OPTIONAL
		Z_PARAM_BOOL_OR_NULL(use_errors, use_errors_is_null)
	ZEND_PARSE_PARAMETERS_END();

	current_handler = xmlStructuredError;
	if (current_handler && current_handler == php_libxml_structured_error_handler) {
		retval = 1;
	} else {
		retval = 0;
	}

	if (use_errors_is_null) {
		RETURN_BOOL(retval);
	}

	if (use_errors == 0) {
		xmlSetStructuredErrorFunc(NULL, NULL);
		if (LIBXML(error_list)) {
			zend_llist_destroy(LIBXML(error_list));
			efree(LIBXML(error_list));
			LIBXML(error_list) = NULL;
		}
	} else {
		xmlSetStructuredErrorFunc(NULL, php_libxml_structured_error_handler);
		if (LIBXML(error_list) == NULL) {
			LIBXML(error_list) = (zend_llist *) emalloc(sizeof(zend_llist));
			zend_llist_init(LIBXML(error_list), sizeof(xmlError), _php_libxml_free_error, 0);
		}
	}
	RETURN_BOOL(retval);
}
/* }}} */

/* {{{ Retrieve last error from libxml */
PHP_FUNCTION(libxml_get_last_error)
{
	ZEND_PARSE_PARAMETERS_NONE();

	const xmlError *error = xmlGetLastError();

	if (error) {
		object_init_ex(return_value, libxmlerror_class_entry);
		add_property_long(return_value, "level", error->level);
		add_property_long(return_value, "code", error->code);
		add_property_long(return_value, "column", error->int2);
		if (error->message) {
			add_property_string(return_value, "message", error->message);
		} else {
			add_property_stringl(return_value, "message", "", 0);
		}
		if (error->file) {
			add_property_string(return_value, "file", error->file);
		} else {
			add_property_stringl(return_value, "file", "", 0);
		}
		add_property_long(return_value, "line", error->line);
	} else {
		RETURN_FALSE;
	}
}
/* }}} */

/* {{{ Retrieve array of errors */
PHP_FUNCTION(libxml_get_errors)
{

	xmlErrorPtr error;

	ZEND_PARSE_PARAMETERS_NONE();

	if (LIBXML(error_list)) {

		array_init(return_value);
		error = zend_llist_get_first(LIBXML(error_list));

		while (error != NULL) {
			zval z_error;

			object_init_ex(&z_error, libxmlerror_class_entry);
			add_property_long_ex(&z_error, "level", sizeof("level") - 1, error->level);
			add_property_long_ex(&z_error, "code", sizeof("code") - 1, error->code);
			add_property_long_ex(&z_error, "column", sizeof("column") - 1, error->int2 );
			if (error->message) {
				add_property_string_ex(&z_error, "message", sizeof("message") - 1, error->message);
			} else {
				add_property_stringl_ex(&z_error, "message", sizeof("message") - 1, "", 0);
			}
			if (error->file) {
				add_property_string_ex(&z_error, "file", sizeof("file") - 1, error->file);
			} else {
				add_property_stringl_ex(&z_error, "file", sizeof("file") - 1, "", 0);
			}
			add_property_long_ex(&z_error, "line", sizeof("line") - 1, error->line);
			add_next_index_zval(return_value, &z_error);

			error = zend_llist_get_next(LIBXML(error_list));
		}
	} else {
		RETURN_EMPTY_ARRAY();
	}
}
/* }}} */

/* {{{ Clear last error from libxml */
PHP_FUNCTION(libxml_clear_errors)
{
	ZEND_PARSE_PARAMETERS_NONE();

	xmlResetLastError();
	if (LIBXML(error_list)) {
		zend_llist_clean(LIBXML(error_list));
	}
}
/* }}} */

PHP_LIBXML_API bool php_libxml_disable_entity_loader(bool disable) /* {{{ */
{
	bool old = LIBXML(entity_loader_disabled);

	LIBXML(entity_loader_disabled) = disable;
	return old;
} /* }}} */

/* {{{ Disable/Enable ability to load external entities */
PHP_FUNCTION(libxml_disable_entity_loader)
{
	bool disable = 1;

	ZEND_PARSE_PARAMETERS_START(0, 1)
		Z_PARAM_OPTIONAL
		Z_PARAM_BOOL(disable)
	ZEND_PARSE_PARAMETERS_END();

	RETURN_BOOL(php_libxml_disable_entity_loader(disable));
}
/* }}} */

/* {{{ Changes the default external entity loader */
PHP_FUNCTION(libxml_set_external_entity_loader)
{
	zval					*callback;
	zend_fcall_info			fci;
	zend_fcall_info_cache	fcc;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_FUNC_OR_NULL_WITH_ZVAL(fci, fcc, callback)
	ZEND_PARSE_PARAMETERS_END();

	if (ZEND_FCI_INITIALIZED(fci)) { /* argument not null */
		LIBXML(entity_loader).fci = fci;
		LIBXML(entity_loader).fcc = fcc;
	} else {
		LIBXML(entity_loader).fci.size = 0;
	}
	if (!Z_ISNULL(LIBXML(entity_loader).callback)) {
		zval_ptr_dtor_nogc(&LIBXML(entity_loader).callback);
	}
	ZVAL_COPY(&LIBXML(entity_loader).callback, callback);
	RETURN_TRUE;
}
/* }}} */

/* {{{ Get the current external entity loader, or null if the default loader is installer. */
PHP_FUNCTION(libxml_get_external_entity_loader)
{
	ZEND_PARSE_PARAMETERS_NONE();
	RETURN_COPY(&LIBXML(entity_loader).callback);
}
/* }}} */

/* {{{ Common functions shared by extensions */
int php_libxml_xmlCheckUTF8(const unsigned char *s)
{
	size_t i;
	unsigned char c;

	for (i = 0; (c = s[i++]);) {
		if ((c & 0x80) == 0) {
		} else if ((c & 0xe0) == 0xc0) {
			if ((s[i++] & 0xc0) != 0x80) {
				return 0;
			}
		} else if ((c & 0xf0) == 0xe0) {
			if ((s[i++] & 0xc0) != 0x80 || (s[i++] & 0xc0) != 0x80) {
				return 0;
			}
		} else if ((c & 0xf8) == 0xf0) {
			if ((s[i++] & 0xc0) != 0x80 || (s[i++] & 0xc0) != 0x80 || (s[i++] & 0xc0) != 0x80) {
				return 0;
			}
		} else {
			return 0;
		}
	}
	return 1;
}

zval *php_libxml_register_export(zend_class_entry *ce, php_libxml_export_node export_function)
{
	php_libxml_func_handler export_hnd;

	/* Initialize in case this module hasn't been loaded yet */
	php_libxml_initialize();
	export_hnd.export_func = export_function;

	return zend_hash_add_mem(&php_libxml_exports, ce->name, &export_hnd, sizeof(export_hnd));
}

PHP_LIBXML_API xmlNodePtr php_libxml_import_node(zval *object)
{
	zend_class_entry *ce = NULL;
	xmlNodePtr node = NULL;
	php_libxml_func_handler *export_hnd;

	if (Z_TYPE_P(object) == IS_OBJECT) {
		ce = Z_OBJCE_P(object);
		while (ce->parent != NULL) {
			ce = ce->parent;
		}
		if ((export_hnd = zend_hash_find_ptr(&php_libxml_exports, ce->name))) {
			node = export_hnd->export_func(object);
		}
	}
	return node;
}

PHP_LIBXML_API int php_libxml_increment_node_ptr(php_libxml_node_object *object, xmlNodePtr node, void *private_data)
{
	int ret_refcount = -1;

	if (object != NULL && node != NULL) {
		if (object->node != NULL) {
			if (object->node->node == node) {
				return object->node->refcount;
			} else {
				php_libxml_decrement_node_ptr(object);
			}
		}
		if (node->_private != NULL) {
			object->node = node->_private;
			ret_refcount = ++object->node->refcount;
			/* Only dom uses _private */
			if (object->node->_private == NULL) {
				object->node->_private = private_data;
			}
		} else {
			ret_refcount = 1;
			object->node = emalloc(sizeof(php_libxml_node_ptr));
			object->node->node = node;
			object->node->refcount = 1;
			object->node->_private = private_data;
			node->_private = object->node;
		}
	}

	return ret_refcount;
}

PHP_LIBXML_API int php_libxml_decrement_node_ptr(php_libxml_node_object *object)
{
	int ret_refcount = -1;
	php_libxml_node_ptr *obj_node;

	if (object != NULL && object->node != NULL) {
		obj_node = (php_libxml_node_ptr *) object->node;
		ret_refcount = --obj_node->refcount;
		if (ret_refcount == 0) {
			if (obj_node->node != NULL) {
				obj_node->node->_private = NULL;
			}
			efree(obj_node);
		}
		object->node = NULL;
	}

	return ret_refcount;
}

PHP_LIBXML_API int php_libxml_increment_doc_ref(php_libxml_node_object *object, xmlDocPtr docp)
{
	int ret_refcount = -1;

	if (object->document != NULL) {
		object->document->refcount++;
		ret_refcount = object->document->refcount;
	} else if (docp != NULL) {
		ret_refcount = 1;
		object->document = emalloc(sizeof(php_libxml_ref_obj));
		object->document->ptr = docp;
		object->document->refcount = ret_refcount;
		object->document->doc_props = NULL;
	}

	return ret_refcount;
}

PHP_LIBXML_API int php_libxml_decrement_doc_ref(php_libxml_node_object *object)
{
	int ret_refcount = -1;

	if (object != NULL && object->document != NULL) {
		ret_refcount = --object->document->refcount;
		if (ret_refcount == 0) {
			if (object->document->ptr != NULL) {
				xmlFreeDoc((xmlDoc *) object->document->ptr);
			}
			if (object->document->doc_props != NULL) {
				if (object->document->doc_props->classmap) {
					zend_hash_destroy(object->document->doc_props->classmap);
					FREE_HASHTABLE(object->document->doc_props->classmap);
				}
				efree(object->document->doc_props);
			}
			efree(object->document);
		}
		object->document = NULL;
	}

	return ret_refcount;
}

PHP_LIBXML_API void php_libxml_node_free_resource(xmlNodePtr node)
{
	if (!node) {
		return;
	}

	switch (node->type) {
		case XML_DOCUMENT_NODE:
		case XML_HTML_DOCUMENT_NODE:
			break;
		default:
			if (node->parent == NULL || node->type == XML_NAMESPACE_DECL) {
				php_libxml_node_free_list((xmlNodePtr) node->children);
				switch (node->type) {
					/* Skip property freeing for the following types */
					case XML_ATTRIBUTE_DECL:
					case XML_DTD_NODE:
					case XML_DOCUMENT_TYPE_NODE:
					case XML_ENTITY_DECL:
					case XML_ATTRIBUTE_NODE:
					case XML_NAMESPACE_DECL:
					case XML_TEXT_NODE:
						break;
					default:
						php_libxml_node_free_list((xmlNodePtr) node->properties);
				}
				if (php_libxml_unregister_node(node) == 0) {
					node->doc = NULL;
				}
				php_libxml_node_free(node);
			} else {
				php_libxml_unregister_node(node);
			}
	}
}

PHP_LIBXML_API void php_libxml_node_decrement_resource(php_libxml_node_object *object)
{
	int ret_refcount = -1;
	xmlNodePtr nodep;
	php_libxml_node_ptr *obj_node;

	if (object != NULL && object->node != NULL) {
		obj_node = (php_libxml_node_ptr *) object->node;
		nodep = object->node->node;
		ret_refcount = php_libxml_decrement_node_ptr(object);
		if (ret_refcount == 0) {
			php_libxml_node_free_resource(nodep);
		} else {
			if (obj_node && object == obj_node->_private) {
				obj_node->_private = NULL;
			}
		}
	}
	if (object != NULL && object->document != NULL) {
		/* Safe to call as if the resource were freed then doc pointer is NULL */
		php_libxml_decrement_doc_ref(object);
	}
}
/* }}} */

#if defined(PHP_WIN32) && defined(COMPILE_DL_LIBXML)
PHP_LIBXML_API BOOL WINAPI DllMain(HINSTANCE hinstDLL, DWORD fdwReason, LPVOID lpvReserved)
{
	return xmlDllMain(hinstDLL, fdwReason, lpvReserved);
}
#endif

#endif
