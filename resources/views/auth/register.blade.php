@extends('layouts.app')

@section('title', 'Reģistrācija - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
    </div>
</nav>

<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="width: 440px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-4">Reģistrācija</h3>

            <form method="POST" action="#">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Vārds</label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-pasts</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Parole</label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Atkārtota parole</label>
                    <input type="password" class="form-control form-control-lg" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Reģistrēties</button>
            </form>
        </div>
    </div>
</div>

@endsection
