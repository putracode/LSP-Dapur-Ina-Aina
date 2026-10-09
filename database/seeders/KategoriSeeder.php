<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriList = ['Makanan Utama', 'Appetizer', 'Minuman'];

        foreach ($kategoriList as $nama) {
            Kategori::create(['nama_kategori' => $nama]);
        }
    }
}
