@extends('errors.layout')

@section('title', 'Akses Ditolak')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
</svg>
@endsection

@section('code', '403')

@section('message', 'Akses Terbatas')

@section('description')
Mohon maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi admin untuk permintaan akses atau gunakan akun yang sesuai.
@endsection
