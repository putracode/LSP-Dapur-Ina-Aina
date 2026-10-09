<?php

use App\Livewire\Kasir\PosCart;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('kasir user can access pos page and render livewire component', function () {
    $kasir = User::factory()->create([
        'role' => 'Kasir',
    ]);

    $this->actingAs($kasir)
        ->get(route('kasir.pos'))
        ->assertStatus(200)
        ->assertSeeLivewire(PosCart::class);
});

test('kasir can add items to cart and calculate total and change', function () {
    $kasir = User::factory()->create(['role' => 'Kasir']);
    $kategori = Kategori::create(['nama_kategori' => 'Minuman']);
    $menu = Menu::create([
        'id_kategori' => $kategori->id_kategori,
        'nama_menu' => 'Es Teh Manis',
        'harga' => 5000,
        'stok' => 10,
    ]);

    Livewire::actingAs($kasir)
        ->test(PosCart::class)
        ->call('addToCart', $menu->id_menu)
        ->assertSet('cart.'.$menu->id_menu.'.qty', 1)
        ->assertSet('cartTotal', 5000)
        ->call('updateQty', $menu->id_menu, 1)
        ->assertSet('cart.'.$menu->id_menu.'.qty', 2)
        ->assertSet('cartTotal', 10000)
        ->set('jumlahBayar', 15000)
        ->assertSet('kembalian', 5000);
});

test('kasir can filter menu by search and category', function () {
    $kasir = User::factory()->create(['role' => 'Kasir']);
    $katFood = Kategori::create(['nama_kategori' => 'Makanan']);
    $katDrink = Kategori::create(['nama_kategori' => 'Minuman']);

    $mieAyam = Menu::create([
        'id_kategori' => $katFood->id_kategori,
        'nama_menu' => 'Mie Ayam Bakso',
        'harga' => 15000,
        'stok' => 10,
    ]);

    $esJeruk = Menu::create([
        'id_kategori' => $katDrink->id_kategori,
        'nama_menu' => 'Es Jeruk Peras',
        'harga' => 6000,
        'stok' => 10,
    ]);

    Livewire::actingAs($kasir)
        ->test(PosCart::class)
        ->assertSee('Mie Ayam Bakso')
        ->assertSee('Es Jeruk Peras')
        ->set('search', 'Jeruk')
        ->assertSee('Es Jeruk Peras')
        ->assertDontSee('Mie Ayam Bakso')
        ->set('search', '')
        ->call('selectCategory', $katFood->id_kategori)
        ->assertSee('Mie Ayam Bakso')
        ->assertDontSee('Es Jeruk Peras');
});

test('kasir can complete checkout with cash payment', function () {
    $kasir = User::factory()->create(['role' => 'Kasir']);
    $kategori = Kategori::create(['nama_kategori' => 'Makanan']);
    $menu = Menu::create([
        'id_kategori' => $kategori->id_kategori,
        'nama_menu' => 'Nasi Goreng Spesial',
        'harga' => 20000,
        'stok' => 5,
    ]);

    Livewire::actingAs($kasir)
        ->test(PosCart::class)
        ->call('addToCart', $menu->id_menu)
        ->call('openPaymentModal')
        ->assertSet('showPaymentModal', true)
        ->call('setUangPas', 50000)
        ->call('processPayment')
        ->assertSet('showPaymentModal', false)
        ->assertSet('showReceiptModal', true)
        ->assertSet('cart', [])
        ->assertCount('receiptData.items', 1);

    expect(Transaksi::count())->toBe(1);
    expect($menu->fresh()->stok)->toBe(4);
});
