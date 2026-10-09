@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item active">Pengguna</li>
@endsection

@section('content')
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title fw-bold mb-0">Daftar Pengguna</h3>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-sm" style="width: 16rem">
            <span class="input-group-text bg-light border-end-0">
              <i class="bi bi-search" aria-hidden="true"></i>
            </span>
            <input
              id="user-table-filter"
              type="search"
              class="form-control border-start-0"
              placeholder="Cari nama / username..."
              aria-label="Filter rows"
            />
          </div>
          <a href="{{ route('admin.user.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pengguna
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

      <div id="users-table"></div>
    </div>
    <div class="card-footer bg-white border-top text-secondary small py-2">
      Total <span id="user-count" class="fw-bold">{{ $users->count() }}</span> data pengguna
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const rawData = [
      @foreach($users as $index => $user)
        {
          no: {{ $index + 1 }},
          id: {{ $user->id_user }},
          nama: @json($user->nama),
          username: @json($user->username),
          role: @json($user->role),
          created_at: @json($user->created_at->format('d/m/Y H:i')),
          edit_url: @json(route('admin.user.edit', $user->id_user)),
          delete_url: @json(route('admin.user.destroy', $user->id_user)),
          is_current_user: {{ $user->id_user === auth()->id() ? 'true' : 'false' }},
          csrf_token: @json(csrf_token()),
        },
      @endforeach
    ];

    const roleBadgeFormatter = (cell) => {
      const role = cell.getValue();
      const badgeClass = role === 'Admin' ? 'text-bg-primary' : 'text-bg-success';
      return `<span class="badge ${badgeClass} px-2 py-1">${role}</span>`;
    };

    const actionFormatter = (cell) => {
      const row = cell.getData();
      let deleteHtml = '';
      if (!row.is_current_user) {
        deleteHtml = `
          <form action="${row.delete_url}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
            <input type="hidden" name="_token" value="${row.csrf_token}">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-danger btn-sm py-1 px-2" title="Hapus">
              <i class="bi bi-trash"></i>
            </button>
          </form>
        `;
      }
      return `
        <div class="d-flex gap-1 justify-content-center">
          <a href="${row.edit_url}" class="btn btn-warning btn-sm py-1 px-2" title="Edit">
            <i class="bi bi-pencil"></i>
          </a>
          ${deleteHtml}
        </div>
      `;
    };

    const table = new Tabulator('#users-table', {
      data: rawData,
      layout: 'fitColumns',
      responsiveLayout: 'collapse',
      pagination: true,
      paginationSize: 10,
      paginationSizeSelector: [10, 25, 50, 100],
      movableColumns: true,
      placeholder: '<div class="py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada data pengguna.</div>',
      columns: [
        { title: '#', field: 'no', width: 65, hozAlign: 'center', headerSort: true },
        { title: 'Nama', field: 'nama', headerFilter: 'input' },
        { title: 'Username', field: 'username', headerFilter: 'input', formatter: (c) => `<code>${c.getValue()}</code>` },
        {
          title: 'Role',
          field: 'role',
          width: 130,
          hozAlign: 'center',
          formatter: roleBadgeFormatter,
          headerFilter: 'list',
          headerFilterParams: { values: ['', 'Admin', 'Kasir'] },
        },
        { title: 'Dibuat', field: 'created_at', width: 180, hozAlign: 'center', sorter: 'date' },
        { title: 'Aksi', field: 'id', width: 130, hozAlign: 'center', headerSort: false, formatter: actionFormatter },
      ],
    });

    document.getElementById('user-table-filter').addEventListener('input', (e) => {
      const value = e.target.value;
      if (value) {
        table.setFilter([
          [
            { field: 'nama', type: 'like', value: value },
            { field: 'username', type: 'like', value: value },
          ]
        ]);
      } else {
        table.clearFilter();
      }
    });

    document.getElementById('export-csv').addEventListener('click', () => table.download('csv', 'daftar-pengguna.csv'));
    document.getElementById('export-json').addEventListener('click', () => table.download('json', 'daftar-pengguna.json'));
    document.getElementById('print-table').addEventListener('click', () => table.print(false, true));
  });
</script>
@endpush
