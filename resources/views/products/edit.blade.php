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
    <div class="card shadow-sm border-0" style="width: 640px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-1">Rediģēt produktu</h3>
            <p class="text-center text-muted mb-4">Mainiet sludinājuma datus</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <h5 class="fw-bold mb-3">Attēli ({{ $product->images->count() }} / {{ \App\Models\Product::MAX_IMAGES }})</h5>

            @if ($product->images->isNotEmpty())
                <div class="row g-3 mb-4">
                    @foreach ($product->images as $image)
                        <div class="col-6">
                            <div class="card h-100">
                                <img src="{{ $image->url }}" alt="{{ $product->name }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                <div class="card-body p-2">
                                    @if ($image->path === $product->image)
                                        <span class="badge bg-primary d-block text-center">Galvenais attēls</span>
                                    @else
                                        <form method="POST" action="{{ route('products.images.main', [$product, $image]) }}" class="mb-1">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">Par galveno</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('Vai tiešām vēlaties dzēst šo attēlu?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Dzēst</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small">Šim produktam vēl nav neviena attēla.</p>
            @endif

            @if ($product->remainingImageSlots() > 0)
                <form method="POST" action="{{ route('products.images.store', $product) }}" enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <label for="images" class="form-label">Pievienot attēlus (vēl {{ $product->remainingImageSlots() }} vietas)</label>
                    <input type="file" class="form-control form-control-lg" id="images" name="images[]" accept="image/*" multiple required>
                    <div class="form-text">Atbalstītie formāti: JPG, PNG, GIF, WEBP (maks. 2 MB katram)</div>
                    <button type="submit" class="btn btn-outline-primary mt-2">Pievienot attēlus</button>
                </form>
            @else
                <p class="text-muted small mb-4">Sasniegts maksimālais attēlu skaits ({{ \App\Models\Product::MAX_IMAGES }}).</p>
            @endif

            <h5 class="fw-bold mb-3">Produkta dati</h5>

            <form method="POST" action="{{ route('products.update', $product) }}">
                @csrf
                @method('PUT')

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

                <button type="submit" class="btn btn-primary btn-lg w-100">Saglabāt izmaiņas</button>
                <a href="{{ route('products.mine') }}" class="btn btn-outline-secondary w-100 mt-2">Atcelt</a>
            </form>
        </div>
    </div>
</div>

@endsection