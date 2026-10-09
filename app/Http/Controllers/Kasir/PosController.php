<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index(): View
    {
        $kategoris = Kategori::with(['menu' => function ($query): void {
            $query->orderBy('nama_menu');
        }])->get();

        return view('kasir.pos', compact('kategoris'));
    }

    /**
     * Process checkout / payment.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_menu' => ['required', 'exists:menu,id_menu'],
            'items.*.kuantitas' => ['required', 'integer', 'min:1'],
            'metode_pembayaran' => ['required', 'in:Tunai,Non-Tunai'],
            'jumlah_bayar' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $transaksi = DB::transaction(function () use ($validated, $request) {
                $totalTagihan = 0;
                $details = [];

                foreach ($validated['items'] as $item) {
                    $menu = Menu::where('id_menu', $item['id_menu'])->lockForUpdate()->first();

                    if (! $menu) {
                        throw new \Exception('Menu tidak ditemukan.');
                    }

                    if ($menu->stok < $item['kuantitas']) {
                        throw new \Exception("Stok {$menu->nama_menu} tidak mencukupi. Tersisa: {$menu->stok}");
                    }

                    $subtotal = $menu->harga * $item['kuantitas'];
                    $totalTagihan += $subtotal;

                    $details[] = [
                        'id_menu' => $menu->id_menu,
                        'kuantitas' => $item['kuantitas'],
                        'subtotal' => $subtotal,
                        'nama_menu' => $menu->nama_menu,
                        'harga' => $menu->harga,
                    ];

                    $menu->decrement('stok', $item['kuantitas']);
                }

                if ($validated['metode_pembayaran'] === 'Tunai') {
                    $jumlahBayar = $validated['jumlah_bayar'] ?? 0;
                    if ($jumlahBayar < $totalTagihan) {
                        throw new \Exception('Jumlah bayar kurang dari total tagihan.');
                    }
                }

                $transaksi = Transaksi::create([
                    'id_user' => $request->user()->id_user,
                    'total_tagihan' => $totalTagihan,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
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

                return [
                    'transaksi' => $transaksi,
                    'details' => $details,
                    'total_tagihan' => $totalTagihan,
                ];
            });

            $kembalian = 0;
            $jumlahBayar = $validated['jumlah_bayar'] ?? $transaksi['total_tagihan'];

            if ($validated['metode_pembayaran'] === 'Tunai') {
                $kembalian = $jumlahBayar - $transaksi['total_tagihan'];
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'data' => [
                    'id_transaksi' => $transaksi['transaksi']->id_transaksi,
                    'total_tagihan' => $transaksi['total_tagihan'],
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'jumlah_bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                    'items' => $transaksi['details'],
                    'kasir' => $request->user()->nama,
                    'tanggal' => now()->format('d/m/Y H:i:s'),
                    'no_nota' => 'INV-'.str_pad($transaksi['transaksi']->id_transaksi, 6, '0', STR_PAD_LEFT),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show receipt for a transaction.
     */
    public function receipt(Transaksi $transaksi): View
    {
        $transaksi->load('detailTransaksi.menu', 'user');

        return view('kasir.receipt', compact('transaksi'));
    }
}
