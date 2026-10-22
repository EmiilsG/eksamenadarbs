@extends('layouts.app')

@section('title', 'Sarakste')

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
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Statistika</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('conversations.index') }}">Ziņojumi @if(($unreadMessagesCount ?? 0) > 0)<span class="badge bg-danger rounded-pill ms-1">{{ $unreadMessagesCount }}</span>@endif</a></li>
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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center gap-3">
                    <a href="{{ route('conversations.index') }}" class="text-decoration-none text-muted">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <img src="{{ $conversation->otherParticipant(auth()->user())->profile_image_url }}" alt="{{ $conversation->otherParticipant(auth()->user())->name }}" class="rounded-circle" width="40" height="40">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $conversation->otherParticipant(auth()->user())->name }}</div>
                        @if($conversation->product)
                            <a href="{{ route('products.show', $conversation->product) }}" class="small text-decoration-none text-muted">
                                {{ $conversation->product->name }} • {{ number_format($conversation->product->price, 2) }} €
                            </a>
                        @else
                            <div class="small text-muted">Produkts dzēsts</div>
                        @endif
                    </div>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @forelse($conversation->messages as $message)
                        <div class="d-flex mb-3 {{ $message->user_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="d-flex gap-2 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}" style="max-width: 75%;">
                                @if($message->user_id !== auth()->id())
                                    <img src="{{ $message->user->profile_image_url }}" alt="{{ $message->user->name }}" class="rounded-circle align-self-end" width="32" height="32">
                                @endif
                                <div>
                                    <div class="p-2 rounded-3 {{ $message->user_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}">
                                        {!! nl2br(e($message->body)) !!}
                                    </div>
                                    <div class="small text-muted mt-1 {{ $message->user_id === auth()->id() ? 'text-end' : '' }}">
                                        {{ $message->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">Vēl nav ziņu. Sāc saraksti!</div>
                    @endforelse
                </div>
                <div class="card-footer bg-white">
                    <form action="{{ route('messages.store', $conversation) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <textarea name="body" class="form-control" rows="2" placeholder="Rakstīt ziņu..." required maxlength="2000"></textarea>
                            <button class="btn btn-primary" type="submit">Sūtīt</button>
                        </div>
                        @error('body')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
