@extends('layouts.sales')

@section('title', 'Stock Management')
@section('header', 'Stock Management')

@section('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        height: 100%;
        transition: transform 0.2s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-card .icon {
        width: 48px;
        height: 48px;
        background: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    
    .stats-card .icon i {
        font-size: 1.5rem;
        color: white;
    }
    
    .stats-card h3 {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .stats-card .value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark-color);
        margin: 0;
    }

    .table-container {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-top: 2rem;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .table-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark-color);
        margin: 0;
    }

    .table thead th {
        background: var(--light-color);
        color: var(--dark-color);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem;
        border: none;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: #f0f0f0;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .status-low {
        background: #fff5f5;
        color: var(--danger-color);
    }

    .status-normal {
        background: #f0fdf4;
        color: var(--success-color);
    }

    .btn-update {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        background: var(--primary-color);
        color: white;
        border: none;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-update:hover {
        background: #0052cc;
        transform: translateY(-2px);
    }

    .btn-delete {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        background: var(--danger-color);
        color: white;
        border: none;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-delete:hover {
        background: #dc3545;
        transform: translateY(-2px);
    }

    .modal-content {
        border-radius: 1rem;
        border: none;
    }

    .modal-header {
        background: var(--light-color);
        border-radius: 1rem 1rem 0 0;
        border: none;
    }

    .modal-footer {
        border-top: 1px solid #f0f0f0;
    }

    .form-label {
        font-weight: 500;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }

    .form-control {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e0e0e0;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(0, 97, 242, 0.25);
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        margin-left: 0.5rem;
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        padding: 0.5rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary-color);
        border: none;
        color: white !important;
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        background: #0052cc;
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <h3>Total Products</h3>
                <p class="value" id="totalProducts">0</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Low Stock Items</h3>
                <p class="value" id="lowStockCount">0</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3>Total Inventory Value</h3>
                <p class="value" id="totalValue">Rp 0</p>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">
                <i class="fas fa-table me-2"></i>Stock List
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStockModal">
                <i class="fas fa-plus me-2"></i>Add New Product
            </button>
        </div>
        <div class="table-responsive">
            <table id="stockTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>PRODUCT NAME</th>
                        <th>PRICE</th>
                        <th>CURRENT STOCK</th>
                        <th>NOTE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div class="modal fade" id="updateStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Update Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStockForm">
                    <input type="hidden" id="stockId">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" class="form-control" id="currentStock" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Stock Quantity</label>
                        <input type="number" class="form-control" id="newStock" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" id="stockNote" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveStockUpdate">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add New Product Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="newProductName">Product Name</label>
                        <input type="text" class="form-control" id="newProductName" name="product_name" required>
                        <div class="invalid-feedback">Please enter a product name</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="newProductPrice">Price</label>
                        <input type="number" class="form-control" id="newProductPrice" name="price" required min="0">
                        <div class="invalid-feedback">Please enter a valid price (min: 0)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="newProductStock">Initial Stock</label>
                        <input type="number" class="form-control" id="newProductStock" name="stock" required min="1">
                        <div class="invalid-feedback">Please enter a valid stock quantity (min: 1)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="newProductNote">Note</label>
                        <textarea class="form-control" id="newProductNote" name="note" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewProduct">
                    <i class="fas fa-save me-2"></i>Save Product
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Format price in IDR
    function formatPrice(price) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(price || 0);
    }

    // Calculate total inventory value
    function calculateTotalValue(data) {
        return data.reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0);
    }

    // Count low stock items
    function getLowStockCount(data) {
        return data.filter(item => item.quantity < 10).length;
    }

    // Update summary with animation
    function updateSummary(data) {
        const elements = {
            totalProducts: document.getElementById('totalProducts'),
            lowStockCount: document.getElementById('lowStockCount'),
            totalValue: document.getElementById('totalValue')
        };

        elements.totalProducts.textContent = data.length;
        elements.lowStockCount.textContent = getLowStockCount(data);
        elements.totalValue.textContent = formatPrice(calculateTotalValue(data));

        Object.values(elements).forEach(el => {
            el.style.transform = 'scale(1.1)';
            setTimeout(() => {
                el.style.transform = 'scale(1)';
            }, 200);
        });
    }

    // Initialize DataTable
    $(document).ready(function() {
        const stockTable = $('#stockTable').DataTable({
            ajax: {
                url: 'http://localhost:9090/stocks',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                dataSrc: function(response) {
                    if (Array.isArray(response)) {
                        updateSummary(response);
                        return response;
                    }
                    console.error('Unexpected response format:', response);
                    return [];
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables error:', error, thrown);
                    if (xhr.status === 401) {
                        window.location.href = '/login';
                    } else {
                        alert('Failed to load stock data. Please refresh the page.');
                    }
                }
            },
            columns: [
                { 
                    data: 'product_name',
                    defaultContent: 'Undefined Product'
                },
                { 
                    data: 'price',
                    render: function(data) {
                        return formatPrice(data);
                    }
                },
                { 
                    data: 'quantity',
                    render: function(data) {
                        return `<span class="fw-bold">${data}</span>`;
                    }
                },
                { data: 'note' },
                {
                    data: 'quantity',
                    render: function(data) {
                        return data < 10 
                            ? '<span class="status-badge status-low">Low Stock</span>'
                            : '<span class="status-badge status-normal">Normal</span>';
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div class="d-flex gap-2">
                                <button class="btn-update" data-stock='${JSON.stringify(data)}'>
                                    <i class="fas fa-edit me-1"></i>Update
                                </button>
                                <button class="btn-delete" onclick="deleteStock(${data.stock_id})">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>`;
                    }
                }
            ],
            order: [[2, 'asc']],
            responsive: true,
            language: {
                search: '<i class="fas fa-search"></i>',
                searchPlaceholder: 'Search stocks...'
            },
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip'
        });

        // Handle update button click
        $('#stockTable').on('click', '.btn-update', function() {
            const stockData = JSON.parse($(this).data('stock'));
            document.getElementById('stockId').value = stockData.stock_id;
            document.getElementById('productName').value = stockData.product_name;
            document.getElementById('currentStock').value = stockData.quantity;
            document.getElementById('newStock').value = stockData.quantity;
            document.getElementById('stockNote').value = stockData.note || '';
            
            new bootstrap.Modal(document.getElementById('updateStockModal')).show();
        });

        // Delete stock function
        function deleteStock(stockId) {
            if (!stockId) {
                alert('Invalid stock ID');
                return;
            }

            if (!confirm('Are you sure you want to delete this stock?')) {
                return;
            }

            $.ajax({
                url: `/sales/stocks?stock_id=${stockId}`,
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    stockTable.ajax.reload();
                    alert('Stock deleted successfully');
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        window.location.href = '/login';
                    } else {
                        alert('Failed to delete stock: ' + (xhr.responseJSON?.message || error || 'Unknown error'));
                    }
                }
            });
        }

        // Add delete button styling
        const style = document.createElement('style');
        style.textContent = `
            .btn-delete {
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                background: var(--danger-color);
                color: white;
                border: none;
                font-size: 0.875rem;
                transition: all 0.2s;
            }
            .btn-delete:hover {
                background: #dc3545;
                transform: translateY(-2px);
            }
        `;
        document.head.appendChild(style);

        // Handle stock update submission
        $('#saveStockUpdate').click(function() {
            const stockId = document.getElementById('stockId').value;
            const newQuantity = document.getElementById('newStock').value;
            const note = document.getElementById('stockNote').value;

            if (!newQuantity || newQuantity < 0) {
                alert('Please enter a valid quantity');
                return;
            }

            const btn = $(this);
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

            $.ajax({
                url: `/sales/stocks`,
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                data: JSON.stringify({
                    stock_id: parseInt(stockId),
                    quantity: parseInt(newQuantity),
                    note: note
                }),
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('updateStockModal')).hide();
                    stockTable.ajax.reload();
                    alert('Stock updated successfully');
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        window.location.href = '/login';
                    } else {
                        alert('Failed to update stock: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    }
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Handle new product submission
        $('#saveNewProduct').click(function() {
            const token = getToken();
            if (!token) return;
            const productName = $('#newProductName').val().trim();
            const price = $('#newProductPrice').val();
            const stock = $('#newProductStock').val();
            const note = $('#newProductNote').val().trim();

            if (!validateForm()) {
                return;
            }

            const btn = $(this);
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

            const data = {
                product: {
                    product_name: productName,
                    price: parseFloat(price),
                    note: note || ''
                },
                stock: {
                    stock: parseInt(stock)
                }
            };

            $.ajax({
                url: 'http://localhost:9090/sales/stocks',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                data: JSON.stringify(data),
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('addStockModal')).hide();
                    $('#addProductForm')[0].reset();
                    stockTable.ajax.reload();
                    alert('Product added successfully');
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        window.location.href = '/login';
                    } else {
                        alert('Failed to add product: ' + (xhr.responseJSON?.message || error || 'Unknown error'));
                    }
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Form validation function
        function validateForm() {
            const form = document.getElementById('addProductForm');
            const productName = $('#newProductName').val().trim();
            const price = parseFloat($('#newProductPrice').val());
            const stock = parseInt($('#newProductStock').val());
            
            let isValid = true;
            
            form.classList.remove('was-validated');
            
            if (!productName) {
                isValid = false;
                $('#newProductName').addClass('is-invalid');
            } else {
                $('#newProductName').removeClass('is-invalid');
            }
            
            if (isNaN(price) || price < 0) {
                isValid = false;
                $('#newProductPrice').addClass('is-invalid');
            } else {
                $('#newProductPrice').removeClass('is-invalid');
            }
            
            if (isNaN(stock) || stock < 1) {
                isValid = false;
                $('#newProductStock').addClass('is-invalid');
            } else {
                $('#newProductStock').removeClass('is-invalid');
            }
            
            form.classList.add('was-validated');
            return isValid;
        }

        // Reset form when modal is closed
        $('#addStockModal').on('hidden.bs.modal', function() {
            const form = document.getElementById('addProductForm');
            form.reset();
            form.classList.remove('was-validated');
            $('.is-invalid').removeClass('is-invalid');
        });

        // Refresh data every 5 minutes
        setInterval(() => {
            stockTable.ajax.reload(null, false);
        }, 300000);
    });

    // Tambahkan di awal script
    function getToken() {
        const token = localStorage.getItem('token');
        if (!token) {
            alert('Session expired, please login again.');
            window.location.href = '/login';
            return null;
        }
        return token;
    }
</script>
@endsection 