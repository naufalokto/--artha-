<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transactions Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1a365d;
        }
        .header p {
            margin: 5px 0;
            color: #4a5568;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #2d3748;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .footer {
            text-align: right;
            font-size: 10px;
            color: #718096;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Transactions Report</h1>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Customer Name</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction['transaction_id'] ?? '' }}</td>
                <td>{{ $transaction['customer_name'] ?? '' }}</td>
                <td>Rp {{ number_format($transaction['total_amount'] ?? 0, 2, ',', '.') }}</td>
                <td>{{ $transaction['status'] ?? '' }}</td>
                <td>{{ $transaction['created_at'] ? date('Y-m-d H:i:s', strtotime($transaction['created_at'])) : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Page {PAGE_NUM} of {PAGE_COUNT}</p>
    </div>
</body>
</html> 