<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use App\Helpers\MidtransHelper;

class MidtransTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $midtransHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->midtransHelper = new MidtransHelper();
    }

    /** @test */
    public function it_can_create_customer_session()
    {
        // Simulate customer login
        $userData = [
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'role' => 'customer'
        ];

        session(['user' => $userData, 'jwt_token' => 'test-jwt-token']);

        $this->assertEquals('customer', session('user')['role']);
        $this->assertEquals('test-jwt-token', session('jwt_token'));
    }

    /** @test */
    public function customer_can_access_dashboard()
    {
        // Create customer session
        session([
            'user' => [
                'name' => 'Test Customer',
                'email' => 'customer@test.com',
                'role' => 'customer'
            ],
            'jwt_token' => 'test-jwt-token'
        ]);

        $response = $this->get('/customer/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('customer.dashboard');
    }

    /** @test */
    public function customer_can_view_products()
    {
        // Mock backend response
        Http::fake([
            'localhost:9090/stocks' => Http::response([
                [
                    'product_id' => 1,
                    'product_name' => 'Test Product',
                    'price' => 100000,
                    'stock' => 10,
                    'description' => 'Test Description'
                ]
            ], 200)
        ]);

        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->get('/customer/products');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'products' => [
                '*' => [
                    'product_id',
                    'product_name',
                    'price',
                    'stock',
                    'description'
                ]
            ]
        ]);
    }

    /** @test */
    public function customer_can_add_to_cart()
    {
        // Mock backend response
        Http::fake([
            'localhost:9090/customer/cart' => Http::response([
                'cart_id' => 1,
                'product_id' => 1,
                'quantity' => 2,
                'user_id' => 1
            ], 201)
        ]);

        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->post('/customer/cart', [
            'product_id' => 1,
            'quantity' => 2
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan ke keranjang'
        ]);
    }

    /** @test */
    public function customer_can_view_cart()
    {
        // Mock backend response
        Http::fake([
            'localhost:9090/customer/cart' => Http::response([
                [
                    'cart_id' => 1,
                    'product_id' => 1,
                    'quantity' => 2,
                    'product' => [
                        'product_id' => 1,
                        'product_name' => 'Test Product',
                        'price' => 100000
                    ]
                ]
            ], 200)
        ]);

        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->get('/customer/cart');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'cartItems' => [
                '*' => [
                    'cart_id',
                    'product_id',
                    'quantity',
                    'product'
                ]
            ]
        ]);
    }

    /** @test */
    public function customer_can_remove_from_cart()
    {
        // Mock backend response
        Http::fake([
            'localhost:9090/customer/cart' => Http::response([], 200)
        ]);

        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->delete('/customer/cart', [
            'cart_ids' => [1, 2]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Item berhasil dihapus dari keranjang'
        ]);
    }

    /** @test */
    public function customer_can_checkout_with_midtrans()
    {
        // Mock backend response for checkout
        Http::fake([
            'localhost:9090/customer/checkout' => Http::response([
                'message' => 'Transaksi berhasil dibuat',
                'token' => 'test-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/test'
            ], 200)
        ]);

        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->post('/customer/checkout', [
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 100000,
                    'name' => 'Test Product'
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Transaksi berhasil dibuat',
            'token' => 'test-snap-token'
        ]);
    }

    /** @test */
    public function customer_can_view_transaction_summary()
    {
        // Mock backend response
        Http::fake([
            'localhost:9090/customer/transactions/summary' => Http::response([
                [
                    'transaction_id' => 1,
                    'date' => '2024-01-01',
                    'total_price' => 200000,
                    'status' => 'settlement',
                    'items_summary' => '2x Test Product'
                ]
            ], 200)
        ]);

        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->get('/customer/transactions/summary');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'transactions' => [
                '*' => [
                    'transaction_id',
                    'date',
                    'total_price',
                    'status',
                    'items_summary'
                ]
            ]
        ]);
    }

    /** @test */
    public function customer_can_view_transaction_details()
    {
        // Mock backend response
        Http::fake([
            'localhost:9090/customer/transactions/detail*' => Http::response([
                [
                    'transaction_id' => 1,
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 100000,
                    'product' => [
                        'product_name' => 'Test Product'
                    ]
                ]
            ], 200)
        ]);

        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->get('/customer/transactions/detail?id=1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'transaction_details' => [
                '*' => [
                    'transaction_id',
                    'product_id',
                    'quantity',
                    'price',
                    'product'
                ]
            ]
        ]);
    }

    /** @test */
    public function it_handles_midtrans_webhook()
    {
        // Mock webhook payload
        $webhookPayload = [
            'order_id' => 'ORDER-1',
            'status_code' => '200',
            'gross_amount' => '200000.00',
            'signature_key' => hash('sha512', 'ORDER-1' . '200' . '200000.00' . config('midtrans.server_key')),
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept'
        ];

        // Mock backend response
        Http::fake([
            'localhost:9090/midtrans/webhook' => Http::response(['status' => 'ok'], 200)
        ]);

        $response = $this->post('/midtrans/webhook', $webhookPayload);
        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    /** @test */
    public function unauthorized_user_cannot_access_customer_routes()
    {
        // Test without session
        $response = $this->get('/customer/dashboard');
        $response->assertRedirect('/login');

        // Test with wrong role
        session([
            'user' => ['role' => 'admin'],
            'jwt_token' => 'test-token'
        ]);

        $response = $this->get('/customer/products');
        $response->assertStatus(403);
    }

    /** @test */
    public function midtrans_helper_can_verify_signature()
    {
        $orderId = 'ORDER-123';
        $statusCode = '200';
        $grossAmount = '100000.00';
        $serverKey = config('midtrans.server_key');
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $isValid = $this->midtransHelper->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey);
        $this->assertTrue($isValid);

        // Test invalid signature
        $invalidSignature = 'invalid-signature';
        $isInvalid = $this->midtransHelper->verifySignature($orderId, $statusCode, $grossAmount, $invalidSignature);
        $this->assertFalse($isInvalid);
    }

    /** @test */
    public function midtrans_helper_can_map_transaction_status()
    {
        $reflection = new \ReflectionClass($this->midtransHelper);
        $method = $reflection->getMethod('mapTransactionStatus');
        $method->setAccessible(true);

        // Test different status mappings
        $this->assertEquals('success', $method->invoke($this->midtransHelper, 'settlement', 'accept'));
        $this->assertEquals('success', $method->invoke($this->midtransHelper, 'capture', 'accept'));
        $this->assertEquals('challenge', $method->invoke($this->midtransHelper, 'capture', 'challenge'));
        $this->assertEquals('pending', $method->invoke($this->midtransHelper, 'pending', null));
        $this->assertEquals('failed', $method->invoke($this->midtransHelper, 'deny', null));
        $this->assertEquals('expired', $method->invoke($this->midtransHelper, 'expire', null));
        $this->assertEquals('cancelled', $method->invoke($this->midtransHelper, 'cancel', null));
        $this->assertEquals('unknown', $method->invoke($this->midtransHelper, 'unknown_status', null));
    }

    /** @test */
    public function midtrans_helper_can_format_snap_transaction()
    {
        $transactionId = 'ORDER-123';
        $amount = 100000;
        $customerDetails = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+628123456789'
        ];
        $items = [
            [
                'id' => '1',
                'price' => 100000,
                'quantity' => 1,
                'name' => 'Test Product'
            ]
        ];

        $formatted = $this->midtransHelper->formatSnapTransaction($transactionId, $amount, $customerDetails, $items);

        $this->assertArrayHasKey('transaction_details', $formatted);
        $this->assertArrayHasKey('customer_details', $formatted);
        $this->assertArrayHasKey('item_details', $formatted);
        $this->assertEquals($transactionId, $formatted['transaction_details']['order_id']);
        $this->assertEquals($amount, $formatted['transaction_details']['gross_amount']);
        $this->assertEquals($customerDetails['name'], $formatted['customer_details']['first_name']);
    }

    /** @test */
    public function midtrans_helper_can_get_payment_methods()
    {
        $paymentMethods = $this->midtransHelper->getPaymentMethods();

        $this->assertIsArray($paymentMethods);
        $this->assertArrayHasKey('credit_card', $paymentMethods);
        $this->assertArrayHasKey('bank_transfer', $paymentMethods);
        $this->assertArrayHasKey('gopay', $paymentMethods);
        
        // Test structure of payment method
        $creditCard = $paymentMethods['credit_card'];
        $this->assertArrayHasKey('name', $creditCard);
        $this->assertArrayHasKey('icon', $creditCard);
        $this->assertArrayHasKey('description', $creditCard);
    }

    /** @test */
    public function midtrans_helper_can_get_test_payment_data()
    {
        $testData = $this->midtransHelper->getTestPaymentData();

        $this->assertIsArray($testData);
        $this->assertArrayHasKey('credit_card', $testData);
        $this->assertArrayHasKey('bank_transfer', $testData);
        $this->assertArrayHasKey('ewallet', $testData);

        // Test credit card test data
        $creditCard = $testData['credit_card'];
        $this->assertEquals('4811111111111114', $creditCard['number']);
        $this->assertEquals('123', $creditCard['cvv']);
        $this->assertEquals('112233', $creditCard['otp']);
    }

    /** @test */
    public function it_validates_checkout_request_data()
    {
        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        // Test with invalid data
        $response = $this->post('/customer/checkout', [
            'items' => []
        ]);
        $response->assertStatus(422);

        // Test with missing required fields
        $response = $this->post('/customer/checkout', [
            'items' => [
                [
                    'product_id' => 1,
                    // missing quantity, price, name
                ]
            ]
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_add_to_cart_request_data()
    {
        session([
            'user' => ['role' => 'customer'],
            'jwt_token' => 'test-token'
        ]);

        // Test with invalid data
        $response = $this->post('/customer/cart', [
            'product_id' => 'invalid',
            'quantity' => 0
        ]);
        $response->assertStatus(422);

        // Test with missing fields
        $response = $this->post('/customer/cart', []);
        $response->assertStatus(422);
    }
} 