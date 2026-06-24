<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        // Mengambil semua transaksi beserta nama kasir dan detail barangnya.
        // latest() akan mengurutkan dari data yang paling baru ke yang paling lama.
        $transactions = Transaction::with(['user', 'details.product'])->latest()->get();

        return response()->json([
            'message' => 'Berhasil mengambil riwayat transaksi',
            'data' => $transactions
        ], 200);
    }
    
    public function store(Request $request)
    {
        // 1. Validasi input dari React (keranjang belanja)
        $request->validate([
            'total_pay' => 'required|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            // Memulai transaksi database agar aman
            DB::beginTransaction();

            $totalPrice = 0;
            $transactionDetails = [];

            // 2. Loop keranjang belanja untuk cek stok dan hitung total
            foreach ($request->items as $item) {
                // Lock row produk (pessimistic locking) untuk mencegah bentrok jika kasir lain juga checkout barengan
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Sisa: {$product->stock}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                // Siapkan data untuk tabel detail
                $transactionDetails[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                // Kurangi stok produk
                $product->stock -= $item['quantity'];
                $product->save();
            }

            // Cek apakah uang bayar cukup
            if ($request->total_pay < $totalPrice) {
                throw new \Exception("Uang pembayaran kurang dari total belanja!");
            }

            // 3. Simpan Nota Induk (Tabel transactions)
            $transaction = Transaction::create([
                'user_id' => $request->user()->id, // Mengambil ID kasir yang sedang login
                'invoice_number' => 'INV-' . time() . '-' . rand(100, 999), // Generate nota unik
                'total_price' => $totalPrice,
                'total_pay' => $request->total_pay,
                'total_return' => $request->total_pay - $totalPrice,
            ]);

            // 4. Simpan Detail Belanja (Tabel transaction_details)
            // Menambahkan transaction_id ke setiap item yang sudah kita siapkan
            foreach ($transactionDetails as &$detail) {
                $detail['transaction_id'] = $transaction->id;
                $detail['created_at'] = now();
                $detail['updated_at'] = now();
            }
            TransactionDetail::insert($transactionDetails);

            // Jika semua aman, simpan permanen ke database
            DB::commit();

            // Load data detail untuk dikirim balik ke React (buat keperluan Print Struk)
            $transaction->load('details.product', 'user');

            return response()->json([
                'message' => 'Transaksi berhasil diproses',
                'data' => $transaction
            ], 201);

        } catch (\Exception $e) {
            // Jika ada error (stok habis/uang kurang), batalkan semua perubahan database!
            DB::rollBack();

            return response()->json([
                'message' => 'Transaksi gagal',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}