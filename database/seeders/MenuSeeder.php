<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuData = [
            'Makanan Utama' => [
                ['nama_menu' => 'Nasi Goreng Spesial', 'harga' => 25000, 'stok' => 50],
                ['nama_menu' => 'Ayam Bakar Madu', 'harga' => 35000, 'stok' => 30],
                ['nama_menu' => 'Bebek Goreng Kremes', 'harga' => 40000, 'stok' => 20],
            ],
            'Appetizer' => [
                ['nama_menu' => 'Tempe Mendoan', 'harga' => 10000, 'stok' => 40],
                ['nama_menu' => 'Tahu Bakso', 'harga' => 12000, 'stok' => 35],
                ['nama_menu' => 'Pangsit Goreng', 'harga' => 15000, 'stok' => 25],
            ],
            'Minuman' => [
                ['nama_menu' => 'Es Teh Manis', 'harga' => 5000, 'stok' => 100],
                ['nama_menu' => 'Es Jeruk', 'harga' => 8000, 'stok' => 80],
                ['nama_menu' => 'Jus Alpukat', 'harga' => 15000, 'stok' => 40],
            ],
        ];

        foreach ($menuData as $kategoriNama => $items) {
            $kategori = Kategori::where('nama_kategori', $kategoriNama)->first();

            if ($kategori) {
                foreach ($items as $item) {
                    Menu::create([
                        'id_kategori' => $kategori->id_kategori,
                        'nama_menu' => $item['nama_menu'],
                        'harga' => $item['harga'],
                        'stok' => $item['stok'],
                    ]);
                }
            }
        }
    }
}
