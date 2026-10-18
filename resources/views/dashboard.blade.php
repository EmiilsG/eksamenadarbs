@extends('layouts.app')

@section('title', 'Pārdevēja statistika - Marketplace')

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
                <li class="nav-item"><a class="nav-link" href="{{ route('products.mine') }}">Mani produkti</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('favorites.index') }}">Favorīti</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('dashboard') }}">Statistika</a></li>
            </ul>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Iestatījumi" aria-label="Iestatījumi">&#9881;</button>
                <a href="{{ route('profile') }}" class="btn btn-outline-light me-2">Mans profils</a>
                <a href="{{ route('products.create') }}" class="btn btn-primary me-2">Pievienot produktu</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">Izrakstīties</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<section class="py-5 bg-light" style="min-height: 80vh;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
            <div>
                <h1 class="fw-bold mb-0">Pārdevēja statistika</h1>
                <p class="text-muted mb-0">Jūsu snieguma pārskats &middot; vidēji {{ number_format($viewsPerDay, 1) }} skatījumi dienā &middot; konts aktīvs {{ $daysActive }} dienas</p>
            </div>
            <a href="{{ route('products.mine') }}" class="btn btn-outline-primary">Mani produkti</a>
        </div>

        @if ($totalProducts === 0)
            <div class="alert alert-info d-flex justify-content-between align-items-center mt-4 mb-0 flex-wrap gap-2">
                <span>Jums vēl nav neviena sludinājuma &mdash; pievienojiet pirmo produktu, lai redzētu statistiku.</span>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">Pievienot produktu</a>
            </div>
        @endif

        <div class="row g-3 mt-1">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold text-primary">{{ number_format($totalViews) }}</div>
                        <div class="text-muted small">Skatījumi</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold text-primary">{{ $totalProducts }}</div>
                        <div class="text-muted small">Produkti</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold text-primary">{{ number_format($totalFavorites) }}</div>
                        <div class="text-muted small">Favorīti</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold text-primary">{{ $reviewsCount }}</div>
                        <div class="text-muted small">Atsauksmes</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold text-primary">{{ number_format($averageRating, 1) }}</div>
                        <div class="text-muted small">Vidējais vērtējums</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold text-primary">&euro;{{ number_format($totalValue, 0) }}</div>
                        <div class="text-muted small">Kopējā vērtība</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Populārākie produkti</h5>
                        <span class="text-muted small">pēc skatījumiem</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($topProducts->isEmpty())
                            <p class="text-muted text-center py-4 mb-0">Vēl nav produktu.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;">#</th>
                                            <th>Produkts</th>
                                            <th style="width: 30%;">Skatījumi</th>
                                            <th class="text-center">Favorīti</th>
                                            <th class="text-end">Cena</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topProducts as $index => $product)
                                            <tr>
                                                <td class="text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <a href="{{ route('products.show', $product) }}" class="text-decoration-none fw-semibold">{{ $product->name }}</a>
                                                    <div><span class="badge bg-secondary">{{ $product->category }}</span></div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar" style="width: {{ round($product->views_count / $maxTopViews * 100) }}%"></div>
                                                        </div>
                                                        <span class="small fw-semibold text-nowrap">{{ number_format($product->views_count) }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ $product->favorites_count }}</td>
                                                <td class="text-end fw-semibold">&euro;{{ number_format($product->price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Kategoriju sadalījums</h5>
                    </div>
                    <div class="card-body">
                        @if ($categoryBreakdown->isEmpty())
                            <p class="text-muted text-center py-3 mb-0">Vēl nav produktu.</p>
                        @else
                            @php($maxCategory = max(1, $categoryBreakdown->max('count')))
                            @foreach ($categoryBreakdown as $category)
                                <div class="mb-3 {{ $loop->last ? 'mb-0' : '' }}">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold small">{{ $category['name'] }}</span>
                                        <span class="text-muted small">{{ $category['count'] }} gab. &middot; {{ number_format($category['views']) }} skat.</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: {{ round($category['count'] / $maxCategory * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Atsauksmes pa zvaigznēm</h5>
                    </div>
                    <div class="card-body">
                        @if ($reviewsCount === 0)
                            <p class="text-muted text-center py-3 mb-0">Vēl nav atsauksmju.</p>
                        @else
                            @for ($stars = 5; $stars >= 1; $stars--)
                                @php($count = (int) $ratingDistribution->get($stars, 0))
                                <div class="d-flex align-items-center gap-2 mb-2 {{ $stars === 1 ? 'mb-0' : '' }}">
                                    <span class="text-nowrap small" style="width: 52px;">
                                        {{ $stars }} <span class="text-warning">&#9733;</span>
                                    </span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar" style="width: {{ round($count / $reviewsCount * 100) }}%"></div>
                                    </div>
                                    <span class="small text-muted text-end" style="width: 32px;">{{ $count }}</span>
                                </div>
                            @endfor
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Aktivitāte pēc mēneša</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-end gap-2 text-center" style="height: 170px;">
                            @foreach ($monthlyActivity as $month)
                                <div class="d-flex flex-column justify-content-end h-100 flex-fill">
                                    <div class="small fw-bold text-muted mb-1">{{ $month['count'] }}</div>
                                    <div class="rounded-top" style="height: {{ max(4, (int) round($month['count'] / $maxMonthly * 110)) }}px; background-color: var(--bs-primary); opacity: .85;"></div>
                                    <div class="small text-muted border-top mt-1 pt-1">{{ $month['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Kopsavilkums</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Vidējā cena</span>
                            <span class="fw-bold">&euro;{{ number_format($averagePrice, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Vidēji skatījumi uz produktu</span>
                            <span class="fw-bold">{{ $totalProducts > 0 ? number_format($totalViews / $totalProducts, 1) : '0.0' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Vidēji skatījumi dienā</span>
                            <span class="fw-bold">{{ number_format($viewsPerDay, 1) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Favorīti uz produktu</span>
                            <span class="fw-bold">{{ $totalProducts > 0 ? number_format($totalFavorites / $totalProducts, 1) : '0.0' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Konts aktīvs</span>
                            <span class="fw-bold">{{ $daysActive }} dienas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Pēdējās atsauksmes</h5>
            </div>
            <div class="card-body p-4">
                @if ($recentReviews->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Vēl nav atsauksmju.</p>
                @else
                    @foreach ($recentReviews as $review)
                        <div class="d-flex gap-3 {{ $loop->last ? '' : 'border-bottom pb-3 mb-3' }}">
                            <img src="{{ $review->reviewer->profile_image_url }}" alt="{{ $review->reviewer->name }}" class="rounded-circle" width="48" height="48" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $review->reviewer->name }}</strong>
                                        <div class="text-muted small">{{ $review->created_at->format('d.m.Y') }}</div>
                                    </div>
                                    <div>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $review->rating ? 'text-warning' : 'text-body-secondary' }}">&#9733;</span>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mb-0 mt-2 text-muted">{{ $review->comment }}</p>
                            </div>
                        </div>
                    @endforeach
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
