@extends('layouts.app')

@section('title', 'Pievienot produktu - Marketplace')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">Marketplace</a>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Iestatījumi" aria-label="Iestatījumi">&#9881;</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light me-2">Produkti</a>
            <a href="{{ route('products.mine') }}" class="btn btn-outline-light me-2">Mani produkti</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light">Izrakstīties</button>
            </form>
        </div>
    </div>
</nav>

<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px);">
    <div class="card shadow-sm border-0" style="width: 520px;">
        <div class="card-body p-5">
            <h3 class="text-center fw-bold mb-4">Pievienot produktu</h3>

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nosaukums</label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Apraksts</label>
                    <textarea class="form-control form-control-lg" id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Cena (&euro;)</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-lg" id="price" name="price" value="{{ old('price') }}" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Kategorija</label>
                    <select class="form-select form-select-lg" id="category" name="category">
                        <option value="">Cita</option>
                        <option value="Elektronika" @selected(old('category') === 'Elektronika')>Elektronika</option>
                        <option value="Mēbeles" @selected(old('category') === 'Mēbeles')>Mēbeles</option>
                        <option value="Sports" @selected(old('category') === 'Sports')>Sports</option>
                        <option value="Auto" @selected(old('category') === 'Auto')>Auto</option>
                        <option value="Bērniem" @selected(old('category') === 'Bērniem')>Bērniem</option>
                        <option value="Rotaļlietas" @selected(old('category') === 'Rotaļlietas')>Rotaļlietas</option>
                        <option value="Apģērbs" @selected(old('category') === 'Apģērbs')>Apģērbs</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="images" class="form-label">Produktu attēli (pēc izvēles)</label>
                    <input type="file" class="form-control form-control-lg" id="images" name="images[]" accept="image/*" multiple>
                    <div class="form-text">
                        Maksimāli {{ \App\Models\Product::MAX_IMAGES }} attēli. Pirmais attēls tiks izmantots kā galvenais.
                        JPG, PNG, GIF, WEBP (maks. 2 MB katram).
                    </div>
                    <div id="imagePreviews" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Saglabāt produktu</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var input = document.getElementById('images');
        var previews = document.getElementById('imagePreviews');

        input.addEventListener('change', function () {
            previews.innerHTML = '';

            Array.prototype.forEach.call(input.files, function (file) {
                var wrapper = document.createElement('div');
                wrapper.className = 'position-relative';

                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = file.name;
                img.className = 'rounded-3 border';
                img.style.width = '72px';
                img.style.height = '72px';
                img.style.objectFit = 'cover';

                var badge = document.createElement('span');
                badge.className = 'position-absolute top-0 end-0 badge bg-dark';
                badge.style.opacity = '.75';

                wrapper.appendChild(img);
                wrapper.appendChild(badge);
                previews.appendChild(wrapper);

                img.addEventListener('load', function () {
                    badge.textContent = previews.children.length === 1 ? 'Galvenais' : '';
                    URL.revokeObjectURL(img.src);
                });
            });
        });
    })();
</script>
@endpush

@endsection
