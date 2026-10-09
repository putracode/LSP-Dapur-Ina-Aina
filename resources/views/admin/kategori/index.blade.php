@extends('layouts.admin')

@section('title', 'Manajemen Kategori')
@section('page-title', 'Manajemen Kategori Menu')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item active">Kategori</li>
@endsection

@section('content')
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title fw-bold mb-0">Daftar Kategori</h3>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-sm" style="width: 16rem">
            <span class="input-group-text bg-light border-end-0">
              <i class="bi bi-search" aria-hidden="true"></i>
            </span>
            <input
              id="kategori-table-filter"
              type="search"
              class="form-control border-start-0"
              placeholder="Cari kategori..."
              aria-label="Filter rows"
            />
          </div>
          <a href="{{ route('admin.kategori.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button id="export-csv" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-filetype-csv me-1" aria-hidden="true"></i> Export CSV
        </button>
        <button id="export-json" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-filetype-json me-1" aria-hidden="true"></i> Export JSON
        </button>
        <button id="print-table" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-printer me-1" aria-hidden="true"></i> Print
        </button>
      </div>

      <div id="kategori-table"></div>
    </div>
    <div class="card-footer bg-white border-top text-secondary small py-2">
      Total <span id="kategori-count" class="fw-bold">{{ $kategoris->count() }}</span> data kategori
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const rawData = [
      @foreach($kategoris as $index => $kategori)
        {
          no: {{ $index + 1 }},
          id: {{ $kategori->id_kategori }},
          nama_kategori: @json($kategori->nama_kategori),
          menu_count: {{ $kategori->menu_count }},
          created_at: @json($kategori->created_at->format('d/m/Y H:i')),
          edit_url: @json(route('admin.kategori.edit', $kategori->id_kategori)),
          delete_url: @json(route('admin.kategori.destroy', $kategori->id_kategori)),
          csrf_token: @json(csrf_token()),
        },
      @endforeach
    ];

    const actionFormatter = (cell) => {
      const row = cell.getData();
      return `
        <div class="d-flex gap-1 justify-content-center">
          <a href="${row.edit_url}" class="btn btn-warning btn-sm py-1 px-2" title="Edit">
            <i class="bi bi-pencil"></i>
          </a>
          <form action="${row.delete_url}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
            <input type="hidden" name="_token" value="${row.csrf_token}">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-danger btn-sm py-1 px-2" title="Hapus">
              <i class="bi bi-trash"></i>
            </button>
          </form>
        </div>
      `;
    };

    const countFormatter = (cell) => {
      const val = cell.getValue();
      return `<span class="badge text-bg-info text-white px-2 py-1">${val} Menu</span>`;
    };

    const table = new Tabulator('#kategori-table', {
      data: rawData,
      layout: 'fitColumns',
      responsiveLayout: 'collapse',
      pagination: true,
      paginationSize: 10,
      paginationSizeSelector: [10, 25, 50, 100],
      movableColumns: true,
      placeholder: '<div class="py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada data kategori.</div>',
      columns: [
        { title: '#', field: 'no', width: 65, hozAlign: 'center', headerSort: true },
        { title: 'Nama Kategori', field: 'nama_kategori', headerFilter: 'input' },
        { title: 'Jumlah Menu', field: 'menu_count', width: 140, hozAlign: 'center', formatter: countFormatter },
        { title: 'Dibuat', field: 'created_at', width: 180, hozAlign: 'center', sorter: 'date' },
        { title: 'Aksi', field: 'id', width: 130, hozAlign: 'center', headerSort: false, formatter: actionFormatter },
      ],
    });

    document.getElementById('kategori-table-filter').addEventListener('input', (e) => {
      const value = e.target.value;
      if (value) {
        table.setFilter('nama_kategori', 'like', value);
      } else {
        table.clearFilter();
      }
    });

    document.getElementById('export-csv').addEventListener('click', () => table.download('csv', 'daftar-kategori.csv'));
    document.getElementById('export-json').addEventListener('click', () => table.download('json', 'daftar-kategori.json'));
    document.getElementById('print-table').addEventListener('click', () => table.print(false, true));
  });
</script>
@endpush
