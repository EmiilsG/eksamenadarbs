<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kļūda') - Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="d-flex align-items-center justify-content-center min-vh-100 p-4">
        <div class="card border-0 shadow-sm text-center" style="max-width: 520px;">
            <div class="card-body p-5">
                <div class="display-1 fw-bold text-primary mb-2">@yield('code')</div>
                <h1 class="h3 fw-bold mb-3">@yield('title')</h1>
                <p class="text-muted mb-4">@yield('description')</p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ url('/') }}" class="btn btn-primary px-4">Uz sākumu</a>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4">Atpakaļ</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
