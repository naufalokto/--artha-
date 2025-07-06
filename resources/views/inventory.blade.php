@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Inventory Status</h1>
        <a href="{{ url()->previous() }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition duration-150 ease-in-out">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-boxes mr-2"></i>Stock List
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="4" class="text-center py-10">
                            <i class="fas fa-spinner fa-spin text-2xl text-purple-500"></i>
                            <p class="mt-2">Loading inventory...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchInventory();
});

function fetchInventory() {
    fetch('/api/sales/stocks')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const inventoryData = Array.isArray(data) ? data : (data.data || []);
            populateTable(inventoryData);
        })
        .catch(error => {
            console.error('Error fetching inventory:', error);
            const tbody = document.getElementById('inventoryTableBody');
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">Failed to load inventory data. Please try again later.</td></tr>`;
        });
}

function populateTable(inventoryData) {
    const tbody = document.getElementById('inventoryTableBody');
    tbody.innerHTML = '';

    if (inventoryData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-10">No inventory data found.</td></tr>`;
        return;
    }

    inventoryData.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.product_name || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp ${parseFloat(item.price || 0).toLocaleString()}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.quantity}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.note || ''}</td>
        `;
        tbody.appendChild(row);
    });
}
</script>
@endpush 