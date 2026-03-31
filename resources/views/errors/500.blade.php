@extends('errors.layout')

@section('title', 'Kesalahan Internal Server')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
</svg>
@endsection

@section('code', '500')

@section('message', 'Terjadi Masalah Teknis')

@section('description')
Sistem mengalami gangguan saat memproses permintaan Anda. Tim kami telah menerima notifikasi ini. Jika masih berlanjut, mohon hubungi admin.
@endsection
