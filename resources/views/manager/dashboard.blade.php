@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manager Dashboard</h1>
        <div class="flex items-center space-x-4">
            <span class="text-gray-600">Welcome, {{ session('user')['name'] ?? 'Manager' }}</span>
            <a href="/logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Logout</a>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Sales Overview</h2>
            <div>
                <p>Total Sales: <span class="font-bold">Rp 10.000.000</span></p>
                <p>Today's Sales: <span class="font-bold">Rp 500.000</span></p>
                <p>Active Sales: <span class="font-bold">12</span></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Inventory Status</h2>
            <div>
                <p>Total Products: <span class="font-bold">20</span></p>
                <p>Low Stock Items: <span class="font-bold">3</span></p>
                <p>Out of Stock: <span class="font-bold">1</span></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Recent Transactions</h2>
            <div>
                <div class="border-b py-2">
                    <p class="font-medium">TRX001</p>
                    <p class="text-sm text-gray-600">2024-06-14</p>
                    <p class="text-sm">Rp 150.000</p>
                </div>
                <div class="border-b py-2">
                    <p class="font-medium">TRX002</p>
                    <p class="text-sm text-gray-600">2024-06-13</p>
                    <p class="text-sm">Rp 200.000</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 