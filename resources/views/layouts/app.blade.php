<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Marketplace')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        .theme-option { transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out; }
        .theme-option.active {
            border-color: var(--bs-primary) !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, .25);
        }
        .theme-swatch {
            height: 80px;
            border-radius: .5rem;
            border: 1px solid var(--bs-border-color);
        }
    </style>
</head>
<body>
    @yield('content')

    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="settingsModalLabel">Iestatījumi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-semibold mb-3">Krāsu motīvs</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <button type="button" class="theme-option btn border w-100 text-center p-2" data-theme="light">
                                <span class="theme-swatch d-block mb-2" style="background: #ffffff;"></span>
                                <span class="fw-semibold">Gaišais</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="theme-option btn border w-100 text-center p-2" data-theme="dark">
                                <span class="theme-swatch d-block mb-2" style="background: #212529;"></span>
                                <span class="fw-semibold">Tumšais</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aizvērt</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var root = document.documentElement;
            var options = document.querySelectorAll('.theme-option');

            function applyTheme(theme) {
                root.setAttribute('data-bs-theme', theme);
                localStorage.setItem('theme', theme);
                options.forEach(function (btn) {
                    btn.classList.toggle('active', btn.dataset.theme === theme);
                });
            }

            options.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    applyTheme(btn.dataset.theme);
                });
            });

            applyTheme(localStorage.getItem('theme') || 'light');
        })();
    </script>
    @stack('scripts')
</body>
</html>
