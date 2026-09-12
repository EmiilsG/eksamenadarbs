@extends('layouts.app')

@section('title', $profileUser->name . ' - Profils')

@section('content')

<style>
    .profile-cover {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        height: 220px;
        border-radius: 0 0 1rem 1rem;
        position: relative;
    }
    .profile-avatar-wrap {
        position: absolute;
        left: 50%;
        bottom: -64px;
        transform: translateX(-50%);
        border: 6px solid #fff;
        border-radius: 50%;
        box-shadow: 0 8px 24px rgba(0,0,0,.15);
    }
    .profile-avatar {
        width: 128px;
        height: 128px;
        border-radius: 50%;
        object-fit: cover;
        background: #fff;
    }
    .star { color: #ffc107; font-size: 1.15rem; }
    .star.empty { color: #dee2e6; }
    .stat-box { border: 1px solid #e9ecef; border-radius: .75rem; }
</style>

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

<section class="pb-5" style="min-height: 80vh;">
    <div class="profile-cover"></div>

    <div class="container position-relative" style="margin-top: 64px;">
        <div class="d-flex flex-column align-items-center mb-4">
            <div class="profile-avatar-wrap">
                <img src="{{ $profileUser->profile_image_url }}" alt="{{ $profileUser->name }}" class="profile-avatar">
            </div>
            <h2 class="fw-bold mt-4 mb-0 text-center">{{ $profileUser->name }}</h2>
            <p class="text-muted mb-2">{{ $profileUser->email }}</p>

            <div class="d-flex align-items-center gap-1 mb-1">
                @for ($i = 1; $i <= 5; $i++)
                    <span class="star {{ $i <= round($averageRating) ? '' : 'empty' }}">&#9733;</span>
                @endfor
                <span class="ms-2 fw-bold">{{ number_format($averageRating, 1) }}</span>
                <span class="text-muted">/ 5</span>
            </div>
            <p class="text-muted small mb-0">{{ $reviewsCount }} atsauksme(-s)</p>
        </div>

        <div class="row g-3 mb-5" style="max-width: 720px; margin: 0 auto;">
            <div class="col-md-4">
                <div class="stat-box text-center p-3">
                    <div class="fw-bold fs-4">{{ $reviewsCount }}</div>
                    <div class="text-muted small">Atsauksmes</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box text-center p-3">
                    <div class="fw-bold fs-4">{{ $productsCount }}</div>
                    <div class="text-muted small">Sludinājumi</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box text-center p-3">
                    <div class="fw-bold fs-4">{{ $profileUser->created_at->format('Y') }}</div>
                    <div class="text-muted small">Reģistrējies {{ $profileUser->created_at->format('d.m.Y') }}</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
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

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-chat-square-text me-2"></i>Pārdevēja atsauksmes</h5>
                    </div>
                    <div class="card-body p-4">
                        @auth
                            @if (!$isOwnProfile)
                                <form method="POST" action="{{ route('reviews.store', $profileUser) }}" class="bg-light p-4 rounded-3 mb-4">
                                    @csrf
                                    <h6 class="fw-bold mb-3">Novērtējiet šo pārdevēju</h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Vērtējums</label>
                                        <div class="btn-group" role="group" aria-label="Vērtējums">
                                            @for ($i = 5; $i >= 1; $i--)
                                                <input type="radio" class="btn-check" name="rating" id="rating{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }}>
                                                <label class="btn btn-outline-warning" for="rating{{ $i }}">{{ $i }} &#9733;</label>
                                            @endfor
                                        </div>
                                        @error('rating')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="comment" class="form-label fw-semibold">Atsauksmes teksts</label>
                                        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Pastāstiet par savu pieredzi ar šo pārdevēju...">{{ old('comment') }}</textarea>
                                        @error('comment')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary">Iesniegt atsauksmi</button>
                                </form>
                            @endif
                        @endauth

                        @if ($reviews->isEmpty())
                            <p class="text-muted text-center py-4 mb-0">Šim lietotājam vēl nav atsauksmju.</p>
                        @else
                            @foreach ($reviews as $review)
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
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white py-4 mt-4">
    <div class="container text-center">
        <p class="mb-1">&copy; {{ date('Y') }} Marketplace. Visas tiesības aizsargātas.</p>
    </div>
</footer>

@endsection
