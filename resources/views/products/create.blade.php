@extends('layouts.app')

@section('title', 'Pievienot produktu - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Iestatījumi" aria-label="Iestatījumi">&#9881;</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light me-2">Produkti</a>
            <a href="{{ route('products.mine') }}" class="btn btn-outline-light me-2">Mani produkti</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light">Izrakstīties</button>
            </form>
        </div>
    </div>
</nav>

<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="width: 520px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-4">Pievienot produktu</h3>

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nosaukums</label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Apraksts</label>
                    <textarea class="form-control form-control-lg" id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Cena (&euro;)</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-lg" id="price" name="price" value="{{ old('price') }}" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Kategorija</label>
                    <select class="form-select form-select-lg" id="category" name="category">
                        <option value="">Cita</option>
                        <option value="Elektronika" @selected(old('category') === 'Elektronika')>Elektronika</option>
                        <option value="Mēbeles" @selected(old('category') === 'Mēbeles')>Mēbeles</option>
                        <option value="Sports" @selected(old('category') === 'Sports')>Sports</option>
                        <option value="Auto" @selected(old('category') === 'Auto')>Auto</option>
                        <option value="Bērniem" @selected(old('category') === 'Bērniem')>Bērniem</option>
                        <option value="Rotaļlietas" @selected(old('category') === 'Rotaļlietas')>Rotaļlietas</option>
                        <option value="Apģērbs" @selected(old('category') === 'Apģērbs')>Apģērbs</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="image" class="form-label">Produkta bilde (pēc izvēles)</label>
                    <input type="file" class="form-control form-control-lg" id="image" name="image" accept="image/*">
                    <div class="form-text">Atbalstītie formāti: JPG, PNG, GIF, WEBP (maks. 2 MB)</div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Saglabāt produktu</button>
            </form>
        </div>
    </div>
</div>

@endsection
