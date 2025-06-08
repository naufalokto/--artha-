<!-- Set Raw Material Modal -->
<div id="setRawMaterialModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Tambah Bahan Baku</h3>
            <form id="setRawMaterialForm" class="space-y-4">
                <div>
                    <label for="raw_material_name" class="block text-sm font-medium text-gray-700">Nama Bahan Baku</label>
                    <input type="text" name="raw_material_name" id="raw_material_name" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Harga</label>
                    <div class="mt-1 relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number" name="price" id="price" required min="0.01" step="0.01"
                            class="pl-12 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                    <input type="text" name="supplier" id="supplier" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideModal('setRawMaterialModal')"
                        class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Set Raw Material form initialized');

    $('#setRawMaterialForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');

        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(item => {
            data[item.name] = item.value;
        });

        // Konversi harga ke float
        if (data.price) {
            data.price = parseFloat(data.price);
        }

        // Log data yang akan dikirim
        console.log('Data to be sent:', data);

        // Ambil token dari session
        const token = '{{ session("jwt_token") }}';
        console.log('Token exists:', !!token);

        // Tampilkan loading
        Swal.fire({
            title: 'Menyimpan...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Log request URL
        const requestUrl = 'http://localhost:9090/sales/rawmaterial';
        console.log('Request URL:', requestUrl);

        $.ajax({
            url: requestUrl,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Success response:', response);
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Bahan baku berhasil ditambahkan',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    hideModal('setRawMaterialModal');
                    $('#setRawMaterialForm')[0].reset();
                    // Refresh data
                    if (typeof loadRawMaterialsData === 'function') {
                        loadRawMaterialsData();
                    }
                });
            },
            error: function(xhr, status, error) {
                // Log semua informasi error
                console.error('Error details:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });

                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    console.log('Parsed error response:', errorResponse);
                } catch (e) {
                    console.log('Could not parse error response');
                }

                let errorMessage = 'Terjadi kesalahan saat menyimpan bahan baku';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>
@endpush 