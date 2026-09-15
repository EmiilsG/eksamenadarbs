@extends('layouts.app')

@section('title', 'Rediģēt profilu - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Pārslēgt navigāciju">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Preces</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('favorites.index') }}">Favorīti</a></li>
            </ul>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Iestatījumi" aria-label="Iestatījumi">&#9881;</button>
                @auth
                    <a href="{{ route('profile') }}" class="btn btn-outline-light me-2">Mans profils</a>
                    <a href="{{ route('products.create') }}" class="btn btn-primary me-2">Pievienot produktu</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-light">Izrakstīties</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>

<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="width: 560px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-1">Rediģēt profilu</h3>
            <p class="text-center text-muted mb-4">Mainiet savu vārdu, aprakstu un profila bildi</p>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="text-center mb-4">
                    <img src="{{ $profileUser->profile_image_url }}" alt="{{ $profileUser->name }}" class="rounded-circle" width="112" height="112" style="object-fit: cover;">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Vārds</label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name" value="{{ old('name', $profileUser->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="bio" class="form-label">Apraksts (pēc izvēles)</label>
                    <textarea class="form-control form-control-lg" id="bio" name="bio" rows="4" placeholder="Pastāstiet par sevi, savu pieredzi pārdošanā...">{{ old('bio', $profileUser->bio) }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="profile_image" class="form-label">Profila bilde (pēc izvēles)</label>
                    <input type="file" class="form-control form-control-lg" id="profile_image" name="profile_image" accept="image/*">
                    <div class="form-text">Atbalstītie formāti: JPG, PNG, GIF, WEBP (maks. 2 MB)</div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Saglabāt izmaiņas</button>
                <a href="{{ route('profile') }}" class="btn btn-outline-secondary w-100 mt-2">Atcelt</a>
            </form>
        </div>
    </div>
</div>

@endsection