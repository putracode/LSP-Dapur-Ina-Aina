<?php

namespace App\Livewire\Kasir;

use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PosCart extends Component
{
    /** @var array<string, array{id: int, nama: string, harga: int, stok: int, qty: int, foto: ?string}> */
    public array $cart = [];

    public string $search = '';

    public int $selectedKategoriId = 0;

    public bool $isCartOpen = false;

    public string $selectedMetode = 'Tunai';

    public int $jumlahBayar = 0;

    public bool $showPaymentModal = false;

    public bool $showReceiptModal = false;

    /** @var array<string, mixed> */
    public array $receiptData = [];

    public function toggleCart(): void
    {
        $this->isCartOpen = ! $this->isCartOpen;
    }

    public function openCart(): void
    {
        $this->isCartOpen = true;
    }

    public function closeCart(): void
    {
        $this->isCartOpen = false;
    }

    public function selectCategory(int $kategoriId): void
    {
        $this->selectedKategoriId = $kategoriId;
    }

    public function addToCart(int $idMenu): void
    {
        $menu = Menu::find($idMenu);

        if (! $menu || $menu->stok <= 0) {
            $this->dispatch('pos-toast', message: 'Menu tidak tersedia atau stok habis.', type: 'warning');

            return;
        }

        $key = (string) $idMenu;

        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['qty'] >= $menu->stok) {
                $this->dispatch('pos-toast', message: "Stok maksimal {$menu->nama_menu} tercapai ({$menu->stok})", type: 'warning');

                return;
            }
            $this->cart[$key]['qty']++;
        } else {
            $this->cart[$key] = [
                'id' => $menu->id_menu,
                'nama' => $menu->nama_menu,
                'harga' => (int) $menu->harga,
                'stok' => (int) $menu->stok,
                'qty' => 1,
                'foto' => $menu->foto,
            ];
        }

        $this->dispatch('pos-toast', message: "{$menu->nama_menu} ditambahkan ke keranjang.", type: 'success');
    }

    public function updateQty(int $idMenu, int $delta): void
    {
        $key = (string) $idMenu;

        if (! isset($this->cart[$key])) {
            return;
        }

        $newQty = $this->cart[$key]['qty'] + $delta;

        if ($newQty <= 0) {
            $this->removeFromCart($idMenu);

            return;
        }

        if ($newQty > $this->cart[$key]['stok']) {
            $this->dispatch('pos-toast', message: "Stok maksimal {$this->cart[$key]['stok']}", type: 'warning');

            return;
        }

        $this->cart[$key]['qty'] = $newQty;
    }

    public function removeFromCart(int $idMenu): void
    {
        $key = (string) $idMenu;

        if (isset($this->cart[$key])) {
            $nama = $this->cart[$key]['nama'];
            unset($this->cart[$key]);
            $this->dispatch('pos-toast', message: "{$nama} dihapus dari keranjang.", type: 'info');
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->dispatch('pos-toast', message: 'Keranjang telah dikosongkan.', type: 'info');
    }

    #[Computed]
    public function cartTotal(): int
    {
        return (int) array_reduce($this->cart, fn ($carry, $item) => $carry + ($item['harga'] * $item['qty']), 0);
    }

    #[Computed]
    public function cartCount(): int
    {
        return (int) array_reduce($this->cart, fn ($carry, $item) => $carry + $item['qty'], 0);
    }

    #[Computed]
    public function kembalian(): int
    {
        if ($this->selectedMetode === 'Tunai') {
            return max(0, $this->jumlahBayar - $this->cartTotal);
        }

        return 0;
    }

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('pos-toast', message: 'Keranjang masih kosong.', type: 'warning');

            return;
        }

        $this->jumlahBayar = $this->selectedMetode === 'Non-Tunai' ? $this->cartTotal : 0;
        $this->isCartOpen = false;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
    }

    public function setMetode(string $metode): void
    {
        $this->selectedMetode = $metode;

        if ($metode === 'Non-Tunai') {
            $this->jumlahBayar = $this->cartTotal;
        }
    }

    public function setUangPas(?int $amount = null): void
    {
        $this->jumlahBayar = $amount ?? $this->cartTotal;
    }

    public function processPayment(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('pos-toast', message: 'Keranjang belanja kosong!', type: 'danger');

            return;
        }

        $totalTagihan = $this->cartTotal;

        if ($this->selectedMetode === 'Tunai' && $this->jumlahBayar < $totalTagihan) {
            $this->dispatch('pos-toast', message: 'Jumlah bayar kurang dari total tagihan!', type: 'danger');

            return;
        }

        try {
            $result = DB::transaction(function () use ($totalTagihan) {
                $details = [];

                foreach ($this->cart as $item) {
                    /** @var Menu|null $menu */
                    $menu = Menu::where('id_menu', $item['id'])->lockForUpdate()->first();

                    if (! $menu || $menu->stok < $item['qty']) {
                        throw new \Exception("Stok {$item['nama']} tidak mencukupi saat checkout.");
                    }

                    $subtotal = $menu->harga * $item['qty'];

                    $details[] = [
                        'id_menu' => $menu->id_menu,
                        'kuantitas' => $item['qty'],
                        'subtotal' => $subtotal,
                        'nama_menu' => $menu->nama_menu,
                        'harga' => $menu->harga,
                    ];

                    $menu->decrement('stok', $item['qty']);
                }

                $jumlahBayar = $this->selectedMetode === 'Tunai' ? $this->jumlahBayar : $totalTagihan;

                $transaksi = Transaksi::create([
                    'id_user' => Auth::user()->id_user,
                    'total_tagihan' => $totalTagihan,
                    'metode_pembayaran' => $this->selectedMetode,
                    'status_pembayaran' => 'lunas',
                ]);

                foreach ($details as $detail) {
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'id_menu' => $detail['id_menu'],
                        'kuantitas' => $detail['kuantitas'],
                        'subtotal' => $detail['subtotal'],
                    ]);
                }

                $kembalian = $this->selectedMetode === 'Tunai' ? max(0, $jumlahBayar - $totalTagihan) : 0;

                return [
                    'transaksi' => $transaksi,
                    'details' => $details,
                    'total_tagihan' => $totalTagihan,
                    'jumlah_bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                ];
            });

            $this->receiptData = [
                'id_transaksi' => $result['transaksi']->id_transaksi,
                'no_nota' => 'INV-'.str_pad((string) $result['transaksi']->id_transaksi, 6, '0', STR_PAD_LEFT),
                'tanggal' => now()->format('d/m/Y H:i:s'),
                'kasir' => Auth::user()->nama,
                'items' => $result['details'],
                'total_tagihan' => $result['total_tagihan'],
                'metode_pembayaran' => $this->selectedMetode,
                'jumlah_bayar' => $result['jumlah_bayar'],
                'kembalian' => $result['kembalian'],
            ];

            $this->cart = [];
            $this->showPaymentModal = false;
            $this->showReceiptModal = true;
            $this->isCartOpen = false;

            $this->dispatch('pos-toast', message: 'Transaksi berhasil disimpan!', type: 'success');

        } catch (\Throwable $e) {
            $this->dispatch('pos-toast', message: $e->getMessage(), type: 'danger');
        }
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->receiptData = [];
    }

    public function render(): View
    {
        $kategoris = Kategori::withCount('menu')->orderBy('nama_kategori')->get();

        $menusQuery = Menu::with('kategori');

        if ($this->selectedKategoriId > 0) {
            $menusQuery->where('id_kategori', $this->selectedKategoriId);
        }

        if (! empty(trim($this->search))) {
            $searchTerm = '%'.trim($this->search).'%';
            $menusQuery->where('nama_menu', 'like', $searchTerm);
        }

        $menus = $menusQuery->orderBy('nama_menu')->get();

        return view('livewire.kasir.pos-cart', [
            'kategoris' => $kategoris,
            'menus' => $menus,
            'cartCount' => $this->cartCount,
            'cartTotal' => $this->cartTotal,
            'kembalian' => $this->kembalian,
        ]);
    }
}
