<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::where('role', 'admin')->first();
        $manager = User::where('email', 'manager@example.com')->first() ?? $admin;

        $products = [
            ['title' => 'Fresh Red Apples',      'category' => 'fruits',     'price' => 3.99,  'stock' => 200, 'description' => '<p>Crisp, sweet red apples freshly harvested from local orchards. Rich in fibre and vitamins.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Organic Bananas',        'category' => 'fruits',     'price' => 1.99,  'stock' => 150, 'description' => '<p>Naturally ripened organic bananas. Perfect for smoothies, baking or a quick energy snack.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Juicy Oranges',          'category' => 'fruits',     'price' => 4.49,  'stock' => 120, 'description' => '<p>Sun-kissed oranges bursting with vitamin C. Great for juicing or eating fresh.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Ripe Mangoes',           'category' => 'fruits',     'price' => 5.99,  'stock' => 80,  'description' => '<p>Sweet, tropical mangoes at peak ripeness. Excellent in salads, smoothies, or on their own.</p>', 'date_available' => '2024-02-01'],
            ['title' => 'Broccoli Crown',         'category' => 'vegetables', 'price' => 2.49,  'stock' => 180, 'description' => '<p>Fresh, vibrant broccoli crowns packed with nutrients. Steam, roast or add to stir-fries.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Baby Spinach',           'category' => 'vegetables', 'price' => 3.29,  'stock' => 100, 'description' => '<p>Tender baby spinach leaves, washed and ready to eat. Perfect for salads and smoothies.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Organic Carrots',        'category' => 'vegetables', 'price' => 1.79,  'stock' => 200, 'description' => '<p>Sweet, crunchy organic carrots. Ideal for snacking, roasting or soups.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Cherry Tomatoes',        'category' => 'vegetables', 'price' => 3.49,  'stock' => 160, 'description' => '<p>Colourful cherry tomatoes with a sweet, tangy flavour. Great for salads and pasta dishes.</p>', 'date_available' => '2024-02-01'],
            ['title' => 'Fresh Whole Milk',       'category' => 'dairy',      'price' => 2.99,  'stock' => 90,  'description' => '<p>Farm-fresh whole milk, pasteurised and homogenised. Rich, creamy taste.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Greek Yoghurt',          'category' => 'dairy',      'price' => 4.99,  'stock' => 70,  'description' => '<p>Thick, creamy Greek yoghurt high in protein. Perfect with fruit or as a cooking ingredient.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Sourdough Loaf',         'category' => 'bakery',     'price' => 5.49,  'stock' => 50,  'description' => '<p>Artisan sourdough bread with a crispy crust and chewy interior. Baked fresh daily.</p>', 'date_available' => '2024-01-01'],
            ['title' => 'Blueberry Muffins (6pk)','category' => 'bakery',     'price' => 6.99,  'stock' => 40,  'description' => '<p>Soft, fluffy muffins loaded with plump blueberries. Baked fresh each morning.</p>', 'date_available' => '2024-02-01'],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'created_by'  => $manager->id,
                    'status'      => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ])
            );
        }
    }
}
