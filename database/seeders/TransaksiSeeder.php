<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Menu;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kasir = User::where('role', 'Kasir')->first() ?? User::first();
        if (! $kasir) {
            $this->command?->error('User Kasir tidak ditemukan! Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $menus = Menu::all();
        if ($menus->isEmpty()) {
            $this->command?->error('Menu tidak ditemukan! Jalankan MenuSeeder terlebih dahulu.');

            return;
        }

        // Definisi skenario transaksi dengan tanggal berbeda
        // Format: [hari_ke_belakang, jam, menit, metode, [[id_menu, qty], ...]]
        $now = Carbon::now();

        $transactionScenarios = [
            // --- HARI INI (Today) ---
            [0, 8, 15, 'Tunai', [[1, 2], [7, 2]]],
            [0, 9, 30, 'Non-Tunai', [[2, 1], [8, 1]]],
            [0, 11, 45, 'Tunai', [[3, 2], [4, 1], [7, 2]]],
            [0, 12, 10, 'Non-Tunai', [[1, 3], [5, 2], [9, 3]]],
            [0, 12, 50, 'Tunai', [[2, 2], [6, 1], [8, 2]]],
            [0, 13, 30, 'Tunai', [[1, 1], [7, 1]]],
            [0, 14, 15, 'Non-Tunai', [[3, 1], [4, 2], [9, 1]]],

            // --- 1 HARI LALU (Yesterday) ---
            [1, 9, 10, 'Tunai', [[1, 2], [7, 2]]],
            [1, 11, 25, 'Non-Tunai', [[2, 2], [5, 1], [8, 2]]],
            [1, 12, 40, 'Tunai', [[3, 3], [4, 2], [7, 3]]],
            [1, 13, 15, 'Non-Tunai', [[1, 1], [9, 1]]],
            [1, 15, 05, 'Tunai', [[2, 1], [6, 2], [8, 1]]],
            [1, 18, 20, 'Tunai', [[3, 2], [4, 1], [7, 2]]],

            // --- 2 HARI LALU ---
            [2, 10, 00, 'Non-Tunai', [[1, 1], [7, 1]]],
            [2, 12, 15, 'Tunai', [[2, 3], [4, 2], [8, 3]]],
            [2, 13, 45, 'Tunai', [[3, 1], [5, 1], [9, 1]]],
            [2, 17, 30, 'Non-Tunai', [[1, 2], [6, 1], [7, 2]]],

            // --- 3 HARI LALU ---
            [3, 11, 10, 'Tunai', [[2, 2], [4, 1], [8, 2]]],
            [3, 12, 30, 'Non-Tunai', [[3, 2], [5, 2], [9, 2]]],
            [3, 14, 20, 'Tunai', [[1, 3], [7, 3]]],
            [3, 18, 45, 'Tunai', [[2, 1], [6, 1], [8, 1]]],

            // --- 4 HARI LALU ---
            [4, 10, 45, 'Non-Tunai', [[1, 2], [4, 2], [7, 2]]],
            [4, 12, 20, 'Tunai', [[3, 1], [8, 1]]],
            [4, 13, 50, 'Tunai', [[2, 2], [5, 1], [9, 2]]],

            // --- 5 HARI LALU ---
            [5, 11, 00, 'Tunai', [[1, 1], [7, 1]]],
            [5, 12, 40, 'Non-Tunai', [[2, 2], [4, 1], [8, 2]]],
            [5, 16, 15, 'Tunai', [[3, 2], [6, 2], [7, 2]]],

            // --- 6 HARI LALU ---
            [6, 12, 05, 'Tunai', [[1, 2], [5, 1], [8, 2]]],
            [6, 13, 30, 'Non-Tunai', [[2, 1], [4, 1], [9, 1]]],

            // --- 10 HARI LALU ---
            [10, 11, 30, 'Tunai', [[1, 3], [4, 2], [7, 3]]],
            [10, 13, 10, 'Non-Tunai', [[3, 2], [8, 2]]],

            // --- 14 HARI LALU ---
            [14, 12, 15, 'Tunai', [[2, 2], [5, 2], [9, 2]]],
            [14, 14, 00, 'Tunai', [[1, 1], [7, 1]]],

            // --- 18 HARI LALU ---
            [18, 12, 30, 'Non-Tunai', [[3, 3], [4, 2], [8, 3]]],
            [18, 17, 45, 'Tunai', [[2, 1], [6, 1], [7, 1]]],

            // --- 22 HARI LALU ---
            [22, 11, 50, 'Tunai', [[1, 2], [4, 1], [7, 2]]],
            [22, 13, 20, 'Non-Tunai', [[2, 2], [8, 2]]],

            // --- 28 HARI LALU ---
            [28, 12, 40, 'Tunai', [[3, 1], [5, 1], [9, 1]]],
            [28, 18, 10, 'Tunai', [[1, 2], [7, 2]]],
        ];

        DB::transaction(function () use ($transactionScenarios, $kasir, $menus, $now): void {
            $menuMap = $menus->keyBy('id_menu');

            foreach ($transactionScenarios as $scenario) {
                [$daysAgo, $hour, $minute, $metode, $items] = $scenario;

                $transactionTime = $now->copy()
                    ->subDays($daysAgo)
                    ->setHour($hour)
                    ->setMinute($minute)
                    ->setSecond(rand(10, 50));

                $totalTagihan = 0;
                $detailsData = [];

                foreach ($items as [$idMenu, $qty]) {
                    $menu = $menuMap->get($idMenu);
                    if (! $menu) {
                        continue;
                    }

                    $subtotal = $menu->harga * $qty;
                    $totalTagihan += $subtotal;

                    $detailsData[] = [
                        'id_menu' => $idMenu,
                        'kuantitas' => $qty,
                        'subtotal' => $subtotal,
                        'created_at' => $transactionTime,
                        'updated_at' => $transactionTime,
                    ];
                }

                $transaksi = Transaksi::create([
                    'id_user' => $kasir->id_user,
                    'total_tagihan' => $totalTagihan,
                    'metode_pembayaran' => $metode,
                    'status_pembayaran' => 'lunas',
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);

                foreach ($detailsData as $detail) {
                    $detail['id_transaksi'] = $transaksi->id_transaksi;
                    DetailTransaksi::create($detail);
                }
            }
        });

        $this->command?->info('Berhasil menambahkan '.count($transactionScenarios).' data dummy transaksi dengan variasi tanggal.');
    }
}
