@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb')
  <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
  {{-- Stat Cards --}}
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box text-bg-primary p-2">
        <div class="inner">
          <h3>{{ $totalMenu }}</h3>
          <p>Total Menu</p>
        </div>
        <i class="small-box-icon bi bi-book"></i>
        <a href="{{ route('admin.menu.index') }}" class="small-box-footer">
          Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
        </a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box text-bg-success p-2">
        <div class="inner">
          <h3>Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</h3>
          <p>Omset Hari Ini</p>
        </div>
        <i class="small-box-icon bi bi-currency-dollar"></i>
        <a href="{{ route('admin.laporan.index') }}" class="small-box-footer">
          Lihat Laporan <i class="bi bi-arrow-right-circle-fill"></i>
        </a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box text-bg-warning p-2">
        <div class="inner">
          <h3>{{ $stokMenipis }}</h3>
          <p>Stok Menipis</p>
        </div>
        <i class="small-box-icon bi bi-exclamation-triangle"></i>
        <a href="{{ route('admin.menu.index') }}" class="small-box-footer">
          Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
        </a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box text-bg-danger p-2">
        <div class="inner">
          <h3>{{ $totalTransaksi }}</h3>
          <p>Transaksi Hari Ini</p>
        </div>
        <i class="small-box-icon bi bi-receipt"></i>
        <a href="{{ route('admin.laporan.index') }}" class="small-box-footer">
          Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body text-center">
          <h6 class="text-muted mb-1">Omset Hari Ini</h6>
          <h4 class="text-black fw-bold">Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</h4>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body text-center">
          <h6 class="text-muted mb-1">Omset Minggu Ini</h6>
          <h4 class="text-black fw-bold">Rp {{ number_format($omsetMingguIni, 0, ',', '.') }}</h4>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body text-center">
          <h6 class="text-muted mb-1">Omset Bulan Ini</h6>
          <h4 class="text-black fw-bold">Rp {{ number_format($omsetBulanIni, 0, ',', '.') }}</h4>
        </div>
      </div>
    </div>
  </div>

  {{-- Low Stock Alert --}}
  @if($menuStokKritis->count() > 0)
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3">
        <h3 class="card-title fw-bold mb-0">
          <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
          Peringatan Stok Kritis
        </h3>
      </div>
      <div class="card-body">
        <div id="stok-kritis-table"></div>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
@if($menuStokKritis->count() > 0)
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const stokData = [
      @foreach($menuStokKritis as $item)
        {
          nama_menu: @json($item->nama_menu),
          kategori: @json($item->kategori->nama_kategori ?? '-'),
          stok: {{ (int) $item->stok }},
          status: @json($item->stok <= 0 ? 'Habis' : 'Menipis'),
        },
      @endforeach
    ];

    new Tabulator('#stok-kritis-table', {
      data: stokData,
      layout: 'fitColumns',
      responsiveLayout: 'collapse',
      columns: [
        { title: 'Menu', field: 'nama_menu' },
        { title: 'Kategori', field: 'kategori', formatter: (c) => `<span class="badge text-bg-info px-2 py-1">${c.getValue()}</span>` },
        { title: 'Stok', field: 'stok', width: 100, hozAlign: 'center', sorter: 'number', formatter: (c) => `<strong>${c.getValue()}</strong>` },
        {
          title: 'Status',
          field: 'status',
          width: 120,
          hozAlign: 'center',
          formatter: (c) => {
            const val = c.getValue();
            const badge = val === 'Habis' ? 'text-bg-danger' : 'text-bg-warning';
            return `<span class="badge ${badge} px-2 py-1">${val}</span>`;
          }
        },
      ],
    });
  });
</script>
@endif
@endpush
