@extends('layouts.app')

@section('title', $product->name . ' - Marketplace')

@section('content')

<style>
    .star { color: #ffc107; font-size: 1.15rem; }
    .star.empty { color: #dee2e6; }
    .stat-box { border: 1px solid #e9ecef; border-radius: .75rem; }
    .product-image { width: 100%; height: 420px; object-fit: cover; }
    .description-text { white-space: pre-line; }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Pārslēgt navigāciju">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="{{ route('products.index') }}">Preces</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products.mine') }}">Mani produkti</a></li>
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
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light me-2">Pieteikties</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Reģistrēties</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<section class="py-5 bg-light" style="min-height: 80vh;">
    <div class="container">
        <nav aria-label="Vadīklīnija">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Sākums</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Preces</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('products.index', ['category' => $product->category]) }}">{{ $product->category }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <img src="{{ $product->image_url }}" class="product-image" alt="{{ $product->name }}">
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Apraksts</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="description-text mb-0">{{ $product->description }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <span class="badge bg-primary mb-3">{{ $product->category }}</span>
                        <h1 class="fw-bold mb-2">{{ $product->name }}</h1>

                        <div class="d-flex align-items-center gap-1 mb-3">
                            <a href="{{ route('profile.user', $seller) }}" class="text-decoration-none text-muted">
                                Pārdevējs: <strong class="text-body">{{ $seller->name }}</strong>
                            </a>
                        </div>

                        <div class="d-flex align-items-center gap-1 mb-3">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= round($sellerRating) ? '' : 'empty' }}">&#9733;</span>
                            @endfor
                            <span class="ms-2 fw-bold">{{ number_format($sellerRating, 1) }}</span>
                            <span class="text-muted">/ 5</span>
                            <span class="text-muted small">({{ $sellerReviewsCount }} atsauksme(-s))</span>
                        </div>

                        <div class="fs-3 fw-bold text-primary mb-1">&euro;{{ number_format($product->price, 2) }}</div>
                        <p class="text-muted small mb-3">
                            Publicēts {{ $product->created_at->format('d.m.Y') }}
                            &middot; {{ $favoritesCount }} favorīt(-s)
                        </p>

                        @auth
                            <form method="POST" action="{{ $isFavorited ? route('favorites.destroy', $product) : route('favorites.store', $product) }}" class="mb-2">
                                @csrf
                                @if ($isFavorited)
                                    @method('DELETE')
                                @endif
                                <button type="submit" class="btn {{ $isFavorited ? 'btn-danger' : 'btn-outline-danger' }} w-100">
                                    &#9829; {{ $isFavorited ? 'Noņemt no favorītiem' : 'Pievienot favorītiem' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-danger w-100 mb-2">&#9829; Pievienot favorītiem</a>
                        @endauth

                        @auth
                            @if ($product->user_id === auth()->id())
                                <div class="d-flex gap-2">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary flex-fill">Rediģēt</a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Vai tiešām vēlaties dzēst šo produktu?');" class="flex-fill">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger w-100">Dzēst</button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Pārdevējs</h5>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="{{ $seller->profile_image_url }}" alt="{{ $seller->name }}" class="rounded-circle" width="64" height="64" style="object-fit: cover;">
                            <div>
                                <a href="{{ route('profile.user', $seller) }}" class="fw-bold text-decoration-none">{{ $seller->name }}</a>
                                <div class="text-muted small">Reģistrējies {{ $seller->created_at->format('Y') }}</div>
                            </div>
                        </div>
                        @if ($seller->bio)
                            <p class="text-muted small mb-3">{{ $seller->bio }}</p>
                        @endif
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="stat-box p-2">
                                    <div class="fw-bold">{{ number_format($sellerRating, 1) }}</div>
                                    <div class="text-muted small">Vērtējums</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-box p-2">
                                    <div class="fw-bold">{{ $sellerProductsCount }}</div>
                                    <div class="text-muted small">Sludinājumi</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-box p-2">
                                    <div class="fw-bold">{{ $favoritesCount }}</div>
                                    <div class="text-muted small">Favorīti</div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('profile.user', $seller) }}" class="btn btn-outline-primary w-100 mt-3">Skatīt pārdevēja profilu</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Atsauksmes par pārdevēju</h5>
            </div>
            <div class="card-body p-4">
                @if ($sellerReviews->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Šim pārdevējam vēl nav atsauksmju.</p>
                @else
                    @foreach ($sellerReviews as $review)
                        <div class="d-flex gap-3 {{ $loop->last ? '' : 'border-bottom pb-3 mb-3' }}">
                            <img src="{{ $review->reviewer->profile_image_url }}" alt="{{ $review->reviewer->name }}" class="rounded-circle" width="48" height="48" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $review->reviewer->name }}</strong>
                                        <div class="text-muted small">{{ $review->created_at->format('d.m.Y') }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span class="star {{ $i <= $review->rating ? '' : 'empty' }}">&#9733;</span>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mb-0 mt-2">{{ $review->comment }}</p>
                            </div>
                        </div>
                    @endforeach
                    <a href="{{ route('profile.user', $seller) }}" class="btn btn-outline-primary w-100 mt-3">Visas atsauksmes</a>
                @endif
            </div>
        </div>

        @if ($otherProducts->isNotEmpty())
            <h2 class="fw-bold mt-5 mb-3">Citi šī pārdevēja produkti</h2>
            <div class="row g-4">
                @foreach ($otherProducts as $other)
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100 overflow-hidden">
                            <a href="{{ route('products.show', $other) }}">
                                <img src="{{ $other->image_url }}" class="card-img-top" alt="{{ $other->name }}" style="height: 200px; object-fit: cover;">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-primary align-self-start mb-2">{{ $other->category }}</span>
                                <h3 class="card-title fw-bold fs-5 mb-2">{{ $other->name }}</h3>
                                <p class="card-text text-muted small flex-grow-1">{{ \Illuminate\Support\Str::limit($other->description, 80) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fs-5 fw-bold text-primary">&euro;{{ number_format($other->price, 2) }}</span>
                                    <a href="{{ route('products.show', $other) }}" class="btn btn-outline-primary btn-sm">Apskatīt</a>
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
