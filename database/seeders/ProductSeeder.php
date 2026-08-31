<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = [
            ['name' => 'Jānis Bērziņš', 'rating' => 4.8, 'image' => 'https://i.pravatar.cc/300?img=12'],
            ['name' => 'Laura Kalniņa', 'rating' => 4.5, 'image' => 'https://i.pravatar.cc/300?img=47'],
            ['name' => 'Māris Ozols', 'rating' => 3.9, 'image' => 'https://i.pravatar.cc/300?img=15'],
            ['name' => 'Anna Liepa', 'rating' => 5.0, 'image' => 'https://i.pravatar.cc/300?img=45'],
            ['name' => 'Pēteris Vītols', 'rating' => 4.2, 'image' => 'https://i.pravatar.cc/300?img=32'],
        ];

        $users = [];
        foreach ($sellers as $i => $seller) {
            $users[] = User::firstOrCreate(
                ['email' => 'seller' . ($i + 1) . '@example.com'],
                [
                    'name' => $seller['name'],
                    'email' => 'seller' . ($i + 1) . '@example.com',
                    'password' => Hash::make('password'),
                    'rating' => $seller['rating'],
                    'profile_image' => $seller['image'],
                ]
            );
        }

        $products = [
            ['name' => 'iPhone 13', 'category' => 'Elektronika', 'price' => 649.00, 'description' => 'Lietots iPhone 13, 128GB, lieliskā stāvoklī.'],
            ['name' => 'Sony PS5', 'category' => 'Elektronika', 'price' => 499.00, 'description' => 'Sony PlayStation 5 ar vienu kontrolieri un 2 spēlēm.'],
            ['name' => 'IKEA rakstāmgalds', 'category' => 'Mēbeles', 'price' => 89.00, 'description' => 'Ozolkoka rakstāmgalds, 120x60cm.'],
            ['name' => 'Velosipēds Scott', 'category' => 'Sports', 'price' => 350.00, 'description' => 'Kalnu velosipēds Scott Aspect 27.5\'\'.'],
            ['name' => 'Samsung TV 55"', 'category' => 'Elektronika', 'price' => 420.00, 'description' => 'Samsung 4K Smart TV, 55 collas.'],
            ['name' => 'Vintage ādas krēsls', 'category' => 'Mēbeles', 'price' => 145.00, 'description' => 'Retro ādas atpūtas krēsls 70. gadiem.'],
            ['name' => 'Fotokamera Canon', 'category' => 'Elektronika', 'price' => 780.00, 'description' => 'Canon EOS 90D ar 18-135mm objektīvu.'],
            ['name' => 'Bērnu ratiņi', 'category' => 'Bērniem', 'price' => 120.00, 'description' => 'Izcili bērnu ratiņi, jaunam bērniņam.'],
            ['name' => 'Audi A4 2005', 'category' => 'Auto', 'price' => 3200.00, 'description' => 'Audi A4 2.0 TDI, reģistrēts, tehniskā apskate līdz 2027.'],
            ['name' => 'Ziemas riepas', 'category' => 'Auto', 'price' => 180.00, 'description' => 'Komplekts 4 ziemas riepas 205/55 R16.'],
            ['name' => 'Galda spēle Catan', 'category' => 'Rotaļlietas', 'price' => 35.00, 'description' => 'Klasiskā stratēģijas spēle Catan, pilns komplekts.'],
            ['name' => 'Jogas paklājiņš', 'category' => 'Sports', 'price' => 25.00, 'description' => 'Neslīdošs jogas paklājiņš, 6mm.'],
        ];

        foreach ($products as $i => $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                [
                    'user_id' => $users[$i % count($users)]->id,
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'image' => 'https://picsum.photos/seed/product' . ($i + 1) . '/600/400',
                ]
            );
        }

        $reviews = [
            ['to' => 0, 'rating' => 5, 'comment' => 'Lielisks pārdevējs! Viss noritēja ātri un bez problēmām. Iesaku visiem.'],
            ['to' => 0, 'rating' => 5, 'comment' => 'Prece atbilda aprakstam, iepakojums lielisks. Paldies!'],
            ['to' => 0, 'rating' => 4, 'comment' => 'Labi komunicē, prece pienāca laikā.'],
            ['to' => 1, 'rating' => 5, 'comment' => 'Ļoti atsaucīga un draudzīga pārdevēja.'],
            ['to' => 1, 'rating' => 4, 'comment' => 'Viss kārtībā, neliels kavējums piegādē, bet citādi super.'],
            ['to' => 2, 'rating' => 4, 'comment' => 'Prece laba, atbilst aprakstam.'],
            ['to' => 2, 'rating' => 3, 'comment' => 'Komunikācija bija lēna, bet produkts tika piegādāts.'],
            ['to' => 3, 'rating' => 5, 'comment' => 'Ideāla tirdzniecība, ļoti uzticams pārdevējs.'],
            ['to' => 3, 'rating' => 5, 'comment' => 'Viss bija nevainojami, augsti novērtēju.'],
            ['to' => 3, 'rating' => 5, 'comment' => 'Pirma līmeņa serviss, noteikti iegādāšos atkal.'],
            ['to' => 4, 'rating' => 4, 'comment' => 'Labs darījums, visu izskaidroja saprotami.'],
        ];

        foreach ($reviews as $i => $review) {
            $reviewer = $users[($i + 1) % count($users)];
            $reviewee = $users[$review['to']];

            Review::firstOrCreate(
                ['reviewee_id' => $reviewee->id, 'reviewer_id' => $reviewer->id, 'comment' => $review['comment']],
                [
                    'reviewee_id' => $reviewee->id,
                    'reviewer_id' => $reviewer->id,
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                    'created_at' => now()->subDays(rand(1, 60))->subHours(rand(0, 23)),
                ]
            );
        }
    }
}
