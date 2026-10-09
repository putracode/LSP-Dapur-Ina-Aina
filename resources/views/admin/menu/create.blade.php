@extends('layouts.admin')

@section('title', 'Tambah Menu')
@section('page-title', 'Tambah Menu')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.menu.index') }}">Menu</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@push('styles')
    <!-- FilePond CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/filepond@4.32.12/dist/filepond.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/filepond-plugin-image-preview@4.6.12/dist/filepond-plugin-image-preview.min.css" />
    <style>
        .filepond--root {
            margin-bottom: 0;
            font-family: inherit;
        }

        .filepond--panel-root {
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
        }

        .filepond--drop-label {
            color: #64748b;
            cursor: pointer;
        }

        .filepond--label-action {
            text-decoration-color: #0d6efd;
            color: #0d6efd;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline mb-4">
                <div class="card-header">
                    <div class="card-title">Tambah Kategori</div>
                </div>
                <form action="/admin/menu" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nama_menu" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_menu') is-invalid @enderror"
                                name="nama_menu" placeholder="Nama Menu" id="nama_menu"
                                autocomplete="off" />
                            @error('nama_menu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('id_kategori') is-invalid @enderror" id="id_kategori"
                                name="id_kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id_kategori }}"
                                        {{ old('id_kategori') == $kategori->id_kategori ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('harga') is-invalid @enderror"
                                    id="harga" name="harga" value="{{ old('harga') }}" placeholder="0" required
                                    min="0" step="500">
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('stok') is-invalid @enderror"
                                    id="stok" name="stok" value="{{ old('stok', 0) }}" placeholder="0" required
                                    min="0">
                                @error('stok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Menu<span class="text-danger">*</span></label>
                            <input type="file" class="filepond" id="foto" name="foto"
                                accept="image/png,image/jpeg,image/jpg,image/gif,image/webp">
                            <small class="text-muted d-block mt-1">Format: JPEG, PNG, JPG, GIF, WebP. Maks: 2MB</small>
                            @error('foto')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="/admin/menu" class="btn btn-danger float-end me-2">Kembali</a>
                        <button type="submit" class="btn btn-primary float-end me-2">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- FilePond Plugins & JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/filepond-plugin-file-validate-type@1.2.9/dist/filepond-plugin-file-validate-type.min.js">
    </script>
    <script
        src="https://cdn.jsdelivr.net/npm/filepond-plugin-file-validate-size@2.2.8/dist/filepond-plugin-file-validate-size.min.js">
    </script>
    <script
        src="https://cdn.jsdelivr.net/npm/filepond-plugin-image-preview@4.6.12/dist/filepond-plugin-image-preview.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/filepond@4.32.12/dist/filepond.min.js"></script>

    <script>
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginFileValidateSize,
            FilePondPluginImagePreview
        );

        const inputElement = document.querySelector('input.filepond');
        const pond = FilePond.create(inputElement, {
            storeAsFile: true,
            allowMultiple: false,
            maxFileSize: '2MB',
            acceptedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'],
            labelIdle: 'Tarik & Letakkan foto atau <span class="filepond--label-action">Pilih File</span>',
            labelFileWaitingForSize: 'Menghitung ukuran file...',
            labelFileSizeNotAvailable: 'Ukuran tidak tersedia',
            labelFileLoading: 'Memuat...',
            labelFileLoadError: 'Gagal memuat file',
            labelFileProcessing: 'Mengupload...',
            labelFileProcessingComplete: 'Upload selesai',
            labelFileProcessingAborted: 'Upload dibatalkan',
            labelFileProcessingError: 'Terjadi kesalahan saat upload',
            labelTapToCancel: 'ketuk untuk membatalkan',
            labelTapToRetry: 'ketuk untuk mencoba lagi',
            labelTapToUndo: 'ketuk untuk menghapus',
            labelButtonRemoveItem: 'Hapus',
            labelButtonAbortItemLoad: 'Batal',
            labelButtonRetryItemLoad: 'Ulangi',
            labelButtonAbortItemProcessing: 'Batal',
            labelButtonStopItemProcessing: 'Stop',
            labelButtonProcessItem: 'Upload',
            labelMaxFileSizeExceeded: 'Ukuran file terlalu besar',
            labelMaxFileSize: 'Ukuran maksimum adalah {filesize}',
            labelFileTypeNotAllowed: 'Tipe file tidak valid',
            fileValidateTypeLabelExpectedTypes: 'Mendukung format gambar JPEG, PNG, GIF, WebP',
            imagePreviewHeight: 200,
        });
    </script>
@endpush
