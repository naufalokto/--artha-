# 🛒 Artha Transaction System Guide

## 💡 Transaction Flow Overview

```
Login → Add to Cart → Checkout → Pay with Midtrans → Webhook → Transaction Complete
```

## 🔐 Authentication

1. **Login** as a customer
2. The system will store a JWT token in your session
3. This token is automatically included in all API requests

## 🛒 Shopping Cart

1. **Browse products** on the dashboard
2. **Add products to cart** with the "Add to Cart" button
3. **View cart** by clicking the Cart tab in the sidebar
4. **Remove items** with the "Remove" button if needed

## 💳 Checkout & Payment Process

### Step 1: Initiate Checkout
- From the cart page, click the "Pay Now" button
- This sends your cart items to the server
- The server creates a transaction with "Pending" status
- The server requests a payment token from Midtrans
- Midtrans returns a token and redirect URL

### Step 2: Complete Payment
- The Midtrans payment popup will appear
- Select your payment method
- Complete the payment process
- Midtrans will send a notification to our webhook

### Step 3: Transaction Status Update
- The webhook receives the payment status from Midtrans
- Our system updates the transaction status
- Transaction can be: Pending, Completed, Cancelled, etc.
- View your transaction history in the Transactions tab

## 🔍 Troubleshooting Common Issues

### 1. "Payment Failed" Error
- **Possible causes:**
  - Your selected payment method is not available
  - There was a timeout during the payment process
  - Midtrans is experiencing issues
- **Solution:**
  - Try again with a different payment method
  - Check your internet connection
  - Contact support if the issue persists

### 2. Payment Completed but Transaction Still Pending
- **Possible causes:**
  - Webhook notification delay
  - Backend processing delay
- **Solution:**
  - Wait a few minutes and refresh the transactions page
  - If still pending after 15 minutes, contact support

### 3. Cannot Proceed to Checkout
- **Possible causes:**
  - Session expired
  - Cart data invalid
- **Solution:**
  - Try logging out and logging in again
  - Clear browser cache and cookies
  - Try adding products to cart again

## 📊 Transaction Statuses

| Status | Description |
|--------|-------------|
| **Pending** | Transaction created but payment not completed |
| **Completed** | Payment successfully processed |
| **Cancelled** | Transaction was cancelled |
| **Failed** | Payment attempt failed |
| **Expired** | Payment window expired |
| **Refunded** | Payment was refunded |

## 💰 Supported Payment Methods

Midtrans supports various payment methods:

1. **Credit/Debit Cards** - Visa, Mastercard, JCB
2. **Bank Transfer** - BCA, BNI, BRI, Mandiri
3. **E-Wallets** - GoPay, ShopeePay, OVO
4. **Convenience Stores** - Alfamart, Indomaret
5. **Internet Banking** - Klik BCA, BNI Internet Banking

## 🚨 When to Contact Support

Contact support if:
- Payment was successful but transaction still shows as pending after 1 hour
- You received a payment confirmation but it's not reflected in your transaction history
- You encounter repeated payment failures
- You need to request a refund

## 🛠️ Developer Notes

### Midtrans Integration

The system uses Midtrans Snap for payment processing. Key components:

1. **Frontend Integration**
   - Snap.js is loaded in the customer dashboard
   - `snap.pay()` is called with the token from the backend
   - Callbacks handle different payment results

2. **Backend Integration**
   - Checkout endpoint communicates with the Go backend
   - Webhook endpoint receives notifications from Midtrans
   - Transaction records are updated based on webhook data

### Testing Transactions

For testing in development:
1. Use Midtrans Sandbox mode
2. Use test cards provided by Midtrans:
   - Card Number: 4811 1111 1111 1114
   - Expiry: Any future date (MM/YY)
   - CVV: Any 3 digits

### Debugging Tools

If you encounter transaction issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Go backend logs
3. Use the test scripts in the project root:
   - `php test_checkout_debug_new.php -t YOUR_JWT_TOKEN`
   - `php test_cart_flow.php` 