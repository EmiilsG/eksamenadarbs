@extends('layouts.app')

@section('title', 'Mani produkti - Marketplace')

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
                <li class="nav-item"><a class="nav-link active" href="{{ route('products.mine') }}">Mani produkti</a></li>
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

<section class="py-5 bg-light" style="min-height: 80vh;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h1 class="fw-bold mb-0">Mani produkti</h1>
            <a href="{{ route('products.create') }}" class="btn btn-primary">+ Pievienot produktu</a>
        </div>
        <p class="text-muted mb-4">{{ count($products) }} prece(-s)</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($products->isEmpty())
            <div class="text-center py-5 mt-5">
                <p class="lead text-muted">Jums vēl nav neviena sludinājuma.</p>
                <a href="{{ route('products.create') }}" class="btn btn-primary mt-2">Pievienot pirmo produktu</a>
            </div>
        @else
            <div class="row g-4">
                @foreach ($products as $product)
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100 overflow-hidden">
                            <div class="position-relative">
                                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                <span class="badge bg-primary position-absolute top-0 start-0 m-2">{{ $product->category }}</span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold mb-1">{{ $product->name }}</h5>
                                <div class="small text-muted mb-2">
                                    Pievienots: {{ $product->created_at->format('d.m.Y') }}
                                </div>
                                <p class="card-text text-muted small flex-grow-1">{{ \Illuminate\Support\Str::limit($product->description, 80) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fs-5 fw-bold text-primary">&euro;{{ number_format($product->price, 2) }}</span>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Vai tiešām vēlaties dzēst šo produktu?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Dzēst</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-1">&copy; {{ date('Y') }} Marketplace. Visas tiesības aizsargātas.</p>
    </div>
</footer>

@endsection