@extends('layouts.app')

@section('title', 'Ziņojumi')

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Mani ziņojumi</h1>
    </div>

    @if($conversations->isEmpty())
        <div class="text-center text-muted py-5">
            <p class="mb-0">Vēl nav neviena sarakstes.</p>
        </div>
    @else
        <div class="list-group shadow-sm">
            @foreach($conversations as $conversation)
                @php
                    $other = $conversation->otherParticipant(auth()->user());
                    $unread = $conversation->unread_count ?? 0;
                    $lastMessage = $conversation->latestMessage;
                @endphp
                <a href="{{ route('conversations.show', $conversation) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                    <img src="{{ $other->profile_image_url }}" alt="{{ $other->name }}" class="rounded-circle" width="48" height="48">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $other->name }}</div>
                                <div class="small text-muted text-truncate">{{ $conversation->product->name ?? 'Produkts dzēsts' }}</div>
                            </div>
                            <div class="text-end">
                                @if($conversation->last_message_at)
                                    <div class="small text-muted">{{ $conversation->last_message_at->diffForHumans() }}</div>
                                @endif
                                @if($unread > 0)
                                    <span class="badge bg-primary rounded-pill mt-1">{{ $unread }}</span>
                                @endif
                            </div>
                        </div>
                        @if($lastMessage)
                            <div class="small text-muted text-truncate mt-1">
                                @if($lastMessage->user_id === auth()->id())
                                    Tu:
                                @endif
                                {{ $lastMessage->body }}
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
