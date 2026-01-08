<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Common\EccLevel;
use App\Models\Segment;

class ProductController extends Controller
{
    // Menampilkan halaman manage product
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $products = $query->latest()->get();
        $segments = Segment::all();

        return view('manage', compact('products', 'segments'));
    }

    // Menyimpan produk baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string',
            'segment_id' => 'nullable|exists:segments,id',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('products', 'public');
        }

        Product::create([
            'name' => $request->name,
            'stock' => $request->stock,
            'category' => $request->category,
            'image_path' => $path,
            'segment_id' => $request->segment_id,
        ]);

        return back()->with('success', 'Barang berhasil ditambahkan!');
    }

    // Update produk
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'segment_id' => 'nullable|exists:segments,id',
        ]);

        $product->update($request->only('name', 'category', 'segment_id'));

        return back()->with('success', 'Barang berhasil diperbarui!');
    }

    // Restock produk
    public function restock(Request $request, Product $product)
    {
        $request->validate(['stock_added' => 'required|integer|min:1']);

        $product->increment('stock', $request->stock_added);

        return back()->with('success', 'Stok barang berhasil ditambahkan!');
    }

    // Hapus produk
    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return back()->with('success', 'Barang berhasil dihapus!');
    }

    // Menampilkan QR code untuk produk
    public function showQR($id)
    {
        try {
            $product = Product::findOrFail($id);

            $qrUrl = route('loan.borrow') . '?qr_product=' . $product->id;

            // Generate QR code dengan Chillerlan
            $qrcode = new QRCode();
            $qrImage = $qrcode->render($qrUrl);

            // Jika sudah base64 data URI, langsung gunakan
            if (strpos($qrImage, 'data:image') === 0) {
                $qrCode = '<img src="' . $qrImage . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            } else {
                // Convert to base64 data URI
                $qrCode = '<img src="data:image/png;base64,' . base64_encode($qrImage) . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            }

            return view('product-qr', compact('product', 'qrCode'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }
}
