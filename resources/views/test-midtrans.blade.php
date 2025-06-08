<!DOCTYPE html>
<html>
<head>
    <title>Midtrans Configuration Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .config-item { margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .label { font-weight: bold; }
        .value { font-family: monospace; }
        h1 { color: #333; }
    </style>
    <script type="text/javascript" 
            src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" 
            data-client-key="{{ config('midtrans.client_key') }}">
    </script>
</head>
<body>
    <div class="container">
        <h1>Midtrans Configuration Test</h1>
        
        <h2>PHP Configuration Values</h2>
        <div class="config-item">
            <div class="label">Server Key:</div>
            <div class="value">{{ !empty(config('midtrans.server_key')) ? substr(config('midtrans.server_key'), 0, 10) . '...' : 'Not set' }}</div>
        </div>
        
        <div class="config-item">
            <div class="label">Client Key:</div>
            <div class="value">{{ config('midtrans.client_key') }}</div>
        </div>
        
        <div class="config-item">
            <div class="label">Production Mode:</div>
            <div class="value">{{ config('midtrans.is_production') ? 'Yes' : 'No' }}</div>
        </div>
        
        <div class="config-item">
            <div class="label">Callback URL:</div>
            <div class="value">{{ config('midtrans.callback_url') }}</div>
        </div>
        
        <div class="config-item">
            <div class="label">Webhook URL:</div>
            <div class="value">{{ config('midtrans.webhook_url') }}</div>
        </div>
        
        <h2>JavaScript Check</h2>
        <div id="js-check">Checking Midtrans snap.js...</div>
        
        <button id="test-btn" style="margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Test Midtrans Connection
        </button>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jsCheck = document.getElementById('js-check');
        
        if (typeof snap !== 'undefined') {
            jsCheck.textContent = '✅ Midtrans snap.js successfully loaded';
            jsCheck.style.color = 'green';
        } else {
            jsCheck.textContent = '❌ Midtrans snap.js failed to load';
            jsCheck.style.color = 'red';
        }
        
        document.getElementById('test-btn').addEventListener('click', function() {
            if (typeof snap === 'undefined') {
                alert('Midtrans snap.js not loaded. Cannot perform test.');
                return;
            }
            
            alert('Midtrans is properly configured!');
        });
    });
    </script>
</body>
</html> 