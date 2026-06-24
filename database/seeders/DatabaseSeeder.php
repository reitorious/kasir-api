<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Kasir
        // Akun Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@toko.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Akun Karyawan / Kasir
        User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@toko.com',
            'password' => bcrypt('password123'),
            'role' => 'karyawan',
        ]);

        // 2. Buat Kategori
        $makanan = Category::create(['name' => 'Makanan']);
        $minuman = Category::create(['name' => 'Minuman']);

        // 3. Buat Produk Contoh
        Product::create([
            'category_id' => $makanan->id,
            'name' => 'Nasi Goreng Spesial',
            'sku' => 'NGS-001',
            'price' => 25000,
            'stock' => 50,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'name' => 'Es Teh Manis',
            'sku' => 'ETM-001',
            'price' => 5000,
            'stock' => 100,
        ]);
    }
}