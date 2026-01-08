<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    // Menampilkan halaman peminjaman
    public function borrow(Request $request)
    {
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

        // build categories (same as ShopController)
        $categoriesFromDB = Product::select('category')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        $categories = [];
        foreach ($categoriesFromDB as $cat) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat->category), '-'));
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
            $categoryLower = strtolower($cat->category);
            $icon = 'fa-solid fa-tag';
            foreach ($icons as $key => $ic) {
                if (strpos($categoryLower, $key) !== false) {
                    $icon = $ic;
                    break;
                }
            }

            $categories[] = [
                'name' => $cat->category,
                'slug' => $slug,
                'count' => $cat->count,
                'icon' => $icon
            ];
        }

        return view('loan.borrow', compact('products', 'categories'));
    }

    // API untuk mendapatkan info produk via QR
    public function getProductByQR($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        // Hitung stok tersedia (stock - reserved_stock)
        $availableStock = $product->stock - $product->reserved_stock;

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'stock' => $product->stock,
                'reserved_stock' => $product->reserved_stock,
                'available_stock' => max(0, $availableStock),
                'image_path' => $product->image_path ? asset('storage/' . $product->image_path) : null,
            ]
        ]);
    }

    // Submit peminjaman (status: pending)
    public function submitBorrow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'duration' => 'required|integer|min:1|max:365',
            'borrow_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            
            // Cek stok tersedia (stock - reserved_stock)
            $availableStock = $product->stock - $product->reserved_stock;
            
            if ($availableStock <= 0) {
                throw new \Exception('Barang tidak tersedia untuk dipinjam. Stok sedang dipesan atau sudah habis.');
            }

            // Generate invoice number
            $invoiceNumber = $this->generateLoanNumber();

            // Buat transaction dengan status pending
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'borrow_reason' => $request->borrow_reason,
                'duration' => $request->duration,
                'borrow_date' => Carbon::now(),
                'total_amount' => null,
                'payment_method' => null,
            ]);

            // Attach product ke transaction
            $transaction->products()->attach($product->id, [
                'quantity' => 1,
                'price_per_item' => 0, // Tidak ada harga untuk pinjaman
            ]);

            // Tambahkan reserved_stock (barang ditandai sebagai "Dipesan")
            $product->increment('reserved_stock');

            DB::commit();
            
            return redirect()->route('loan.borrow')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim. Menunggu persetujuan admin.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Pengajuan peminjaman gagal: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Menampilkan halaman pengembalian
    public function return()
    {
        // Ambil semua transaksi yang sedang dipinjam (borrowed) oleh user
        $activeLoans = Transaction::where('user_id', Auth::id())
            ->where('status', 'borrowed')
            ->with('products')
            ->latest()
            ->get();

        return view('loan.return', compact('activeLoans'));
    }

    // API untuk mendapatkan transaksi pinjaman aktif via QR
    public function getLoanByQR($productId)
    {
        $product = Product::findOrFail($productId);
        
        // Cari transaksi yang sedang dipinjam untuk produk ini oleh user yang login
        $transaction = Transaction::where('user_id', Auth::id())
            ->where('status', 'borrowed')
            ->whereHas('products', function($query) use ($productId) {
                $query->where('products.id', $productId);
            })
            ->with('products')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'error' => 'Tidak ada pinjaman aktif untuk produk ini'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'product' => $transaction->products->first(),
                'borrow_date' => $transaction->borrow_date->format('d/m/Y'),
                'duration' => $transaction->duration,
                'borrow_reason' => $transaction->borrow_reason,
            ]
        ]);
    }

    // Submit pengembalian (status: returning)
    public function submitReturn(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('id', $request->transaction_id)
                ->where('user_id', Auth::id())
                ->where('status', 'borrowed')
                ->firstOrFail();

            // Ubah status menjadi returning (menunggu admin konfirmasi)
            $transaction->update([
                'status' => 'returning',
            ]);

            DB::commit();
            
            return redirect()->route('loan.return')
                ->with('success', 'Pengajuan pengembalian berhasil dikirim. Admin akan memverifikasi barang.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Pengajuan pengembalian gagal: ' . $e->getMessage());
        }
    }

    // Generate nomor peminjaman
    private function generateLoanNumber()
    {
        $prefix = 'PIN';
        $date = date('Ymd');
        
        $lastTransaction = Transaction::whereDate('created_at', today())
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction ? 
            intval(substr($lastTransaction->invoice_number, -4)) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}