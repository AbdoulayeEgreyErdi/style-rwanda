<?php
require_once __DIR__ . '/db.php';

function seedDatabase() {
    global $pdo;
    
    try {
        // Check if products already exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "✅ Products already exist (" . $count . " products)<br>";
            return;
        }
        
        // Sample products
        $products = [
            [
                'name' => 'Premium T-Shirt',
                'slug' => 'premium-t-shirt',
                'description' => 'High-quality cotton t-shirt perfect for everyday wear.',
                'price' => 45000,
                'category' => 'Men',
                'image_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400',
                'sizes' => '["S","M","L","XL"]',
                'colors' => '["Black","White","Gold"]',
                'stock' => 50,
                'is_featured' => 1,
                'is_new' => 1
            ],
            [
                'name' => 'Classic Sneakers',
                'slug' => 'classic-sneakers',
                'description' => 'Stylish and comfortable sneakers for all-day wear.',
                'price' => 62000,
                'category' => 'Shoes',
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400',
                'sizes' => '["7","8","9","10","11"]',
                'colors' => '["Black","White"]',
                'stock' => 30,
                'is_featured' => 1,
                'is_new' => 1
            ],
            [
                'name' => 'Premium Hoodie',
                'slug' => 'premium-hoodie',
                'description' => 'Warm and cozy hoodie with modern design.',
                'price' => 75000,
                'category' => 'Men',
                'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=400',
                'sizes' => '["S","M","L","XL"]',
                'colors' => '["Black","Gray"]',
                'stock' => 40,
                'is_featured' => 1,
                'is_new' => 1
            ],
            [
                'name' => 'Leather Bag',
                'slug' => 'leather-bag',
                'description' => 'Premium leather bag for daily use.',
                'price' => 55000,
                'category' => 'Accessories',
                'image_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400',
                'sizes' => '["One Size"]',
                'colors' => '["Black","Brown"]',
                'stock' => 35,
                'is_featured' => 1,
                'is_new' => 1
            ],
            [
                'name' => 'Running Sports Shoes',
                'slug' => 'running-sports-shoes',
                'description' => 'Professional running shoes for athletes.',
                'price' => 89900,
                'category' => 'Shoes',
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400',
                'sizes' => '["38","39","40","41","42","43","44"]',
                'colors' => '["Red","Blue","Black"]',
                'stock' => 25,
                'is_featured' => 0,
                'is_new' => 1
            ],
            [
                'name' => 'Denim Jacket',
                'slug' => 'denim-jacket',
                'description' => 'Classic denim jacket perfect for layering.',
                'price' => 89000,
                'category' => 'Men',
                'image_url' => 'https://images.unsplash.com/photo-1576871337632-b9aef4c17ab9?w=400',
                'sizes' => '["S","M","L","XL"]',
                'colors' => '["Blue","Black"]',
                'stock' => 20,
                'is_featured' => 1,
                'is_new' => 0
            ]
        ];
        
        // Insert products
        foreach ($products as $product) {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, slug, description, price, category, image_url, sizes, colors, stock, is_featured, is_new)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $product['name'],
                $product['slug'],
                $product['description'],
                $product['price'],
                $product['category'],
                $product['image_url'],
                $product['sizes'],
                $product['colors'],
                $product['stock'],
                $product['is_featured'],
                $product['is_new']
            ]);
        }
        
        echo "✅ " . count($products) . " products added successfully!<br>";
        
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
}

// Run the seed
seedDatabase();
?>