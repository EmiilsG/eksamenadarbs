# Marketplace

Laravel 12 tirdzniecības (marketplace) platforma ar Blade un Bootstrap 5. Lietotāji var pievienot produktus, apskatīt preču katalogu, skatīt pārdevēju profilus un novērtēt pārdevējus.

## Funkcionalitāte

- **Autentifikācija** — reģistrācija un pieteikšanās (Laravel session auth)
- **Preču katalogs** (`/products`) — visas preces kartītēs ar:
  - meklēšanu pēc nosaukuma
  - kategoriju filtru
  - cenas filtru (No / Līdz)
  - kārtošanu (jaunākās, lētākās, dārgākās, labāk novērtētie pārdevēji)
- **Produktu pievienošana** (`/products/create`) — nosaukums, apraksts, cena, kategorija, bilde
- **Produktu rediģēšana** (`/products/{id}/edit`) — īpašnieks var mainīt nosaukumu, aprakstu, cenu, kategoriju un bildi
- **Mani produkti** (`/my-products`) — lietotāja paša sludinājumu pārskats ar rediģēšanas un dzēšanas iespēju
- **Produktu dzēšana** — tikai paša lietotāja produktus var dzēst
- **Lietotāja profils** (`/profile` un `/profile/{user}`) — vārds, e-pasts, profila bilde, vidējais vērtējums, atsauksmju skaits, sludinājumu skaits, reģistrācijas datums
- **Pārdevēju vērtēšana** — lietotāji var novērtēt pārdevējus ar 1–5 zvaigznēm un atsauksmes tekstu (nevar novērtēt pašam sevi)

## Tehnoloģijas

- Laravel 12 (PHP 8.2+)
- Blade templates
- Bootstrap 5.3
- SQLite (noklusējuma datubāze)

## Projekta struktūra

```
app/
├── Http/Controllers/
│   ├── HomeController.php
│   ├── ProductController.php
│   ├── ProfileController.php
│   ├── ReviewController.php
│   └── Auth/           (Login, Register)
├── Models/
│   ├── User.php
│   ├── Product.php
│   └── Review.php
database/
├── migrations/         (users, products, reviews, u.c.)
└── seeders/
    └── ProductSeeder.php  (testa produkti, pārdevēji un atsauksmes)
resources/views/
├── layout/app.blade.php
├── home.blade.php
├── profile.blade.php
├── products/           (index = katalogs, mine = mani produkti, create, edit)
└── auth/               (login, register)
```

## Palaišana

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ProductSeeder   # testa dati (pēc izvēles)
php artisan storage:link                    # lai attēli darbotos
php artisan serve
```

## Testa dati

`ProductSeeder` izveido 5 pārdevējus, 12 produktus un 11 atsauksmes. Pārdevēju testa konti:

```
seller1@example.com ... seller5@example.com (parole: password)
```

## Testi

```bash
php artisan test
```

## Galvenās tabulas

- **users** — `id`, `name`, `email`, `password`, `rating`, `profile_image`
- **products** — `id`, `user_id`, `name`, `description`, `price`, `category`, `image`
- **reviews** — `id`, `reviewee_id`, `reviewer_id`, `rating`, `comment`
