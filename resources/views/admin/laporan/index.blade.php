@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan & Keuangan')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item active">Laporan</li>
@endsection

@push('styles')
<style>
  .kpi-card {
    border-radius: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
  }
  .hero-revenue-card {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 50%, #084298 100%);
    border-radius: 16px;
    position: relative;
    overflow: hidden;
  }
  .hero-revenue-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
  }
  .rank-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
  }
  .rank-1 { background: #fef3c7; color: #b45309; }
  .rank-2 { background: #f1f5f9; color: #475569; }
  .rank-3 { background: #ffedd5; color: #c2410c; }
  .rank-other { background: #f8fafc; color: #64748b; }

  /* ==============================================================
     PRINT LAYOUT STYLING (A4 Official Business Report)
     ============================================================== */
  @media print {
    @page {
      size: A4 portrait;
      margin: 12mm 10mm;
    }
    html, body {
      background: #ffffff !important;
      color: #000000 !important;
      margin: 0 !important;
      padding: 0 !important;
      font-size: 9pt !important;
      font-family: Arial, Helvetica, sans-serif !important;
    }
    /* Hide layout elements & screen UI */
    .app-header,
    .app-sidebar,
    .app-content-header,
    .app-footer,
    .breadcrumb,
    form,
    #trx-table-filter,
    .no-print,
    .d-print-none {
      display: none !important;
    }
    .app-wrapper,
    .app-main,
    .app-content,
    .container-fluid {
      padding: 0 !important;
      margin: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
      border: none !important;
      box-shadow: none !important;
    }
    /* Show print report container */
    .print-report-container {
      display: block !important;
      width: 100% !important;
    }
    .print-table {
      width: 100% !important;
      border-collapse: collapse !important;
      margin-bottom: 14px !important;
    }
    .print-table th,
    .print-table td {
      border: 1px solid #333 !important;
      padding: 5px 6px !important;
      font-size: 8.5pt !important;
      vertical-align: middle !important;
      color: #000 !important;
    }
    .print-table thead {
      display: table-header-group !important;
    }
    .print-table thead th {
      background-color: #f1f5f9 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      font-weight: bold !important;
    }
    .print-table tr {
      page-break-inside: avoid !important;
      break-inside: avoid !important;
    }
    .signature-box {
      page-break-inside: avoid !important;
      break-inside: avoid !important;
    }
  }
</style>
@endpush

@section('content')

  {{-- ============================================================== --}}
  {{-- 1. SCREEN VIEW (Visible on screen, hidden on print) --}}
  {{-- ============================================================== --}}
  <div class="d-print-none">

    {{-- Filter Card --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
      <div class="card-header bg-white py-3 border-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-funnel-fill text-primary me-2"></i>Filter Rentang Waktu
          </h5>
          <span class="badge text-black rounded-pill px-3 py-2">
            <i class="bi bi-calendar-check me-1"></i> {{ $periodLabel }}
          </span>
        </div>
      </div>
      <div class="card-body pt-0">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="row align-items-end g-3">
          <div class="col-md-3">
            <label for="filter" class="form-label fw-semibold text-secondary small">Pilihan Periode</label>
            <select class="form-select rounded-pill px-3" id="filter" name="filter" onchange="toggleCustomDate(this.value)">
              <option value="harian" {{ $filter === 'harian' ? 'selected' : '' }}>Hari Ini</option>
              <option value="mingguan" {{ $filter === 'mingguan' ? 'selected' : '' }}>Minggu Ini</option>
              <option value="bulanan" {{ $filter === 'bulanan' ? 'selected' : '' }}>Bulan Ini</option>
              <option value="custom" {{ $filter === 'custom' ? 'selected' : '' }}>Pilih Tanggal Sendiri</option>
            </select>
          </div>
          <div class="col-md-3" id="custom-start" style="{{ $filter === 'custom' ? '' : 'display:none' }}">
            <label for="tanggal_mulai" class="form-label fw-semibold text-secondary small">Tanggal Mulai</label>
            <input type="date" class="form-control rounded-pill px-3" id="tanggal_mulai" name="tanggal_mulai" value="{{ $tanggalMulai }}">
          </div>
          <div class="col-md-3" id="custom-end" style="{{ $filter === 'custom' ? '' : 'display:none' }}">
            <label for="tanggal_akhir" class="form-label fw-semibold text-secondary small">Tanggal Akhir</label>
            <input type="date" class="form-control rounded-pill px-3" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm flex-grow-1">
              <i class="bi bi-search me-1"></i> Tampilkan
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold shadow-sm" onclick="window.print()">
              <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="row g-3 mb-4">
      {{-- Total Pendapatan Hero --}}
      <div class="col-12 col-lg-4">
        <div class="card hero-revenue-card text-white shadow-sm border-0 h-100 p-3">
          <div class="card-body d-flex flex-column justify-content-between p-2">
            <div>
              <span class="text-uppercase small text-white-50 fw-semibold letter-spacing-1">
                <i class="bi bi-cash-coin me-1"></i> Total Pendapatan
              </span>
              <h2 class="display-6 fw-bold mt-2 mb-1">
                Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
              </h2>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-3 border-top border-white-50 mt-3 small">
              <span class="text-white-50">Rata-rata / Transaksi:</span>
              <span class="fw-bold">Rp {{ number_format($rataRataTransaksi, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Total Transaksi --}}
      <div class="col-6 col-md-3 col-lg-2">
        <div class="card kpi-card shadow-sm border-0 h-100 bg-white">
          <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Transaksi</span>
              <div class="badge bg-primary-subtle text-primary rounded-circle p-2">
                <i class="bi bi-receipt-cutoff fs-5"></i>
              </div>
            </div>
            <div>
              <h3 class="fw-bold text-dark mb-0">{{ $totalTransaksi }}</h3>
              <small class="text-muted">Nota Lunas</small>
            </div>
          </div>
        </div>
      </div>

      {{-- Pendapatan Tunai --}}
      <div class="col-6 col-md-3 col-lg-3">
        <div class="card kpi-card shadow-sm border-0 h-100 bg-white">
          <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Metode Tunai</span>
              <span class="badge text-black rounded-pill px-2 py-1 small fw-bold">
                {{ $persenTunai }}%
              </span>
            </div>
            <div>
              <h4 class="fw-bold text-black mb-1">Rp {{ number_format($pendapatanTunai, 0, ',', '.') }}</h4>
              <small class="text-muted">Total Pembayaran Cash</small>
            </div>
          </div>
        </div>
      </div>

      {{-- Pendapatan Non-Tunai --}}
      <div class="col-12 col-md-6 col-lg-3">
        <div class="card kpi-card shadow-sm border-0 h-100 bg-white">
          <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Metode Non-Tunai</span>
              <span class="badge text-black rounded-pill px-2 py-1 small fw-bold">
                {{ $persenNonTunai }}%
              </span>
            </div>
            <div>
              <h4 class="fw-bold text-black mb-1">Rp {{ number_format($pendapatanNonTunai, 0, ',', '.') }}</h4>
              <small class="text-muted">Total Pembayaran Non Tunai</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-4 mb-4">
      {{-- Trend Line/Bar Chart --}}
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 h-100">
          <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="bi bi-graph-up-arrow text-primary me-2"></i>Tren Penjualan Periode Ini
            </h6>
            <span class="badge bg-light text-muted fw-normal">{{ count($chartLabels) }} Titik Tanggal</span>
          </div>
          <div class="card-body pt-0">
            <div style="height: 260px;">
              <canvas id="salesTrendChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Payment Method Composition Chart --}}
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 h-100">
          <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="bi bi-pie-chart-fill text-info me-2"></i>Komposisi Pembayaran
            </h6>
          </div>
          <div class="card-body pt-0 d-flex flex-column align-items-center justify-content-center">
            <div style="height: 200px; width: 200px; position: relative;">
              <canvas id="paymentMethodChart"></canvas>
            </div>
            <div class="d-flex gap-4 mt-3 small">
              <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-circle p-1 bg-success"> </span>
                <span>Tunai: <strong>{{ $persenTunai }}%</strong></span>
              </div>
              <div class="d-flex align-items-center gap-1">
                <span class="badge rounded-circle p-1 bg-info"> </span>
                <span>Non-Tunai: <strong>{{ $persenNonTunai }}%</strong></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Top 5 Items Section --}}
    @if($itemTerlaris->count() > 0)
      <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-trophy-fill text-warning me-2"></i>5 Menu Terlaris
          </h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-muted small text-uppercase">
                <tr>
                  <th class="ps-4" style="width: 70px;">Peringkat</th>
                  <th>Nama Menu</th>
                  <th style="width: 200px;">Popularitas</th>
                  <th class="text-center" style="width: 130px;">Jumlah Terjual</th>
                  <th class="text-end pe-4" style="width: 170px;">Total Pendapatan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($topItemsData as $item)
                  <tr>
                    <td class="ps-4">
                      <span class="rank-badge {{ $item['no'] === 1 ? 'rank-1' : ($item['no'] === 2 ? 'rank-2' : ($item['no'] === 3 ? 'rank-3' : 'rank-other')) }}">
                        @if($item['no'] === 1) #1
                        @elseif($item['no'] === 2) #2
                        @elseif($item['no'] === 3) #3
                        @else #{{ $item['no'] }}
                        @endif
                      </span>
                    </td>
                    <td>
                      <div class="fw-bold text-dark">{{ $item['nama_menu'] }}</div>
                    </td>
                    <td>
                      <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $item['percent'] }}%" aria-valuenow="{{ $item['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-light text-dark fw-bold px-3 py-2 rounded-pill">
                        {{ $item['total_qty'] }} Porsi
                      </span>
                    </td>
                    <td class="text-end pe-4 fw-bold text-black">
                      Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

    {{-- Transaction List Table --}}
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header bg-white py-3 border-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-journal-text text-primary me-2"></i>Daftar Riwayat Transaksi
          </h5>
          <div class="input-group input-group-sm" style="width: 18rem">
            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3">
              <i class="bi bi-search text-muted" aria-hidden="true"></i>
            </span>
            <input
              id="trx-table-filter"
              type="search"
              class="form-control bg-light border-start-0 rounded-end-pill pe-3"
              placeholder="Cari nota, kasir, menu..."
              aria-label="Filter rows"
            />
          </div>
        </div>
      </div>
      <div class="card-body pt-0">
        <div class="d-flex gap-2 mb-3">
          <button id="export-csv" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none">
            <i class="bi bi-filetype-csv me-1 text-success" aria-hidden="true"></i> Ekspor CSV
          </button>
          <button id="export-json" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none">
            <i class="bi bi-filetype-json me-1 text-warning" aria-hidden="true"></i> Ekspor JSON
          </button>
        </div>

        <div id="transaksi-table"></div>
      </div>
      <div class="card-footer bg-white border-top text-secondary small py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          Menampilkan <strong>{{ $transaksiList->count() }}</strong> total transaksi berhasil pada periode terpilih.
        </div>
        <div class="text-muted">
          Dapur Ina Aina POS & Restoran
        </div>
      </div>
    </div>

  </div>

  {{-- ============================================================== --}}
  {{-- 2. PRINT-ONLY REPORT VIEW (Clean Multi-page A4 Format)          --}}
  {{-- ============================================================== --}}
  <div class="d-none d-print-block print-report-container">

    {{-- Official Header / Kop Surat --}}
    <div class="text-center pb-2 mb-3" style="border-bottom: 3px double #000;">
      <h1 style="font-size: 18pt; font-weight: bold; margin-bottom: 2px; letter-spacing: 1px;">DAPUR INA AINA</h1>
      <div style="font-size: 9.5pt; color: #333; margin-bottom: 2px;">Sistem Informasi POS & Manajemen Restoran</div>
      <div style="font-size: 9pt; color: #555;">Jl. Raya Kuliner No. 12 | Telp: 0812-3456-7890</div>
    </div>

    <div class="text-center mb-3">
      <h2 style="font-size: 13pt; font-weight: bold; margin-bottom: 4px; text-transform: uppercase;">
        Laporan Rekapitulasi Penjualan & Keuangan
      </h2>
      <div style="font-size: 9pt; color: #333;">
        Periode: <strong>{{ $periodLabel }}</strong> &nbsp;|&nbsp; Dicetak: <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong> &nbsp;|&nbsp; Oleh: <strong>{{ Auth::user()->nama }}</strong>
      </div>
    </div>

    {{-- I. Ringkasan Keuangan --}}
    <div style="margin-bottom: 14px;">
      <div style="font-size: 9.5pt; font-weight: bold; margin-bottom: 5px;">I. RINGKASAN REKAPITULASI PENJUALAN</div>
      <table class="print-table">
        <thead>
          <tr>
            <th style="width: 18%; text-align: center;">Total Transaksi</th>
            <th style="width: 25%; text-align: center;">Total Pendapatan</th>
            <th style="width: 21%; text-align: center;">Metode Tunai</th>
            <th style="width: 21%; text-align: center;">Metode Non-Tunai</th>
            <th style="width: 15%; text-align: center;">Rata-rata/Trx</th>
          </tr>
        </thead>
        <tbody>
          <tr style="text-align: center;">
            <td style="font-weight: bold; font-size: 11pt;">{{ $totalTransaksi }}</td>
            <td style="font-weight: bold; font-size: 11pt;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($pendapatanTunai, 0, ',', '.') }} <small>({{ $persenTunai }}%)</small></td>
            <td>Rp {{ number_format($pendapatanNonTunai, 0, ',', '.') }} <small>({{ $persenNonTunai }}%)</small></td>
            <td>Rp {{ number_format($rataRataTransaksi, 0, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- II. 5 Menu Terlaris --}}
    @if($itemTerlaris->count() > 0)
      <div style="margin-bottom: 14px;">
        <div style="font-size: 9.5pt; font-weight: bold; margin-bottom: 5px;">II. 5 MENU TERLARIS</div>
        <table class="print-table">
          <thead>
            <tr>
              <th style="width: 45px; text-align: center;">No</th>
              <th>Nama Menu</th>
              <th style="width: 130px; text-align: center;">Jumlah Terjual</th>
              <th style="width: 160px; text-align: right;">Total Pendapatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($topItemsData as $item)
              <tr>
                <td style="text-align: center; font-weight: bold;">{{ $item['no'] }}</td>
                <td style="font-weight: bold;">{{ $item['nama_menu'] }}</td>
                <td style="text-align: center;">{{ $item['total_qty'] }} Porsi</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    {{-- III. Rincian Lengkap Seluruh Transaksi --}}
    <div style="margin-bottom: 18px;">
      <div style="font-size: 9.5pt; font-weight: bold; margin-bottom: 5px;">
        III. RINCIAN LENGKAP TRANSAKSI PENJUALAN (TOTAL: {{ $transaksiList->count() }} TRANSAKSI)
      </div>
      <table class="print-table">
        <thead>
          <tr>
            <th style="width: 32px; text-align: center;">No</th>
            <th style="width: 90px; text-align: center;">No. Nota</th>
            <th style="width: 95px; text-align: center;">Waktu</th>
            <th style="width: 90px;">Kasir</th>
            <th>Item Pesanan</th>
            <th style="width: 80px; text-align: center;">Metode</th>
            <th style="width: 105px; text-align: right;">Total (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transaksiList as $index => $trx)
            <tr>
              <td style="text-align: center;">{{ $index + 1 }}</td>
              <td style="font-family: monospace; font-weight: bold; text-align: center;">
                INV-{{ str_pad($trx->id_transaksi, 6, '0', STR_PAD_LEFT) }}
              </td>
              <td style="text-align: center; font-size: 8pt;">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
              <td style="font-size: 8pt;">{{ $trx->user->nama ?? '-' }}</td>
              <td style="font-size: 8pt;">
                {{ $trx->detailTransaksi->map(fn($d) => ($d->menu->nama_menu ?? '-') . ' (' . $d->kuantitas . 'x)')->implode(', ') }}
              </td>
              <td style="text-align: center; font-size: 8pt;">{{ $trx->metode_pembayaran }}</td>
              <td style="text-align: right; font-weight: bold;">{{ number_format($trx->total_tagihan, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align: center; padding: 12px;">Tidak ada data transaksi pada periode ini.</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="6" style="text-align: right; font-size: 9pt;">TOTAL KESELURUHAN:</td>
            <td style="text-align: right; font-size: 9pt;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- IV. Kolom Tanda Tangan Pengesahan --}}
    <div class="signature-box" style="margin-top: 30px; page-break-inside: avoid; break-inside: avoid;">
      <table style="width: 100%; border: none;">
        <tr>
          <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
            <div style="font-size: 9pt;">Mengetahui,</div>
            <div style="font-size: 9pt; font-weight: bold; margin-bottom: 50px;">Manajer Restoran</div>
            <div style="font-weight: bold; text-decoration: underline;">( ......................................... )</div>
          </td>
          <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
            <div style="font-size: 9pt;">Dibuat Oleh,</div>
            <div style="font-size: 9pt; font-weight: bold; margin-bottom: 50px;">Petugas Kasir / Administrasi</div>
            <div style="font-weight: bold; text-decoration: underline;">( {{ Auth::user()->nama }} )</div>
          </td>
        </tr>
      </table>
    </div>

  </div>

@endsection

@push('scripts')
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
  function toggleCustomDate(value) {
    const show = value === 'custom';
    document.getElementById('custom-start').style.display = show ? '' : 'none';
    document.getElementById('custom-end').style.display = show ? '' : 'none';
  }

  document.addEventListener('DOMContentLoaded', () => {
    // 1. Chart: Tren Penjualan
    const chartLabels = @json($chartLabels);
    const chartRevenues = @json($chartRevenues);
    const chartCounts = @json($chartCounts);

    const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
    if (trendCtx) {
      new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: chartLabels.length > 0 ? chartLabels : ['Hari Ini'],
          datasets: [
            {
              label: 'Pendapatan (Rp)',
              data: chartRevenues.length > 0 ? chartRevenues : [0],
              borderColor: '#0d6efd',
              backgroundColor: 'rgba(13, 110, 253, 0.08)',
              borderWidth: 2.5,
              fill: true,
              tension: 0.35,
              pointRadius: 4,
              pointHoverRadius: 6,
              pointBackgroundColor: '#0d6efd',
              yAxisID: 'y',
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (context) => 'Rp ' + Number(context.parsed.y).toLocaleString('id-ID')
              }
            }
          },
          scales: {
            x: {
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value) => 'Rp ' + Number(value).toLocaleString('id-ID')
              }
            }
          }
        }
      });
    }

    // 2. Chart: Komposisi Metode Pembayaran
    const methodCtx = document.getElementById('paymentMethodChart')?.getContext('2d');
    if (methodCtx) {
      const tunaiVal = {{ $pendapatanTunai }};
      const nonTunaiVal = {{ $pendapatanNonTunai }};

      new Chart(methodCtx, {
        type: 'doughnut',
        data: {
          labels: ['Tunai', 'Non-Tunai'],
          datasets: [{
            data: (tunaiVal === 0 && nonTunaiVal === 0) ? [1, 0] : [tunaiVal, nonTunaiVal],
            backgroundColor: ['#198754', '#0dcaf0'],
            borderWidth: 2,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const val = context.parsed;
                  return context.label + ': Rp ' + Number(val).toLocaleString('id-ID');
                }
              }
            }
          },
          cutout: '70%'
        }
      });
    }

    // 3. Tabulator: Daftar Transaksi (Screen Interactive Table)
    const trxData = @json($transaksiData);

    const trxTable = new Tabulator('#transaksi-table', {
      data: trxData,
      layout: 'fitColumns',
      responsiveLayout: 'collapse',
      pagination: true,
      paginationSize: 10,
      paginationSizeSelector: [10, 25, 50, 100],
      movableColumns: true,
      placeholder: '<div class="py-5 text-muted text-center"><i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>Tidak ada transaksi pada periode ini.</div>',
      columns: [
        {
          title: 'No. Nota',
          field: 'no_nota',
          width: 140,
          formatter: (c) => `<span class="badge bg-light text-dark border font-monospace px-2 py-1">${c.getValue()}</span>`
        },
        {
          title: 'Tanggal',
          field: 'tanggal',
          width: 145,
          hozAlign: 'center',
          sorter: 'date'
        },
        {
          title: 'Kasir',
          field: 'kasir',
          width: 130
        },
        {
          title: 'Item Pesanan',
          field: 'items'
        },
        {
          title: 'Total Tagihan',
          field: 'total_tagihan',
          width: 145,
          hozAlign: 'right',
          sorter: 'number',
          formatter: (c) => `<strong class="text-black">Rp ${Number(c.getValue()).toLocaleString('id-ID')}</strong>`
        },
        {
          title: 'Metode',
          field: 'metode',
          width: 120,
          hozAlign: 'center',
          headerFilter: 'list',
          headerFilterParams: { values: ['', 'Tunai', 'Non-Tunai'] },
          formatter: (c) => {
            const val = c.getValue();
            const badge = val === 'Tunai' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-info-subtle text-info border border-info-subtle';
            return `<span class="badge ${badge} px-2 py-1 rounded-pill">${val}</span>`;
          }
        },
        {
          title: 'Aksi',
          field: 'receipt_url',
          width: 120,
          hozAlign: 'center',
          headerSort: false,
          formatter: (c) => {
            const url = c.getValue();
            return `<a href="${url}?autoprint=1" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" title="Cetak Struk">
                      <i class="bi bi-printer me-1"></i> Struk
                    </a>`;
          }
        }
      ],
    });

    document.getElementById('trx-table-filter').addEventListener('input', (e) => {
      const value = e.target.value;
      if (value) {
        trxTable.setFilter([
          [
            { field: 'no_nota', type: 'like', value: value },
            { field: 'kasir', type: 'like', value: value },
            { field: 'items', type: 'like', value: value },
          ]
        ]);
      } else {
        trxTable.clearFilter();
      }
    });

    document.getElementById('export-csv').addEventListener('click', () => trxTable.download('csv', 'laporan-transaksi-dapur-ina-aina.csv'));
    document.getElementById('export-json').addEventListener('click', () => trxTable.download('json', 'laporan-transaksi-dapur-ina-aina.json'));
  });
</script>
@endpush
