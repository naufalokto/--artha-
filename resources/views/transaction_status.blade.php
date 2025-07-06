<!DOCTYPE html>
<html>
<head>
    <title>Status Transaksi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #222; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #e2e8f0; padding: 32px; text-align: center; }
        h2 { color: #2563eb; }
        .status { font-size: 1.5em; margin: 16px 0; }
        .message { margin-bottom: 24px; }
        a { display: inline-block; padding: 10px 24px; background: #2563eb; color: #fff; border-radius: 4px; text-decoration: none; transition: background 0.2s; }
        a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Status Transaksi</h2>
        <div class="status">{{ $status }}</div>
        <div class="message">{{ $message }}</div>
        <a href="/">Kembali ke Beranda</a>
    </div>
</body>
</html> 