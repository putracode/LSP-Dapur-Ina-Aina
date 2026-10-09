@extends('layouts.admin')

@section('title', 'Data Menu & Stok')
@section('page-title', 'Data Menu & Stok')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item active">Menu</li>
@endsection

@section('content')
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title fw-bold mb-0">Daftar Menu</h3>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-sm" style="width: 16rem">
            <span class="input-group-text bg-light border-end-0">
              <i class="bi bi-search" aria-hidden="true"></i>
            </span>
            <input
              id="menu-table-filter"
              type="search"
              class="form-control border-start-0"
              placeholder="Cari nama menu / kategori..."
              aria-label="Filter rows"
            />
          </div>
          <a href="{{ route('admin.menu.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Tambah Menu
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

      <div id="menu-table"></div>
    </div>
    <div class="card-footer bg-white border-top text-secondary small py-2">
      Total <span id="menu-count" class="fw-bold">{{ $menus->count() }}</span> data menu
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const rawData = [
      @foreach($menus as $index => $menu)
        {
          no: {{ $index + 1 }},
          id: {{ $menu->id_menu }},
          foto_url: @json($menu->foto ? asset('storage/' . $menu->foto) : null),
          nama_menu: @json($menu->nama_menu),
          kategori: @json($menu->kategori->nama_kategori ?? '-'),
          harga: {{ (int) $menu->harga }},
          stok: {{ (int) $menu->stok }},
          status: @json($menu->isOutOfStock() ? 'Habis' : ($menu->isLowStock() ? 'Menipis' : 'Tersedia')),
          edit_url: @json(route('admin.menu.edit', $menu->id_menu)),
          delete_url: @json(route('admin.menu.destroy', $menu->id_menu)),
          csrf_token: @json(csrf_token()),
        },
      @endforeach
    ];

    const imageFormatter = (cell) => {
      const url = cell.getValue();
      const nama = cell.getRow().getData().nama_menu;
      if (url) {
        return `<img src="${url}" alt="${nama}" class="rounded shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">`;
      }
      return `
        <div class="bg-secondary-subtle rounded d-flex align-items-center justify-content-center mx-auto" style="width: 44px; height: 44px;">
          <i class="bi bi-cup-hot text-secondary fs-5"></i>
        </div>
      `;
    };

    const statusBadgeFormatter = (cell) => {
      const val = cell.getValue();
      const map = {
        'Tersedia': 'success',
        'Menipis': 'warning',
        'Habis': 'danger',
      };
      const badgeClass = map[val] || 'secondary';
      return `<span class="badge text-bg-${badgeClass} px-2 py-1">${val}</span>`;
    };

    const priceFormatter = (cell) => {
      const val = cell.getValue();
      return 'Rp ' + Number(val).toLocaleString('id-ID');
    };

    const actionFormatter = (cell) => {
      const row = cell.getData();
      return `
        <div class="d-flex gap-1 justify-content-center">
          <a href="${row.edit_url}" class="btn btn-warning btn-sm py-1 px-2" title="Edit">
            <i class="bi bi-pencil"></i>
          </a>
          <form action="${row.delete_url}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
            <input type="hidden" name="_token" value="${row.csrf_token}">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-danger btn-sm py-1 px-2" title="Hapus">
              <i class="bi bi-trash"></i>
            </button>
          </form>
        </div>
      `;
    };

    const table = new Tabulator('#menu-table', {
      data: rawData,
      layout: 'fitColumns',
      responsiveLayout: 'collapse',
      pagination: true,
      paginationSize: 10,
      paginationSizeSelector: [10, 25, 50, 100],
      movableColumns: true,
      placeholder: '<div class="py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada data menu.</div>',
      columns: [
        { title: '#', field: 'no', width: 65, hozAlign: 'center', headerSort: true },
        { title: 'Foto', field: 'foto_url', width: 75, hozAlign: 'center', headerSort: false, formatter: imageFormatter },
        { title: 'Nama Menu', field: 'nama_menu', headerFilter: 'input' },
        {
          title: 'Kategori',
          field: 'kategori',
          headerFilter: 'list',
          headerFilterParams: { valuesLookup: true, clearable: true },
          formatter: (cell) => `<span class="badge text-black px-2 py-1">${cell.getValue()}</span>`
        },
        { title: 'Harga', field: 'harga', formatter: priceFormatter, hozAlign: 'right', sorter: 'number' },
        { title: 'Stok', field: 'stok', width: 90, hozAlign: 'center', sorter: 'number', formatter: (c) => `<strong>${c.getValue()}</strong>` },
        {
          title: 'Status',
          field: 'status',
          width: 110,
          hozAlign: 'center',
          formatter: statusBadgeFormatter,
          headerFilter: 'list',
          headerFilterParams: { values: ['', 'Tersedia', 'Menipis', 'Habis'] },
        },
        { title: 'Aksi', field: 'id', width: 130, hozAlign: 'center', headerSort: false, formatter: actionFormatter },
      ],
    });

    document.getElementById('menu-table-filter').addEventListener('input', (e) => {
      const value = e.target.value;
      if (value) {
        table.setFilter([
          [
            { field: 'nama_menu', type: 'like', value: value },
            { field: 'kategori', type: 'like', value: value },
          ]
        ]);
      } else {
        table.clearFilter();
      }
    });

    document.getElementById('export-csv').addEventListener('click', () => table.download('csv', 'daftar-menu.csv'));
    document.getElementById('export-json').addEventListener('click', () => table.download('json', 'daftar-menu.json'));
    document.getElementById('print-table').addEventListener('click', () => table.print(false, true));
  });
</script>
@endpush
