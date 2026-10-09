<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * Display the sales report.
     */
    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'harian');
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalAkhir = $request->get('tanggal_akhir');

        $query = Transaksi::where('status_pembayaran', 'lunas')->with(['detailTransaksi.menu', 'user']);

        switch ($filter) {
            case 'harian':
                $query->whereDate('created_at', Carbon::today());
                $periodLabel = 'Hari Ini ('.Carbon::today()->format('d/m/Y').')';
                break;
            case 'mingguan':
                $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                $periodLabel = 'Minggu Ini ('.Carbon::now()->startOfWeek()->format('d/m').' - '.Carbon::now()->endOfWeek()->format('d/m/Y').')';
                break;
            case 'bulanan':
                $query->where('created_at', '>=', Carbon::now()->startOfMonth());
                $periodLabel = 'Bulan '.Carbon::now()->translatedFormat('F Y');
                break;
            case 'custom':
                if ($tanggalMulai && $tanggalAkhir) {
                    $query->whereDate('created_at', '>=', $tanggalMulai)
                        ->whereDate('created_at', '<=', $tanggalAkhir);
                    $periodLabel = Carbon::parse($tanggalMulai)->format('d/m/Y').' - '.Carbon::parse($tanggalAkhir)->format('d/m/Y');
                } else {
                    $periodLabel = 'Custom (pilih tanggal)';
                }
                break;
            default:
                $query->whereDate('created_at', Carbon::today());
                $periodLabel = 'Hari Ini';
        }

        $transaksiList = $query->latest()->get();

        $totalTransaksi = $transaksiList->count();
        $totalPendapatan = (int) $transaksiList->sum('total_tagihan');
        $pendapatanTunai = (int) $transaksiList->where('metode_pembayaran', 'Tunai')->sum('total_tagihan');
        $pendapatanNonTunai = (int) $transaksiList->where('metode_pembayaran', 'Non-Tunai')->sum('total_tagihan');
        $rataRataTransaksi = $totalTransaksi > 0 ? (int) round($totalPendapatan / $totalTransaksi) : 0;

        $persenTunai = $totalPendapatan > 0 ? round(($pendapatanTunai / $totalPendapatan) * 100, 1) : 0;
        $persenNonTunai = $totalPendapatan > 0 ? round(($pendapatanNonTunai / $totalPendapatan) * 100, 1) : 0;

        $itemTerlaris = DetailTransaksi::whereIn('id_transaksi', $transaksiList->pluck('id_transaksi'))
            ->selectRaw('id_menu, SUM(kuantitas) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('id_menu')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('menu')
            ->get();

        $maxQty = $itemTerlaris->max('total_qty') ?: 1;

        $transaksiData = $transaksiList->map(function ($trx) {
            $items = $trx->detailTransaksi->map(function ($d) {
                $name = $d->menu->nama_menu ?? '-';

                return "{$name} ({$d->kuantitas}x)";
            })->implode(', ');

            return [
                'id_transaksi' => $trx->id_transaksi,
                'no_nota' => 'INV-'.str_pad((string) $trx->id_transaksi, 6, '0', STR_PAD_LEFT),
                'tanggal' => $trx->created_at->format('d/m/Y H:i'),
                'kasir' => $trx->user->nama ?? '-',
                'items' => $items,
                'total_tagihan' => (int) $trx->total_tagihan,
                'metode' => $trx->metode_pembayaran,
                'receipt_url' => route('kasir.receipt', $trx->id_transaksi),
            ];
        })->values()->all();

        $topItemsData = $itemTerlaris->values()->map(function ($item, $index) use ($maxQty) {
            return [
                'no' => $index + 1,
                'nama_menu' => $item->menu->nama_menu ?? '-',
                'total_qty' => (int) $item->total_qty,
                'total_revenue' => (int) $item->total_revenue,
                'percent' => round(($item->total_qty / $maxQty) * 100),
            ];
        })->all();

        // Chart data: chronological order
        $dailyTrend = $transaksiList->sortBy('created_at')->groupBy(function ($trx) {
            return $trx->created_at->format('d/m');
        });

        $chartLabels = $dailyTrend->keys()->values()->all();
        $chartRevenues = $dailyTrend->map(fn ($group) => (int) $group->sum('total_tagihan'))->values()->all();
        $chartCounts = $dailyTrend->map(fn ($group) => $group->count())->values()->all();

        return view('admin.laporan.index', compact(
            'filter',
            'periodLabel',
            'transaksiList',
            'transaksiData',
            'topItemsData',
            'totalTransaksi',
            'totalPendapatan',
            'pendapatanTunai',
            'pendapatanNonTunai',
            'rataRataTransaksi',
            'persenTunai',
            'persenNonTunai',
            'itemTerlaris',
            'tanggalMulai',
            'tanggalAkhir',
            'chartLabels',
            'chartRevenues',
            'chartCounts',
        ));
    }
}
