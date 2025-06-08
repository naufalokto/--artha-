@extends('layouts.sales')

@section('title', 'Raw Materials')
@section('header', 'Raw Materials Management')

@section('styles')
<style>
.stat-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
}
.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    transition: all 0.3s ease;
}
.stat-card:hover .stat-icon {
    transform: scale(1.1);
}
.stat-value {
    font-size: 2rem;
    font-weight: bold;
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.table-container {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
    overflow: hidden;
}
.table-hover tbody tr:hover {
    background: rgba(240, 245, 250, 0.5);
    transform: translateX(5px);
    transition: all 0.3s ease;
}
.material-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}
.search-input {
    border-radius: 1rem;
    padding: 0.75rem 1rem;
    padding-left: 2.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}
.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0, 97, 242, 0.1);
}
.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}
.add-button {
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    border: none;
    border-radius: 1rem;
    padding: 0.75rem 1.5rem;
    color: white;
    transition: all 0.3s ease;
}
.add-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 97, 242, 0.15);
}
.supplier-badge {
    background: rgba(0, 97, 242, 0.1);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
}
.action-button {
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}
.action-button:hover {
    transform: scale(1.1);
}
.loading-spinner {
    width: 3rem;
    height: 3rem;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Delete animation styles */
.deleting {
    opacity: 0.5;
    pointer-events: none;
    transition: all 0.3s ease-out;
}

tr {
    transition: all 0.3s ease-out;
}

.delete-error {
    animation: shake 0.5s ease-in-out;
    background-color: rgba(220, 53, 69, 0.1);
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Stats Overview -->
    <div class="row g-4 mb-4">
        <!-- Total Materials Card -->
        <div class="col-md-4">
            <div class="stat-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-3">Total Materials</h4>
                        <div class="stat-value mb-2" id="totalMaterials">0</div>
                        <p class="text-muted mb-0">Available raw materials</p>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="fas fa-box fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Suppliers Card -->
        <div class="col-md-4">
            <div class="stat-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-3">Total Suppliers</h4>
                        <div class="stat-value mb-2" id="totalSuppliers">0</div>
                        <p class="text-muted mb-0">Active suppliers</p>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10">
                        <i class="fas fa-users fa-lg text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Value Card -->
        <div class="col-md-4">
            <div class="stat-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-3">Total Value</h4>
                        <div class="stat-value mb-2" id="totalValue">Rp 0</div>
                        <p class="text-muted mb-0">Current materials value</p>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="fas fa-rupiah-sign fa-lg text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="table-container p-4">
        <!-- Controls Section -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <input type="text" id="supplierFilter" 
                        class="search-input form-control" 
                        placeholder="Search materials or suppliers...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-end gap-3">
                <select id="sortOrder" class="form-select" style="width: auto;">
                    <option value="asc">Name: A-Z</option>
                    <option value="desc">Name: Z-A</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
                
                <button onclick="showAddMaterialModal()" class="add-button">
                    <i class="fas fa-plus me-2"></i>
                    Add Material
                </button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Material Name</th>
                        <th>Price</th>
                        <th>Supplier</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="rawMaterialsTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Material Modal -->
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMaterialForm">
                    <div class="mb-3">
                        <label class="form-label">Material Name</label>
                        <input type="text" class="form-control" name="raw_material_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" class="form-control" name="supplier" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAddMaterial()">Add Material</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Template -->
<template id="loadingSpinner">
    <tr>
        <td colspan="6" class="text-center p-5">
            <div class="d-flex flex-column align-items-center">
                <div class="loading-spinner mb-3"></div>
                <p class="text-muted">Loading materials...</p>
            </div>
        </td>
    </tr>
</template>

<!-- Empty State Template -->
<template id="emptyState">
    <tr>
        <td colspan="6" class="text-center p-5">
            <div class="d-flex flex-column align-items-center">
                <div class="bg-light p-4 rounded-circle mb-3">
                    <i class="fas fa-box-open fa-3x text-muted"></i>
                </div>
                <h5>No materials found</h5>
                <p class="text-muted mb-4">Get started by adding your first raw material</p>
                <button onclick="showAddMaterialModal()" class="add-button">
                    <i class="fas fa-plus me-2"></i>
                    Add New Material
                </button>
            </div>
        </td>
    </tr>
</template>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadRawMaterials();
    
    // Handle filter and sorting changes
    $('#supplierFilter').on('input', debounce(loadRawMaterials, 300));
    $('#sortOrder').on('change', loadRawMaterials);
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function loadRawMaterials() {
    const supplier = $('#supplierFilter').val();
    const sortOrder = $('#sortOrder').val();
    
    $('#rawMaterialsTableBody').html($('#loadingSpinner').html());
    
    $.ajax({
        url: 'http://localhost:9090/rawmaterial',
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.status === 'error') {
                showErrorAlert(response.message);
                return;
            }
            
            const data = Array.isArray(response) ? response : response.data;
            if (Array.isArray(data)) {
                updateRawMaterialsTable(data);
                updateMetrics(data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading raw materials:', error);
            const errorMessage = xhr.responseJSON?.message || 'Failed to load raw materials data. Please try again.';
            showErrorAlert(errorMessage);
            
            // Show empty state if error
            $('#rawMaterialsTableBody').html($('#emptyState').html());
        }
    });
}

function updateMetrics(data) {
    animateValue('#totalMaterials', 0, data.length, 1500);
    
    const uniqueSuppliers = new Set(data.map(item => item.supplier)).size;
    animateValue('#totalSuppliers', 0, uniqueSuppliers, 1500);
    
    const totalValue = data.reduce((sum, item) => sum + parseFloat(item.price), 0);
    animateValue('#totalValue', 0, totalValue, 1500, true);
}

function animateValue(selector, start, end, duration, isCurrency = false) {
    const obj = document.querySelector(selector);
    const range = end - start;
    const minTimer = 50;
    let stepTime = Math.abs(Math.floor(duration / range));
    stepTime = Math.max(stepTime, minTimer);
    
    const startTime = new Date().getTime();
    const endTime = startTime + duration;
    let timer;

    function run() {
        const now = new Date().getTime();
        const remaining = Math.max((endTime - now) / duration, 0);
        const value = Math.round(end - (remaining * range));
        if (isCurrency) {
            obj.textContent = formatCurrency(value);
        } else {
            obj.textContent = value.toLocaleString();
        }
        if (value === end) {
            clearInterval(timer);
        }
    }
    
    timer = setInterval(run, stepTime);
    run();
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
}

function updateRawMaterialsTable(data) {
    const tbody = $('#rawMaterialsTableBody');
    tbody.empty();

    if (!data || data.length === 0) {
        tbody.html($('#emptyState').html());
        return;
    }

    data.forEach((item, index) => {
        const row = createTableRow(item);
        tbody.append(row);
        $(`#row-${index}`).hide().fadeIn(300);
    });
}

function createTableRow(item) {
    const materialName = item.raw_material_name || 'Undefined Material';
    const price = parseFloat(item.price) || 0;
    const supplier = item.supplier || 'Unknown Supplier';
    const createdAt = formatDate(item.created_at);
    const updatedAt = formatDate(item.updated_at);
    
    return `
        <tr data-id="${item.raw_material_id}">
            <td>
                <div class="d-flex align-items-center">
                    <div class="material-avatar me-3">
                        ${materialName.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <div class="fw-medium">${materialName}</div>
                        <small class="text-muted">${item.description || 'No description'}</small>
                    </div>
                </div>
            </td>
            <td>${formatCurrency(price)}</td>
            <td><span class="supplier-badge">${supplier}</span></td>
            <td>${createdAt}</td>
            <td>${updatedAt}</td>
            <td>
                <div class="d-flex justify-content-end gap-2">
                    <button onclick="editMaterial(${item.id})" class="action-button btn btn-light" title="Edit">
                        <i class="fas fa-edit text-primary"></i>
                    </button>
                    <button onclick="deleteMaterial(${item.id})" class="action-button btn btn-light" title="Delete">
                        <i class="fas fa-trash text-danger"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

function formatDate(dateString) {
    try {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return 'Invalid Date';
    }
}

function showAddMaterialModal() {
    $('#addMaterialForm')[0].reset();
    $('#addMaterialModal').modal('show');
}

function submitAddMaterial() {
    const form = $('#addMaterialForm');
    const formData = {
        raw_material_name: form.find('[name="raw_material_name"]').val().trim(),
        price: parseFloat(form.find('[name="price"]').val()),
        supplier: form.find('[name="supplier"]').val().trim()
    };

    // Validate input
    const errors = [];
    if (!formData.raw_material_name) errors.push('Material name is required');
    if (!formData.price || formData.price <= 0) errors.push('Price must be greater than 0');
    if (!formData.supplier) errors.push('Supplier is required');

    if (errors.length > 0) {
        showErrorAlert(errors.join('\n'));
        return;
    }

    // Disable form and show loading
    const submitBtn = $('#addMaterialModal .btn-primary');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Adding...');
    
    $.ajax({
        url: 'http://localhost:9090/sales/rawmaterial',
        method: 'POST',
        data: JSON.stringify({
            raw_material_name: formData.raw_material_name,
            price: formData.price,
            supplier: formData.supplier
        }),
        contentType: 'application/json',
        headers: {
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.status === 'error') {
                showErrorAlert(response.message);
                return;
            }
            
            $('#addMaterialModal').modal('hide');
            form[0].reset();
            loadRawMaterials();
            showSuccessAlert(response.message || 'Raw material added successfully');
        },
        error: function(xhr, status, error) {
            console.error('Error adding raw material:', error);
            const errorMessage = xhr.responseJSON?.message || 'Failed to add raw material. Please try again.';
            showErrorAlert(errorMessage);
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
}

function showSuccessAlert(message) {
    alert(message); // Ganti dengan SweetAlert2 jika tersedia
}

function showErrorAlert(message) {
    alert(message); // Ganti dengan SweetAlert2 jika tersedia
}

function editMaterial(id) {
    alert('Edit feature will be implemented soon.');
}

function deleteMaterial(id) {
    // Validate ID
    if (!id) {
        showErrorAlert('Invalid material ID');
        return;
    }

    // Confirm deletion
    if (!confirm('Are you sure you want to delete this material?')) {
        return;
    }

    const $row = $(`tr[data-id="${id}"]`);
    
    // Optimistic UI update
    $row.addClass('deleting');

    $.ajax({
        url: `http://localhost:9090/rawmaterial?raw_material_id=${id}`,
        method: 'DELETE',
        headers: {
            'Accept': 'application/json'
        },
        success: function(response) {
            // Animate row removal
            $row.fadeOut(300, function() {
                $(this).remove();
                // Reload data to update stats
                loadRawMaterials();
                showSuccessAlert(response.message || 'Raw material deleted successfully');
            });
        },
        error: function(xhr, status, error) {
            handleDeleteError($row, xhr.responseJSON?.message || 'Failed to delete raw material');
        }
    });
}

function handleDeleteError($row, errorMessage) {
    // Remove deleting state
    $row.removeClass('deleting');
    
    // Show error message
    showErrorAlert(errorMessage);
    
    // Visual feedback of error
    $row.addClass('delete-error')
        .delay(1000)
        .queue(function(next) {
            $(this).removeClass('delete-error');
            next();
        });
}
</script>
@endsection