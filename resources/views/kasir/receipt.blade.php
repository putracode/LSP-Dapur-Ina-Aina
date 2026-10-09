<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Struk #INV-{{ str_pad($transaksi->id_transaksi, 6, '0', STR_PAD_LEFT) }} - Dapur Ina Aina</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Courier New', Courier, monospace, sans-serif;
      font-size: 12px;
      line-height: 1.35;
      color: #000;
      background-color: #f1f5f9;
      padding: 20px 10px;
    }
    .receipt-container {
      background: #fff;
      width: 100%;
      max-width: 80mm;
      margin: 0 auto;
      padding: 5mm 4mm;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border-radius: 4px;
    }
    .text-center { text-align: center; }
    .text-end, .right { text-align: right; }
    .bold, .fw-bold { font-weight: bold; }
    .separator {
      border-top: 1px dashed #000;
      margin: 6px 0;
      width: 100%;
      display: block;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    td {
      padding: 2px 0;
      vertical-align: top;
    }
    .small {
      font-size: 10px;
      color: #444;
    }
    .store-title {
      font-size: 16px;
      font-weight: bold;
      letter-spacing: 0.5px;
    }
    .store-sub {
      font-size: 10px;
      color: #555;
    }
    .action-bar {
      max-width: 80mm;
      margin: 15px auto 0;
      display: flex;
      gap: 10px;
      justify-content: center;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 18px;
      border-radius: 50px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      border: 1px solid transparent;
      transition: all 0.2s;
    }
    .btn-print {
      background: #0d6efd;
      color: #fff;
      border-color: #0d6efd;
    }
    .btn-print:hover {
      background: #0b5ed7;
    }
    .btn-close-win {
      background: #e2e8f0;
      color: #334155;
      border-color: #cbd5e1;
    }
    .btn-close-win:hover {
      background: #cbd5e1;
    }

    @media print {
      body {
        background: #fff;
        padding: 0;
        margin: 0;
      }
      .receipt-container {
        max-width: 80mm;
        width: 80mm;
        box-shadow: none;
        border-radius: 0;
        padding: 4mm 3mm;
        margin: 0;
      }
      .no-print {
        display: none !important;
      }
      @page {
        size: 80mm auto;
        margin: 0;
      }
    }
  </style>
</head>
<body>

  <div class="receipt-container" id="receiptCard">
    {{-- Header --}}
    <div class="text-center">
      <div class="store-title">DAPUR INA AINA</div>
      <div class="store-sub">Sistem Informasi POS & Restoran</div>
      <div class="store-sub">Jl. Raya Kuliner No. 12</div>
      <div class="store-sub">Telp: 0812-3456-7890</div>
    </div>

    <div class="separator"></div>

    {{-- Metadata --}}
    <table>
      <tr>
        <td>No. Nota</td>
        <td class="right bold">INV-{{ str_pad($transaksi->id_transaksi, 6, '0', STR_PAD_LEFT) }}</td>
      </tr>
      <tr>
        <td>Tanggal</td>
        <td class="right">{{ $transaksi->created_at->format('d/m/Y H:i:s') }}</td>
      </tr>
      <tr>
        <td>Kasir</td>
        <td class="right">{{ $transaksi->user->nama ?? '-' }}</td>
      </tr>
      <tr>
        <td>Status</td>
        <td class="right bold">{{ strtoupper($transaksi->status_pembayaran) }}</td>
      </tr>
    </table>

    <div class="separator"></div>

    {{-- Items --}}
    <table>
      <thead>
        <tr class="bold" style="border-bottom: 1px dashed #bbb;">
          <td style="padding-bottom: 4px;">Item Menu</td>
          <td class="text-center" style="width: 30px; padding-bottom: 4px;">Qty</td>
          <td class="right" style="width: 80px; padding-bottom: 4px;">Subtotal</td>
        </tr>
      </thead>
      <tbody>
        @foreach($transaksi->detailTransaksi as $detail)
          <tr>
            <td colspan="3" style="padding-top: 4px;" class="bold">
              {{ $detail->menu->nama_menu ?? 'Item dihapus' }}
            </td>
          </tr>
          <tr style="border-bottom: 1px dotted #ddd;">
            <td class="small" style="padding-bottom: 4px;">
              @ Rp {{ number_format($detail->menu->harga ?? ($detail->subtotal / max(1, $detail->kuantitas)), 0, ',', '.') }}
            </td>
            <td class="text-center small" style="padding-bottom: 4px;">{{ $detail->kuantitas }}x</td>
            <td class="right small bold" style="padding-bottom: 4px;">
              Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="separator"></div>

    {{-- Totals --}}
    <table>
      <tr class="bold" style="font-size: 13px;">
        <td>TOTAL TAGIHAN</td>
        <td class="right">Rp {{ number_format($transaksi->total_tagihan, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Metode Bayar</td>
        <td class="right bold">{{ $transaksi->metode_pembayaran }}</td>
      </tr>
      <tr>
        <td>Pembayaran</td>
        <td class="right bold" style="color: #0d6efd;">LUNAS</td>
      </tr>
    </table>

    <div class="separator"></div>

    {{-- Footer --}}
    <div class="text-center" style="margin-top: 6px;">
      <div class="bold">Terima Kasih Atas Kunjungan Anda!</div>
      <div class="small">Selamat Menikmati Hidangan Kami</div>
    </div>
  </div>

  {{-- On-Screen Actions (Hidden when printing) --}}
  <div class="action-bar no-print">
    <button type="button" class="btn btn-print" onclick="window.print()">
      <i class="bi bi-printer"></i> Cetak Struk
    </button>
    <button type="button" class="btn btn-close-win" onclick="window.close()">
      <i class="bi bi-x-circle"></i> Tutup
    </button>
  </div>

  <script>
    // Auto-print if query param autoprint=1
    const params = new URLSearchParams(window.location.search);
    if (params.get('autoprint') === '1') {
      window.addEventListener('load', () => {
        setTimeout(() => {
          window.print();
        }, 300);
      });
    }
  </script>
</body>
</html>
