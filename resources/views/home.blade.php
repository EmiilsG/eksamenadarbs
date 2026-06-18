@extends('layouts.app')

@section('title', 'Marketplace - Pērc un pārdod droši')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <div class="d-flex">
            @auth
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
</nav>

<section class="bg-primary text-white py-5" style="min-height: 500px;">
    <div class="container h-100 d-flex align-items-center">
        <div class="row w-100">
            <div class="col-lg-7">
                <h1 class="display-3 fw-bold mb-3">Pērc un pārdod droši</h1>
                <p class="lead mb-4 fs-5">Mūsu platforma nodrošina drošu un uzticamu vidi preču pirkšanai un pārdošanai. Aizsargāti darījumi, pārbaudīti pārdevēji un godīgas atsauksmes.</p>
                <a href="#" class="btn btn-light btn-lg px-4 fw-semibold">Apskatīt preces</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="card-body">
                        <div class="display-4 mb-3">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                        <h5 class="card-title fw-bold">Pārdevēju vērtējumi</h5>
                        <p class="card-text text-muted">Novērtējiet pārdevējus pēc pieredzes un veidojiet uzticamu kopienu, kurā katrs dalībnieks ir pārbaudīts.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="card-body">
                        <div class="display-4 mb-3">&#9993;</div>
                        <h5 class="card-title fw-bold">Lietotāju atsauksmes</h5>
                        <p class="card-text text-muted">Dalieties pieredzē un lasiet citu lietotāju atsauksmes, lai pieņemtu pārdomātus pirkuma lēmumus.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="card-body">
                        <div class="display-4 mb-3">&#128737;</div>
                        <h5 class="card-title fw-bold">Droša tirdzniecība</h5>
                        <p class="card-text text-muted">Mūsu sistēma aizsargā jūsu darījumus un nodrošina, ka nauda un prece nonāk īstajās rokās.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-1">&copy; {{ date('Y') }} Marketplace. Visas tiesības aizsargātas.</p>
        <p class="mb-0 small text-muted">Droša un uzticama tirdzniecības platforma</p>
    </div>
</footer>

@endsection
