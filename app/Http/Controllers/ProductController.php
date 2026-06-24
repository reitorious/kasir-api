<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Menampilkan semua produk beserta nama kategorinya
    public function index()
    {
        // Mengambil produk yang stoknya lebih dari 0 saja (opsional, tergantung bisnis)
        $products = Product::with('category')->get();

        return response()->json([
            'message' => 'Berhasil mengambil data produk',
            'data' => $products
        ], 200);
    }
    // Tambah Produk Baru
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($request->all());

        return response()->json(['message' => 'Produk berhasil ditambahkan', 'data' => $product], 201);
    }

    // Update Produk (Bisa untuk tambah stok atau ubah harga)
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($request->all());

        return response()->json(['message' => 'Produk berhasil diupdate', 'data' => $product]);
    }

    // Hapus Produk
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}