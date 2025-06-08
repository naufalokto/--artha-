<!-- Set Product Modal -->
<div id="setProductModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Set New Product</h3>
            <form id="setProductForm" class="space-y-4">
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" name="product_name" id="product_name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" name="price" id="price" required min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Initial Stock</label>
                    <input type="number" name="quantity" id="quantity" required min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideModal('setProductModal')"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#setProductForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            product: {
                product_name: $('#product_name').val(),
                note: $('#description').val(),
                price: parseFloat($('#price').val())
            },
            stock: {
                stock: parseInt($('#quantity').val())
            }
        };

        // Log data yang akan dikirim
        console.log('Sending data:', formData);

        $.ajax({
            url: '{{ route("sales.products.store") }}',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer {{ session("jwt_token") }}',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Response:', response);
                Swal.fire({
                    title: 'Success!',
                    text: 'Product has been added successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    hideModal('setProductModal');
                    $('#setProductForm')[0].reset();
                    // Refresh dashboard data if we're on dashboard
                    if (typeof loadDashboardData === 'function') {
                        loadDashboardData();
                    }
                    // Refresh stock data if we're on stock page
                    if (typeof loadStockData === 'function') {
                        loadStockData();
                    }
                });
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                const message = xhr.responseJSON?.message || 'An error occurred while saving the product';
                Swal.fire({
                    title: 'Error!',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>
@endpush 