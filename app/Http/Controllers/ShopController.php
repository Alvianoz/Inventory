<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    // Menampilkan halaman shop
    public function index(Request $request)
    {
        $totalProducts = Product::count();
        $query = Product::query();

        if ($request->has('search') && $request->search) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->has('category') && $request->category) {
            $categoryName = str_replace('-', ' ', $request->category);
            $query->where('category', 'like', '%' . $categoryName . '%');
        }

        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'stock':
                $query->orderBy('stock', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = $this->getCategories();

        return view('shop', compact('products', 'categories', 'totalProducts'));
    }

    // API untuk mendapatkan info produk via QR
    public function getProductByQR($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => $product->category,
                'image_path' => $product->image_url,
            ]
        ]);
    }

    // Proses checkout
    public function checkout(Request $request)
    {
        $request->validate([
            'cart' => 'required|json',
            'payment_method' => 'required|string|in:cash,qris,debit',
        ]);

        $cartItems = json_decode($request->cart, true);

        if (empty($cartItems)) {
            return back()->with('error', 'Keranjang Anda kosong!');
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $productsToAttach = [];
            
            foreach ($cartItems as $item) {
                $product = Product::find($item['id']);
                
                if (!$product) {
                    throw new \Exception('Produk dengan ID ' . $item['id'] . ' tidak ditemukan.');
                }
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception('Stok produk ' . $product->name . ' tidak mencukupi. Tersedia: ' . $product->stock);
                }

                $totalAmount += $product->price * $item['quantity'];
                $productsToAttach[$item['id']] = [
                    'quantity' => $item['quantity'],
                    'price_per_item' => $product->price,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $transaction = Transaction::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
            ]);

            $transaction->products()->attach($productsToAttach);

            DB::commit();
            
            return redirect()->route('shop.receipt', $transaction->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }

    // Menampilkan halaman nota
    public function receipt($id)
    {
        $transaction = Transaction::with(['products', 'user'])->findOrFail($id);
        
        // Pastikan user hanya bisa lihat nota mereka sendiri (kecuali admin)
        if ($transaction->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        return view('receipt', compact('transaction'));
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = date('Ymd');
        
        $lastTransaction = Transaction::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction ? 
            intval(substr($lastTransaction->invoice_number, -4)) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function getCategories()
    {
        $categoriesFromDB = Product::select('category')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        $categories = [];
        foreach ($categoriesFromDB as $cat) {
            $categories[] = [
                'name' => $cat->category,
                'slug' => $this->createSlug($cat->category),
                'count' => $cat->count,
                'icon' => $this->getCategoryIcon($cat->category)
            ];
        }

        return $categories;
    }

    private function createSlug($text)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }

    private function getCategoryIcon($category)
    {
        $icons = [
            'makanan' => 'fa-solid fa-utensils',
            'minuman' => 'fa-solid fa-coffee',
            'snack' => 'fa-solid fa-cookie-bite',
            'elektronik' => 'fa-solid fa-laptop',
            'pakaian' => 'fa-solid fa-tshirt',
            'sepatu' => 'fa-solid fa-shoe-prints',
            'tas' => 'fa-solid fa-shopping-bag',
            'aksesoris' => 'fa-solid fa-ring',
            'kosmetik' => 'fa-solid fa-palette',
            'olahraga' => 'fa-solid fa-dumbbell',
            'buku' => 'fa-solid fa-book',
            'mainan' => 'fa-solid fa-puzzle-piece',
        ];

        $categoryLower = strtolower($category);
        
        foreach ($icons as $key => $icon) {
            if (strpos($categoryLower, $key) !== false) {
                return $icon;
            }
        }

        return 'fa-solid fa-tag';
    }
}