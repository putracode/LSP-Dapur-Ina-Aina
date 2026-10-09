<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalMenu = Menu::count();
        $stokMenipis = Menu::where('stok', '>', 0)->where('stok', '<=', 5)->count();
        $stokHabis = Menu::where('stok', '<=', 0)->count();

        $omsetHariIni = Transaksi::where('status_pembayaran', 'lunas')
            ->whereDate('created_at', $today)
            ->sum('total_tagihan');

        $omsetMingguIni = Transaksi::where('status_pembayaran', 'lunas')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('total_tagihan');

        $omsetBulanIni = Transaksi::where('status_pembayaran', 'lunas')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_tagihan');

        $totalTransaksi = Transaksi::where('status_pembayaran', 'lunas')
            ->whereDate('created_at', $today)
            ->count();

        $menuStokKritis = Menu::where('stok', '<=', 5)
            ->orderBy('stok')
            ->with('kategori')
            ->get();

        return view('admin.dashboard', compact(
            'totalMenu',
            'stokMenipis',
            'stokHabis',
            'omsetHariIni',
            'omsetMingguIni',
            'omsetBulanIni',
            'totalTransaksi',
            'menuStokKritis',
        ));
    }
}
