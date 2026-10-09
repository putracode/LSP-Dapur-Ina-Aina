@extends('layouts.admin')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale (POS)')
@section('breadcrumb')
  <li class="breadcrumb-item active">POS</li>
@endsection

@section('content')
  @livewire('kasir.pos-cart')
@endsection
