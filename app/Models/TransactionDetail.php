<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    // Larang Eloquent menebak nama tabel jamak jika nama model sudah spesifik, 
    // namun demi keamanan kita definisikan secara eksplisit jika diperlukan.
    protected $table = 'transaction_details';

    protected $fillable = [
        'transaction_id', 
        'product_id', 
        'quantity', 
        'price', 
        'subtotal'
    ];

    // Relasi: Detail ini merujuk ke sebuah transaksi induk
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi: Detail ini merujuk ke produk yang dibeli
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}