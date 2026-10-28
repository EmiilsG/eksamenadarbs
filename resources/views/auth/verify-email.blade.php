@extends('layouts.app')

@section('title', 'E-pasta apstiprināšana - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Iestatījumi" aria-label="Iestatījumi">&#9881;</button>
        </div>
    </div>
</nav>

<div class="d-flex align-items-center justify-content-center py-5" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="max-width: 560px;">
        <div class="card-body p-5 text-center">
            <div class="display-4 mb-3">&#9993;</div>
            <h3 class="fw-bold mb-3">Apstipriniet savu e-pastu</h3>

            @if (session('success'))
                <div class="alert alert-success text-start">{{ session('success') }}</div>
            @endif

            <p class="text-muted mb-4">
                Mēs nosūtījām verifikācijas saiti uz <strong>{{ auth()->user()->email }}</strong>.
                Atveriet to, lai apstiprinātu kontu un iegūtu pilnu piekļuvi.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="d-grid gap-2 mb-3">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">Nosūtīt saiti vēlreiz</button>
            </form>

            <p class="small text-muted mb-3">
                Nesaņēmāt e-pastu? Pārbaudiet mapi "Spam" vai uzgaidiet dažas minūtes.
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">Uz sākumu</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Izrakstīties</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
