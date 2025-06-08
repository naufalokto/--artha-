<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Exports\TransactionsExport;
use PDF;
use Excel;

class ApiController extends Controller
{
    protected $api;
    private $apiUrl;

    public function __construct()
    {
        $this->api = 'http://localhost:9090';
        $this->apiUrl = 'http://localhost:9090';
    }

    public function signup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|string|in:admin,manager,sales'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = Http::post($this->api . '/signup', [
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'email' => $request->email,
                'password' => $request->password,
                'role' => $request->role
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = Http::post($this->api . '/login', [
                'email' => $request->email,
                'password' => $request->password
            ]);

            if (!$response->successful()) {
                $errorMessage = $response->body();
                
                // Match Go backend's error responses
                if (strpos($errorMessage, 'Invalid email') !== false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid email'
                    ], 401);
                }
                if (strpos($errorMessage, 'Invalid password') !== false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid password'
                    ], 401);
                }
                
                throw new \Exception($errorMessage);
            }

            $data = $response->json();
            
            // Log the response for debugging
            Log::info('Login response from Go backend:', $data);

            // Validate required fields
            if (!isset($data['token']) || !isset($data['email']) || !isset($data['name'])) {
                throw new \Exception('Invalid response structure from backend');
            }

            // Get role and ensure it's lowercase for consistency
            $role = strtolower($data['role'] ?? 'customer');

            // Store user data in session
            session([
                'user' => [
                    'id' => $data['id'] ?? null,
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'token' => $data['token'],
                    'role' => $role
                ]
            ]);

            // Get redirect URL based on role
            $redirectUrl = $this->getRedirectUrl($role);

            // Return success response with all necessary data
            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $data['token'],
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'role' => $role
                ],
                'redirect' => $redirectUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to login: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getRedirectUrl($role)
    {
        // Ensure role is lowercase for consistent comparison
        $role = strtolower($role);
        
        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'manager':
                return '/manager/dashboard';
            case 'sales':
                return '/sales/dashboard';
            case 'customer':
                return '/customer/dashboard';
            default:
                Log::warning('Unknown role detected during login', ['role' => $role]);
                return '/login';
        }
    }

    public function midtransWebhook(Request $request)
    {
        try {
            $notification = $request->all();
            
            Log::info('Midtrans webhook received', [
                'notification' => $notification
            ]);

            $orderId = $notification['order_id'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;

            if (!$orderId || !$transactionStatus) {
                throw new \Exception('Invalid webhook data');
            }

            // Forward webhook to Go backend
            $response = Http::post($this->api . '/midtrans/webhook', $notification);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to forward webhook: ' . $response->body());
            }

            return response()->json([
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Webhook error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
                'role' => 'required|string|in:Admin,Manager,Sales,admin,manager,sales'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Get admin token from session
            $token = session('user')['token'] ?? null;
            
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login as admin.'
                ], 401);
            }

            // Kirim payload HANYA field yang dibutuhkan backend Go
            $payload = [
                'username' => $request->username,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => $request->password,
                'role' => ucfirst(strtolower($request->role))
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->api . '/admin/create-account', $payload);

            if (!$response->successful()) {
                // Coba ambil pesan error dari backend Go
                $errorMsg = $response->json('message') ?? $response->body();
                throw new \Exception($errorMsg);
            }

                return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create account', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function insertProductAndStock(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'raw_materials' => 'required|array',
                'raw_materials.*.id' => 'required|integer',
                'raw_materials.*.quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = Http::post($this->api . '/products', [
                'product_name' => $request->product_name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'raw_materials' => $request->raw_materials
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Product and stock added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add product and stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProductStock(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:0',
                'raw_materials' => 'required|array',
                'raw_materials.*.id' => 'required|integer',
                'raw_materials.*.quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = Http::put($this->api . '/products/' . $request->product_id . '/stock', [
                'quantity' => $request->quantity,
                'raw_materials' => $request->raw_materials
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function insertRawMaterial(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'quantity' => 'required|integer|min:0',
                'unit' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = Http::post($this->api . '/raw-materials', [
                'name' => $request->name,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'unit' => $request->unit
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

                return response()->json([
                    'status' => 'success',
                'message' => 'Raw material added successfully'
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add raw material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProducts(Request $request)
    {
        try {
            $response = Http::get($this->api . '/products');
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch products: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUsers()
    {
        try {
            $token = session('user')['token'] ?? null;
            
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login again.'
                ], 401);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->timeout(config('api.timeout'))
              ->get($this->api . '/admin/viewuser');

            if ($response->status() === 401) {
                session()->forget('user');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired. Please login again.'
                ], 401);
            }
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID is required'
                ], 400);
            }
            $token = session('user')['token'] ?? null;
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login as admin.'
                ], 401);
            }
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->withBody(json_encode(['user_id' => $userId]), 'application/json')
              ->delete($this->api . '/admin/delete-user');
            if (!$response->successful()) {
                $errorMsg = $response->json('message') ?? $response->body();
                throw new \Exception($errorMsg);
            }
                return response()->json([
                    'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            // Clear session data
            session()->forget('user');
            
            return redirect('/login')->with('success', 'Logged out successfully');

        } catch (\Exception $e) {
            Log::error('Logout error', [
                'error' => $e->getMessage()
            ]);
            
            return redirect('/login')->with('error', 'Failed to logout');
        }
    }

    public function getStock(Request $request)
    {
        try {
            $response = Http::get($this->api . '/stock');
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proxyToGo(Request $request)
    {
        try {
            $path = $request->path();
            $method = strtolower($request->method());
            $data = $request->all();
            
            Log::info('Proxying request to Go backend', [
                'path' => $path,
                'method' => $method,
                'data' => $data
            ]);

            $response = Http::withHeaders([
                'Authorization' => $request->header('Authorization')
            ])->$method($this->api . '/' . $path, $data);
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            Log::error('Proxy error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to proxy request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRawMaterials(Request $request)
    {
        try {
            $response = Http::get($this->api . '/raw-materials');

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch raw materials: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteRawMaterial(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = Http::delete($this->api . '/raw-materials/' . $request->id);
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Raw material deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete raw material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteStock(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $response = Http::delete($this->api . '/stock/' . $request->id);
            
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Get user data from session
            $user = session('user');
            if (!$user) {
            return response()->json([
                'status' => 'error',
                    'message' => 'User not logged in'
                ], 401);
            }

            $token = $user['token'] ?? null;
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            // Add to cart in Go backend (POST /customer/cart)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->api . '/customer/cart', [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUserCart(Request $request)
    {
        try {
            $user = session('user');
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not logged in'
                ], 401);
            }
            $token = $user['token'] ?? null;
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }
            // Get cart from Go backend (GET /customer/cart)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get($this->api . '/customer/cart');

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get cart: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCartItems(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cart_ids' => 'required|array',
                'cart_ids.*' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user = session('user');
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not logged in'
                ], 401);
            }
            $token = $user['token'] ?? null;
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }
            // Delete items from cart in Go backend (DELETE /customer/cart)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->delete($this->api . '/customer/cart', [
                'cart_ids' => $request->cart_ids
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Cart items deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete cart items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewTransactionSummary(Request $request)
    {
        try {
            // Get user data from session
            $user = session('user');
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not logged in'
                ], 401);
            }

            // Get transaction summary from Go backend
            $response = Http::get($this->api . '/transactions/summary/' . $user['id']);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewTransactionDetailByID(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Get user data from session
            $user = session('user');
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not logged in'
                ], 401);
            }

            // Get transaction detail from Go backend
            $response = Http::get($this->api . '/transactions/' . $request->transaction_id);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkout(Request $request)
    {
        try {
            // Get user data from session
            $user = session('user');
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not logged in'
                ], 401);
            }

            // Create transaction in Go backend
            $response = Http::post($this->api . '/transactions', [
                'user_id' => $user['id']
            ]);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            $transaction = $response->json();

            // Create Midtrans payment
            $midtransResponse = Http::withBasicAuth(config('midtrans.server_key'), '')
                ->post(config('midtrans.base_url') . '/transactions', [
                    'transaction_details' => [
                        'order_id' => $transaction['id'],
                        'gross_amount' => $transaction['total_amount']
                    ],
                    'customer_details' => [
                        'first_name' => $user['name'],
                        'last_name' => $user['name'],
                        'email' => $user['email']
                    ]
                ]);

            if (!$midtransResponse->successful()) {
                throw new \Exception('Failed to create payment: ' . $midtransResponse->body());
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'transaction' => $transaction,
                    'payment' => $midtransResponse->json()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStocksForSales(Request $request)
    {
        try {
            $token = session('user')['token'] ?? null;
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login as sales.'
                ], 401);
            }
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get($this->api . '/stocks');
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }
            // Return array langsung dari Go
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch stocks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRawMaterialsForSales(Request $request)
    {
        try {
            $token = session('user')['token'] ?? null;
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login as sales.'
                ], 401);
            }
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get($this->api . '/rawmaterial');
            if (!$response->successful()) {
                throw new \Exception($response->body());
            }
            // Return array langsung dari Go
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch raw materials: ' . $e->getMessage()
            ], 500);
        }
    }
}