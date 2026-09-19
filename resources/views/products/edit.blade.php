@extends('layouts.app')

@section('title', 'Rediģēt produktu - Marketplace')

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

<div class="d-flex align-items-center justify-content-center py-5" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="width: 520px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-1">Rediģēt produktu</h3>
            <p class="text-center text-muted mb-4">Mainiet sludinājuma datus</p>

            @if ($product->image && !str_starts_with($product->image, 'http'))
                <div class="text-center mb-3">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-3 shadow-sm" style="max-height: 160px; object-fit: cover;">
                </div>
            @endif

            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nosaukums</label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Apraksts</label>
                    <textarea class="form-control form-control-lg" id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Cena (&euro;)</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-lg" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Kategorija</label>
                    <select class="form-select form-select-lg" id="category" name="category">
                        <option value="" @selected(old('category', $product->category) === '')>Cita</option>
                        <option value="Elektronika" @selected(old('category', $product->category) === 'Elektronika')>Elektronika</option>
                        <option value="Mēbeles" @selected(old('category', $product->category) === 'Mēbeles')>Mēbeles</option>
                        <option value="Sports" @selected(old('category', $product->category) === 'Sports')>Sports</option>
                        <option value="Auto" @selected(old('category', $product->category) === 'Auto')>Auto</option>
                        <option value="Bērniem" @selected(old('category', $product->category) === 'Bērniem')>Bērniem</option>
                        <option value="Rotaļlietas" @selected(old('category', $product->category) === 'Rotaļlietas')>Rotaļlietas</option>
                        <option value="Apģērbs" @selected(old('category', $product->category) === 'Apģērbs')>Apģērbs</option>
                        @if ($product->category && !in_array($product->category, ['Elektronika', 'Mēbeles', 'Sports', 'Auto', 'Bērniem', 'Rotaļlietas', 'Apģērbs']))
                            <option value="{{ $product->category }}" selected>{{ $product->category }}</option>
                        @endif
                    </select>
                </div>

                <div class="mb-4">
                    <label for="image" class="form-label">Produkta bilde (pēc izvēles)</label>
                    <input type="file" class="form-control form-control-lg" id="image" name="image" accept="image/*">
                    <div class="form-text">Atstājiet tukšu, lai saglabātu esošo bildi. Atbalstītie formāti: JPG, PNG, GIF, WEBP (maks. 2 MB)</div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Saglabāt izmaiņas</button>
                <a href="{{ route('products.mine') }}" class="btn btn-outline-secondary w-100 mt-2">Atcelt</a>
            </form>
        </div>
    </div>
</div>

@endsection