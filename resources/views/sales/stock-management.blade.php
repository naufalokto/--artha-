@extends('layouts.app')

@section('content')
<div class="table-container p-6">
    <!-- Toolbar -->
    <div class="toolbar flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Stock Management</h2>
        <button id="deleteSelected" class="btn btn-danger bg-red-500 text-white px-4 py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Delete Selected (<span id="selectedCount">0</span>)
        </button>
    </div>

    <!-- Table Products -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="productTableBody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center">
                        <div class="flex justify-center items-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <span class="ml-2">Loading data...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal" style="display: none;">
    <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">Confirm Delete</h3>
        <p class="mb-6">Are you sure you want to delete selected item(s)?</p>
        <div class="flex justify-end space-x-4">
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300" id="cancelDelete">Cancel</button>
            <button class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" id="confirmDelete">Delete</button>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
}

.loading {
    opacity: 0.5;
    pointer-events: none;
    position: relative;
}

.loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    transform: translate(-50%, -50%);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 4px;
    color: white;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
}

.notification.success {
    background: #48bb78;
}

.notification.error {
    background: #f56565;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedItems = new Set();
    const deleteModal = document.getElementById('deleteModal');
    const deleteSelectedBtn = document.getElementById('deleteSelected');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    // Load Data
    function loadProducts() {
        fetch('/sales/stocks', {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch');
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('productTableBody');
            tbody.innerHTML = '';
            
            data.data.forEach(product => {
                const row = `
                    <tr data-id="${product.id}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="item-checkbox rounded border-gray-300">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${product.quantity}</td>
                        <td class="px-6 py-4 whitespace-nowrap">Rp ${product.price.toLocaleString()}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                ${product.quantity < 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                ${product.quantity < 10 ? 'Low Stock' : 'In Stock'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button class="delete-btn text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
            
            attachEventListeners();
        })
        .catch(error => {
            showNotification('error', 'Failed to load products');
            console.error('Error:', error);
        });
    }

    // Event Listeners
    function attachEventListeners() {
        // Select All checkbox
        document.getElementById('selectAll').addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
                const id = checkbox.closest('tr').dataset.id;
                if (e.target.checked) {
                    selectedItems.add(id);
                } else {
                    selectedItems.delete(id);
                }
            });
            updateDeleteButton();
        });

        // Individual checkboxes
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                const id = this.closest('tr').dataset.id;
                if (this.checked) {
                    selectedItems.add(id);
                } else {
                    selectedItems.delete(id);
                }
                updateDeleteButton();
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                selectedItems.clear();
                selectedItems.add(id);
                showDeleteModal();
            });
        });
    }

    // Update delete button state
    function updateDeleteButton() {
        deleteSelectedBtn.disabled = selectedItems.size === 0;
        selectedCountSpan.textContent = selectedItems.size;
    }

    // Show delete confirmation modal
    function showDeleteModal() {
        deleteModal.style.display = 'block';
    }

    // Hide delete confirmation modal
    function hideDeleteModal() {
        deleteModal.style.display = 'none';
    }

    // Show notification
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Delete selected items
    async function deleteSelected() {
        try {
            document.body.classList.add('loading');
            
            const response = await fetch('/sales/stocks', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_ids: Array.from(selectedItems)
                })
            });

            if (!response.ok) throw new Error('Delete failed');

            showNotification('success', 'Products deleted successfully');
            selectedItems.clear();
            updateDeleteButton();
            loadProducts();
            
        } catch (error) {
            showNotification('error', 'Failed to delete products');
            console.error('Error:', error);
        } finally {
            document.body.classList.remove('loading');
            hideDeleteModal();
        }
    }

    // Error handling
    function handleError(error) {
        if (error.status === 401) {
            window.location.href = '/login';
        } else if (error.status === 403) {
            showNotification('error', 'You do not have permission to perform this action');
        } else {
            showNotification('error', 'An error occurred. Please try again later.');
        }
    }

    // Modal event listeners
    document.getElementById('confirmDelete').addEventListener('click', deleteSelected);
    document.getElementById('cancelDelete').addEventListener('click', hideDeleteModal);
    deleteSelectedBtn.addEventListener('click', showDeleteModal);

    // Initial load
    loadProducts();
});
</script>
@endpush