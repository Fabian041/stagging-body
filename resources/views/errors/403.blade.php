@extends('errors.layout')

@section('accent', '#dc2626')
@section('tint', '#fef2f2')
@section('emoji', '🔒')
@section('code', 'Error 403 · Akses Ditolak')
@section('heading', 'Anda tidak punya akses')
@section('message')
    {{ (isset($exception) && $exception->getMessage()) ? $exception->getMessage() : 'Aksi ini hanya untuk role tertentu (mis. Admin / PPIC). Hubungi admin jika Anda merasa seharusnya punya akses.' }}
@endsection
