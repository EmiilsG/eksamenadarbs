@extends('layouts.app')

@section('title', 'Pieteikšanās - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
    </div>
</nav>

<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="width: 440px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-4">Pieteikšanās</h3>

            <form method="POST" action="#">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">E-pasts</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Parole</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <span id="passwordIcon">&#128065;</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Pieteikties</button>

                <p class="text-center mb-0">
                    Nav konta?
                    <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Reģistrēties</a>
                </p>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('passwordIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.innerHTML = '&#128064;';
    } else {
        passwordInput.type = 'password';
        icon.innerHTML = '&#128065;';
    }
}
</script>
@endpush

@endsection
