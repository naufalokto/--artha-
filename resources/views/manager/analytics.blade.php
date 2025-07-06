@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation and Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-4">
            <a href="/manager/dashboard" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <span class="text-gray-400">/</span>
            <h1 class="text-2xl font-bold text-gray-900">Product Analytics</h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="/manager/analytics" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-150 ease-in-out">
                <i class="fas fa-chart-line mr-2"></i>Analytics
            </a>
            <a href="/manager/transactions" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded transition duration-150 ease-in-out border border-blue-300">
                <i class="fas fa-exchange-alt mr-2"></i>Transactions
            </a>
            <a href="/inventory" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded transition duration-150 ease-in-out border border-blue-300">
                <i class="fas fa-boxes mr-2"></i>Inventory
            </a>
            <span class="text-blue-700 font-medium">Welcome, {{ session('user')['name'] ?? 'Manager' }}</span>
            <a href="/logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition duration-150 ease-in-out">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">
            <i class="fas fa-filter mr-2"></i>Filter Period
        </h2>
        <div class="flex space-x-4 mb-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150 ease-in-out">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150 ease-in-out">
            </div>
            <div class="flex items-end">
                <button onclick="analyzeProducts()" id="analyzeBtn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded transition duration-150 ease-in-out flex items-center">
                    <i class="fas fa-search mr-2"></i>Analyze
                </button>
            </div>
        </div>
        <div class="text-gray-700 mb-2">
            Please select a <b>date range</b> and complete the <b>Transaction Recap</b> first before running product analysis.
        </div>
        <a href="/manager/transactions" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-150 ease-in-out inline-block">
            <i class="fas fa-list mr-2"></i>Go to Transaction Recap
        </a>
    </div>

    <!-- Analysis Results -->
    <div id="analysisResults" class="space-y-6">
        <!-- Summary Card -->
        <!-- (Summary card removed) -->

        <!-- Recommendations Card -->
        <!-- (Recommendations card removed) -->

        <!-- Detailed Results -->
        <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-table mr-2"></i>Detailed Analysis
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Top 20</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Analysis</th>
                        </tr>
                    </thead>
                    <tbody id="detailsTable" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No data available. Please run the analysis.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function analyzeProducts() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    // Validate date format
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (startDate && !dateRegex.test(startDate)) {
        alert('Start date must be in YYYY-MM-DD format');
        return;
    }
    if (endDate && !dateRegex.test(endDate)) {
        alert('End date must be in YYYY-MM-DD format');
        return;
    }
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        if (start > end) {
            alert('Start date cannot be after end date');
            return;
        }
    }
    // Prepare query params for transaction-summary and analyze
    const queryParams = new URLSearchParams();
    if (startDate) queryParams.append('start', startDate);
    if (endDate) queryParams.append('end', endDate);
    fetch(`/api/manager/transaction-summary?${queryParams.toString()}`, {
        headers: {
            'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to get transaction data');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            if (!data.data || data.data.length === 0) {
                alert('Please fill in the transaction recap first.');
                return;
            }
            // Send the transaction data for analysis directly to Go backend
            const url = `http://localhost:9090/manager/gemini/analyze?${queryParams.toString()}`;
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                },
                body: JSON.stringify(data.data)
            });
        } else {
            throw new Error(data.message || 'Failed to get transaction data');
        }
    })
    .then(response => {
        if (!response) return;
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to analyze products');
            });
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;
        // Go backend may not return 'status', so fallback to data object
        const analysis = data.data || data;
        // Update details table
        if (!analysis.details || analysis.details.length === 0) {
            document.getElementById('detailsTable').innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No analysis results available.
                    </td>
                </tr>
            `;
            return;
        }
        const detailsHtml = (analysis.details || analysis).map(detail => {
            let analytic = {};
            try {
                analytic = typeof detail.analytic_result === 'string' ? JSON.parse(detail.analytic_result) : (detail.analytic_result || {});
            } catch (e) {
                analytic = {};
            }
            return `
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${detail.product_name}</div>
                        <div class="text-sm text-gray-500">ID: ${detail.product_id}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${detail.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp ${detail.revenue?.toLocaleString?.() ?? detail.revenue}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${detail.is_top_20 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${detail.is_top_20 ? 'Yes' : 'No'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div><b>Summary:</b> ${analytic.Summary || '-'}</div>
                        <div><b>Recommendation:</b> ${analytic.Recommendation || '-'}</div>
                    </td>
                </tr>
            `;
        }).join('');
        document.getElementById('detailsTable').innerHTML = detailsHtml;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('detailsTable').innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center">
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-red-700">Failed to load analysis details</p>
                    </div>
                </td>
            </tr>
        `;
    });
}
</script>
@endpush