<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Segment;
use App\Models\Transaction;
use App\Traits\SegmentTokenTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    use SegmentTokenTrait;
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
                'elektronik' => 'fa-solid fa-monitor',
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
            $icon = 'fa-solid fa-monitor';
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

        // Ambil pinjaman yang sedang diajukan (pending) oleh user
        $pendingLoans = Transaction::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with('products')
            ->latest()
            ->get();

        return view('loan.borrow', compact('products', 'categories', 'pendingLoans'));
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
                'image_path' => $product->image_url,
            ]
        ]);
    }

    // Submit peminjaman (status: pending)
    public function submitBorrow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'duration' => 'required|integer|min:0|max:365', // Allow 0 for permanent borrow
            'borrow_reason' => 'required|string|max:500',
            'quantity' => 'nullable|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);

            // Determine requested quantity
            $quantity = (int) ($request->input('quantity', 1));
            $duration = (int) $request->duration;
            $isPermanent = $duration === 0;

            // Cek stok tersedia (stock - reserved_stock)
            $availableStock = $product->stock - $product->reserved_stock;

            if ($availableStock < $quantity) {
                throw new \Exception('Barang tidak tersedia dalam jumlah yang diminta. Tersedia: ' . $availableStock);
            }

            // Generate invoice number
            $invoiceNumber = $this->generateLoanNumber();

            // Buat transaction dengan status pending (baik temporary maupun permanent)
            // Semua peminjaman memerlukan persetujuan admin
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'borrow_reason' => $request->borrow_reason,
                'duration' => $duration, // 0 untuk permanent, >0 untuk temporary
                'borrow_date' => Carbon::now(),
                'total_amount' => null,
                'payment_method' => null,
            ]);

            // Attach product ke transaction with requested quantity
            $transaction->products()->attach($product->id, [
                'quantity' => $quantity,
                'price_per_item' => 0, // Tidak ada harga untuk pinjaman
            ]);

            // Reserve stock untuk semua jenis peminjaman (temporary dan permanent)
            // Stock akan dipotong permanen saat admin approve permanent borrow
            $product->increment('reserved_stock', $quantity);

            DB::commit();

            $successMessage = $isPermanent
                ? 'Pengajuan pinjaman permanen berhasil dikirim. Menunggu persetujuan admin.'
                : 'Pengajuan peminjaman berhasil dikirim. Menunggu persetujuan admin.';

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $successMessage]);
            }

            return redirect()->route('loan.borrow')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pengajuan peminjaman gagal: ' . $e->getMessage()], 400);
            }
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
            ->whereHas('products', function ($query) use ($productId) {
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

    // Batalkan peminjaman (menghapus dari pending)
    public function cancelBorrow($id, Request $request)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

            // Kembalikan reserved_stock ke stok normal
            foreach ($transaction->products as $product) {
                $quantity = $product->pivot->quantity;
                $product->decrement('reserved_stock', $quantity);
            }

            // Hapus transaksi
            $transaction->delete();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Peminjaman berhasil dibatalkan']);
            }

            return back()->with('success', 'Peminjaman berhasil dibatalkan');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', 'Pembatalan peminjaman gagal: ' . $e->getMessage());
        }
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

            // If the submission included a segment_id (submitted from segment return page), redirect back to that segment return page with encrypted token
            if ($request->filled('segment_id')) {
                $segment = Segment::findOrFail($request->input('segment_id'));
                $token = $this->generateSegmentReturnToken($segment);
                return redirect()->route('segments.return', $token)
                    ->with('success', 'Pengajuan pengembalian berhasil dikirim. Admin akan memverifikasi barang.');
            }

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