<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MidtransHelper
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        // Try to load from config
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production');
        
        // Try to load from environment if config is not set
        if (empty($this->serverKey)) {
            $this->serverKey = env('MIDTRANS_SERVER_KEY');
        }
        
        if (empty($this->clientKey)) {
            $this->clientKey = env('MIDTRANS_CLIENT_KEY');
        }
        
        // Fallback to hardcoded values as last resort
        if (empty($this->serverKey)) {
            $this->serverKey = 'SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl';
            \Log::warning('Using hardcoded Midtrans Server Key - this is not secure!');
        }
        
        if (empty($this->clientKey)) {
            $this->clientKey = 'SB-Mid-client-44JyAuImP_XPOzeZ';
            \Log::warning('Using hardcoded Midtrans Client Key');
        }
        
        // Set base URL based on production mode
        $this->baseUrl = $this->isProduction 
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
            
        // Log konfigurasi untuk debugging
        \Log::info('MidtransHelper initialized', [
            'server_key_exists' => !empty($this->serverKey),
            'client_key_exists' => !empty($this->clientKey),
            'is_production' => $this->isProduction,
            'base_url' => $this->baseUrl,
            'server_key' => substr($this->serverKey, 0, 10) . '...',
            'client_key' => substr($this->clientKey, 0, 10) . '...',
        ]);
    }

    /**
     * Create payment token for Snap
     */
    public function createPaymentToken($transactionData)
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/charge', $transactionData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create payment token',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans payment token creation failed', [
                'message' => $e->getMessage(),
                'transaction_data' => $transactionData
            ]);

            return [
                'success' => false,
                'message' => 'Payment service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)
    {
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        return $expectedSignature === $signatureKey;
    }

    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus($orderId)
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get($this->baseUrl . '/' . $orderId . '/status');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get transaction status',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get Midtrans transaction status', [
                'order_id' => $orderId,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get transaction status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel transaction
     */
    public function cancelTransaction($orderId)
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/' . $orderId . '/cancel');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to cancel transaction',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to cancel Midtrans transaction', [
                'order_id' => $orderId,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to cancel transaction: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook notification
     */
    public function processWebhook($notification)
    {
        try {
            $orderId = $notification['order_id'] ?? null;
            $statusCode = $notification['status_code'] ?? null;
            $grossAmount = $notification['gross_amount'] ?? null;
            $signatureKey = $notification['signature_key'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;

            // Verify signature
            if (!$this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
                Log::warning('Invalid webhook signature', [
                    'order_id' => $orderId,
                    'signature' => $signatureKey
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Invalid signature'
                ];
            }

            // Determine transaction status
            $status = $this->mapTransactionStatus($transactionStatus, $fraudStatus);

            Log::info('Webhook processed successfully', [
                'order_id' => $orderId,
                'status' => $status,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus
            ]);

            return [
                'success' => true,
                'order_id' => $orderId,
                'status' => $status,
                'raw_notification' => $notification
            ];

        } catch (\Exception $e) {
            Log::error('Failed to process webhook', [
                'notification' => $notification,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Map Midtrans transaction status to our status
     */
    protected function mapTransactionStatus($transactionStatus, $fraudStatus)
    {
        if ($transactionStatus == 'capture') {
            return $fraudStatus == 'challenge' ? 'challenge' : 'success';
        } elseif ($transactionStatus == 'settlement') {
            return 'success';
        } elseif ($transactionStatus == 'pending') {
            return 'pending';
        } elseif ($transactionStatus == 'deny') {
            return 'failed';
        } elseif ($transactionStatus == 'expire') {
            return 'expired';
        } elseif ($transactionStatus == 'cancel') {
            return 'cancelled';
        }

        return 'unknown';
    }

    /**
     * Format transaction data for Midtrans Snap
     */
    public function formatSnapTransaction($transactionId, $amount, $customerDetails, $items = [])
    {
        return [
            'payment_type' => 'credit_card',
            'transaction_details' => [
                'order_id' => $transactionId,
                'gross_amount' => (int) $amount
            ],
            'customer_details' => [
                'first_name' => $customerDetails['name'] ?? 'Customer',
                'email' => $customerDetails['email'] ?? 'customer@example.com',
                'phone' => $customerDetails['phone'] ?? '+628123456789'
            ],
            'item_details' => $items,
            'credit_card' => [
                'secure' => true
            ]
        ];
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods()
    {
        return [
            'credit_card' => [
                'name' => 'Credit Card',
                'icon' => 'fas fa-credit-card',
                'description' => 'Visa, Mastercard, JCB'
            ],
            'bank_transfer' => [
                'name' => 'Bank Transfer',
                'icon' => 'fas fa-university',
                'description' => 'BCA, BNI, BRI, Mandiri'
            ],
            'echannel' => [
                'name' => 'Mandiri Bill Payment',
                'icon' => 'fas fa-receipt',
                'description' => 'ATM/Internet Banking Mandiri'
            ],
            'gopay' => [
                'name' => 'GoPay',
                'icon' => 'fab fa-google-pay',
                'description' => 'E-wallet GoPay'
            ],
            'shopeepay' => [
                'name' => 'ShopeePay',
                'icon' => 'fas fa-shopping-bag',
                'description' => 'E-wallet ShopeePay'
            ]
        ];
    }

    /**
     * Test payment data for sandbox
     */
    public function getTestPaymentData()
    {
        return [
            'credit_card' => [
                'number' => '4811111111111114',
                'cvv' => '123',
                'exp_month' => '01',
                'exp_year' => '2025',
                'otp' => '112233'
            ],
            'bank_transfer' => [
                'bank' => 'bca',
                'va_number' => 'will be generated automatically'
            ],
            'ewallet' => [
                'phone' => '08123456789',
                'pin' => '123456'
            ]
        ];
    }

    /**
     * Get server key for API calls
     */
    public function getServerKey()
    {
        return $this->serverKey;
    }
    
    /**
     * Set server key
     */
    public function setServerKey($key)
    {
        $this->serverKey = $key;
        return $this;
    }

    /**
     * Get client key for frontend
     */
    public function getClientKey()
    {
        return $this->clientKey;
    }
    
    /**
     * Set client key
     */
    public function setClientKey($key)
    {
        $this->clientKey = $key;
        return $this;
    }

    /**
     * Check if production mode
     */
    public function isProduction()
    {
        return $this->isProduction;
    }

    /**
     * Create Snap token for frontend payment page
     */
    public function createSnapToken($orderId, $amount, $customerDetails, $items = [])
    {
        try {
            // Format data transaksi
            $transactionData = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $amount
                ],
                'customer_details' => [
                    'first_name' => $customerDetails['name'] ?? 'Customer',
                    'email' => $customerDetails['email'] ?? 'customer@example.com',
                    'phone' => $customerDetails['phone'] ?? '08123456789'
                ],
                'item_details' => $items,
                'enabled_payments' => [
                    'credit_card', 'cimb_clicks', 'bca_klikbca', 'bca_klikpay',
                    'bri_epay', 'echannel', 'permata_va', 'bca_va', 'bni_va',
                    'bri_va', 'other_va', 'gopay', 'indomaret', 'alfamart',
                    'shopeepay', 'akulaku'
                ],
                'callbacks' => [
                    'finish' => config('midtrans.callback_url') . '?order_id=' . $orderId
                ],
                'credit_card' => [
                    'secure' => true
                ]
            ];

            Log::info('Creating Snap token', [
                'order_id' => $orderId,
                'amount' => $amount,
                'items_count' => count($items),
                'callback_url' => config('midtrans.callback_url'),
                'server_key' => $this->maskString($this->serverKey)
            ]);

            // Kirim request ke Midtrans Snap API
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/transactions', $transactionData);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Snap token created successfully', [
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null
                ];
            }

            Log::error('Failed to create Snap token', [
                'status' => $response->status(),
                'error' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create payment token',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Snap token creation failed', [
                'message' => $e->getMessage(),
                'order_id' => $orderId
            ]);

            return [
                'success' => false,
                'message' => 'Payment service error: ' . $e->getMessage()
            ];
        }
    }

    private function maskString($string)
    {
        $length = strlen($string);
        $masked = str_repeat('*', $length - 4) . substr($string, -4);
        return $masked;
    }
} 