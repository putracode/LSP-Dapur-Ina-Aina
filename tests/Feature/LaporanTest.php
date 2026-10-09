<?php

use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access laporan index page', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.laporan.index'));

    $response->assertStatus(200);
    $response->assertSee('Laporan Penjualan');
    $response->assertSee('transaksi-table');
    $response->assertSee('salesTrendChart');
});

test('both admin and kasir can view receipt', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $kasir = User::factory()->create(['role' => 'Kasir']);

    $kategori = Kategori::create(['nama_kategori' => 'Makanan']);
    $menu = Menu::create([
        'id_kategori' => $kategori->id_kategori,
        'nama_menu' => 'Nasi Goreng',
        'harga' => 20000,
        'stok' => 10,
    ]);

    $transaksi = Transaksi::create([
        'id_user' => $kasir->id_user,
        'total_tagihan' => 20000,
        'metode_pembayaran' => 'Tunai',
        'status_pembayaran' => 'lunas',
    ]);

    DetailTransaksi::create([
        'id_transaksi' => $transaksi->id_transaksi,
        'id_menu' => $menu->id_menu,
        'kuantitas' => 1,
        'subtotal' => 20000,
    ]);

    // Admin can view receipt
    $this->actingAs($admin)
        ->get(route('kasir.receipt', $transaksi->id_transaksi))
        ->assertStatus(200)
        ->assertSee('DAPUR INA AINA')
        ->assertSee('Nasi Goreng');

    // Kasir can view receipt
    $this->actingAs($kasir)
        ->get(route('kasir.receipt', $transaksi->id_transaksi))
        ->assertStatus(200)
        ->assertSee('DAPUR INA AINA')
        ->assertSee('Nasi Goreng');
});
