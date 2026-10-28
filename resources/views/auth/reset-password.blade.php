@extends('layouts.app')

@section('title', 'Jauna parole - Marketplace')

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
    <div class="card shadow-sm border-0" style="width: 460px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-4">Jauna parole</h3>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label for="email" class="form-label">E-pasts</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" value="{{ old('email', $request->email) }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Jauna parole</label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Atkārtota jaunā parole</label>
                    <input type="password" class="form-control form-control-lg" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Saglabāt jauno paroli</button>
            </form>
        </div>
    </div>
</div>

@endsection
