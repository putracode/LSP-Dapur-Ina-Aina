@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.kategori.index') }}">Kategori</a></li>
  <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline mb-4">
                <div class="card-header">
                    <div class="card-title">Edit Kategori</div>
                </div>
                <form action="/admin/kategori/{{ $kategori->id_kategori }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror" name="nama_kategori" placeholder="Nama Kategori"
                                id="nama_kategori" autocomplete="off" value="{{ old('nama_kategori', $kategori->nama_kategori) }}"/>
                            @error('nama_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary float-end me-2">Submit</button>
                        <a href="{{ url()->previous() }}" class="btn btn-danger float-end me-2">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
