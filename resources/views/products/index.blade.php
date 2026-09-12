@extends('layouts.app')

@section('title', 'Visas preces - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Pārslēgt navigāciju">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="{{ route('products.index') }}">Preces</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('favorites.index') }}">Favorīti</a></li>
            </ul>
            <div class="d-flex align-items-center">
                @auth
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
        <h1 class="fw-bold mb-1">Visas preces</h1>
        <p class="text-muted mb-4">{{ count($products) }} prece(-s)</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Filtri</h6>

                        <form method="GET" action="{{ route('products.index') }}">
                            <div class="mb-3">
                                <label for="search" class="form-label fw-semibold">Meklēt</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Preces nosaukums...">
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label fw-semibold">Kategorija</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Visas kategorijas</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Cena (&euro;)</label>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control" name="min_price" placeholder="No" value="{{ request('min_price') }}">
                                    <input type="number" class="form-control" name="max_price" placeholder="Līdz" value="{{ request('max_price') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="sort" class="form-label fw-semibold">Kārtot pēc</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="" @selected(request('sort') === '')>Jaunākās</option>
                                    <option value="price_low" @selected(request('sort') === 'price_low')>Lētākās</option>
                                    <option value="price_high" @selected(request('sort') === 'price_high')>Dārgākās</option>
                                    <option value="rating" @selected(request('sort') === 'rating')>Labāk novērtētie pārdevēji</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Filtrēt</button>
                            @if (request()->anyFilled(['search', 'category', 'min_price', 'max_price', 'sort']))
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 mt-2">Notīrīt filtrus</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                @if ($products->isEmpty())
                    <div class="text-center py-5 mt-5">
                        <p class="lead text-muted">Nav atrasta neviena prece ar šādu meklēšanu.</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($products as $product)
                            <div class="col-md-6 col-xl-4">
                                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                                    <div class="position-relative">
                                        <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-2">{{ $product->category }}</span>
                                        @auth
                                            <form method="POST" action="{{ in_array($product->id, $favoritedIds) ? route('favorites.destroy', $product) : route('favorites.store', $product) }}" class="position-absolute top-0 end-0 m-2">
                                                @csrf
                                                @if (in_array($product->id, $favoritedIds))
                                                    @method('DELETE')
                                                @endif
                                                <button type="submit" class="btn btn-sm {{ in_array($product->id, $favoritedIds) ? 'btn-danger' : 'btn-outline-danger bg-white' }}" title="{{ in_array($product->id, $favoritedIds) ? 'Noņemt no favorītiem' : 'Pievienot favorītiem' }}">
                                                    &#9829;
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger bg-white position-absolute top-0 end-0 m-2" title="Piesakieties, lai pievienotu favorītiem">&#9829;</a>
                                        @endauth
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold mb-1">{{ $product->name }}</h5>
                                        <div class="small text-muted mb-2">
                                            Pārdevējs: {{ $product->user->name }}
                                            <span class="text-warning">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= round($product->user->rating))
                                                        &#9733;
                                                    @else
                                                        &#9734;
                                                    @endif
                                                @endfor
                                            </span>
                                            <span class="text-muted">({{ number_format($product->user->rating, 1) }})</span>
                                        </div>
                                        <p class="card-text text-muted small flex-grow-1">{{ \Illuminate\Support\Str::limit($product->description, 80) }}</p>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span class="fs-5 fw-bold text-primary">&euro;{{ number_format($product->price, 2) }}</span>
                                            @auth
                                                @if (auth()->id() === $product->user_id)
                                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Vai tiešām vēlaties dzēst šo produktu?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">Dzēst</button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </div>
                                        <a href="#" class="btn btn-outline-primary w-100 mt-3">Apskatīt</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-1">&copy; {{ date('Y') }} Marketplace. Visas tiesības aizsargātas.</p>
    </div>
</footer>

@endsection
