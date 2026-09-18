@extends('layouts.root.main')

@section('main')
@include('pages.partials.settings-styles')

<div class="settings-page">
    @if (session('settings_success'))
        <div class="settings-alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('settings_success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="settings-alert is-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="settings-head">
        <div>
            <h1 class="settings-title">Manage Device</h1>
            <div class="settings-subtitle">Pantau scanner pulling aktif, reset local data, dan kelola device yang terdaftar.</div>
        </div>
        <div class="settings-icon">
            <i class="fas fa-barcode"></i>
        </div>
    </section>

    @include('pages.partials.device-monitoring', ['deviceSessions' => $deviceSessions])
</div>

@include('pages.partials.device-monitoring-modals')
@include('pages.partials.device-monitoring-scripts')
@endsection
