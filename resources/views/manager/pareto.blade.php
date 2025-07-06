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
            <h1 class="text-2xl font-bold text-gray-900">Pareto Analysis</h1>
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

    <!-- Batch Selection Card -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">
            <i class="fas fa-filter mr-2"></i>Select Analysis Batch
        </h2>
        <div class="flex space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Batch ID</label>
                <input type="number" id="batchId" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150 ease-in-out" 
                    placeholder="Enter batch ID">
            </div>
            <div class="flex items-end">
                <button onclick="getParetoAnalysis()" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded transition duration-150 ease-in-out flex items-center">
                    <i class="fas fa-search mr-2"></i>View Analysis
                </button>
            </div>
        </div>
    </div>

    <!-- Analysis Results -->
    <div id="analysisResults" class="space-y-6">
        <!-- Summary Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-info-circle mr-2"></i>Analysis Summary
            </h2>
            <div id="summary" class="prose max-w-none">
                <p class="text-gray-600">Select a batch ID and click View Analysis to see results.</p>
            </div>
        </div>

        <!-- Recommendations Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-lightbulb mr-2"></i>Recommendations
            </h2>
            <div id="recommendations" class="prose max-w-none">
                <p class="text-gray-600">Recommendations will appear here after analysis.</p>
            </div>
        </div>

        <!-- Results Table Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-table mr-2"></i>Analysis Results
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Top 20</th>
                        </tr>
                    </thead>
                    <tbody id="resultsTable" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No data available. Please select a batch ID and click View Analysis.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom styles for loading states */
    .loading {
        position: relative;
        min-height: 100px;
    }
    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
function getParetoAnalysis() {
    const batchId = document.getElementById('batchId').value;
    if (!batchId) {
        alert('Please enter a batch ID');
        return;
    }

    // Show loading state
    document.getElementById('summary').innerHTML = '<div class="loading"></div>';
    document.getElementById('recommendations').innerHTML = '<div class="loading"></div>';
    document.getElementById('resultsTable').innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center"><div class="loading"></div></td></tr>';

    // Make API request
    fetch(`/api/manager/pareto-analysis?batch_id=${batchId}`, {
        headers: {
            'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to get Pareto analysis');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            const analysis = data.data;
            
            // Update summary
            document.getElementById('summary').innerHTML = `
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium mb-2 text-blue-800">${analysis.batch_name}</h3>
                    <p class="text-blue-700">${analysis.summary}</p>
                </div>
            `;
            
            // Update recommendations
            document.getElementById('recommendations').innerHTML = `
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-green-700">${analysis.recommendation}</p>
                </div>
            `;
            
            // Update results table
            if (!analysis.results || analysis.results.length === 0) {
                document.getElementById('resultsTable').innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No results found for this batch.
                        </td>
                    </tr>
                `;
                return;
            }

            const resultsHtml = analysis.results.map(result => `
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${result.product_name}</div>
                        <div class="text-sm text-gray-500">ID: ${result.product_id}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${result.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp ${result.revenue.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${result.is_top_20 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${result.is_top_20 ? 'Yes' : 'No'}
                        </span>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('resultsTable').innerHTML = resultsHtml;
        } else {
            throw new Error(data.message || 'Failed to get Pareto analysis');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('summary').innerHTML = `
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-red-700">Error: ${error.message}</p>
            </div>
        `;
        document.getElementById('recommendations').innerHTML = `
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-red-700">Failed to load recommendations</p>
            </div>
        `;
        document.getElementById('resultsTable').innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-4 text-center">
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-red-700">Failed to load analysis results</p>
                    </div>
                </td>
            </tr>
        `;
    });
}
</script>
@endpush 