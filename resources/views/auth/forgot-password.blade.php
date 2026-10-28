@extends('layouts.app')

@section('title', 'Aizmirsi paroli - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Iestatījumi" aria-label="Iestatījumi">&#9881;</button>
        </div>
    </div>
</nav>

<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="width: 440px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-3">Aizmirsi paroli?</h3>
            <p class="text-muted text-center mb-4">
                Ievadiet savu e-pastu, un mēs nosūtīsim saiti paroles atiestatīšanai.
            </p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first('email') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">E-pasts</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Nosūtīt atiestatīšanas saiti</button>

                <p class="text-center mb-0">
                    <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Atgriezties pie pieteikšanās</a>
                </p>
            </form>
        </div>
    </div>
</div>

@endsection
