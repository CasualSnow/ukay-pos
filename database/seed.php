<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

// Clear existing data
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE sale_items;");
$pdo->exec("TRUNCATE TABLE sales;");
$pdo->exec("TRUNCATE TABLE reservations;");
$pdo->exec("TRUNCATE TABLE items;");
$pdo->exec("TRUNCATE TABLE users;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

// Seed Users
$users = [
    ['owner', password_hash('owner123', PASSWORD_DEFAULT), 'admin'],
    ['staff', password_hash('staff123', PASSWORD_DEFAULT), 'staff'],
];

$stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
foreach ($users as $user) {
    $stmt->execute($user);
}

// Category Shared Images (LOCAL USER-PROVIDED IMAGES)
$category_images = [
    'T-Shirts' => '/thrift_pos/assets/images/images (2).jpg',
    'Pants'    => '/thrift_pos/assets/images/download.jpg',
    'Jackets'  => '/thrift_pos/assets/images/download (1).jpg',
    'Shoes'    => '/thrift_pos/assets/images/download (2).jpg'
];

// Seed Items with SHARED IMAGES per category but UNIQUE NAMES
$items_data = [
    'T-Shirts' => [
        ['name' => 'Uniqlo Basic Tee White', 'price' => 150],
        ['name' => 'Uniqlo Basic Tee Black', 'price' => 150],
        ['name' => 'Nike Sportswear Essential Tee', 'price' => 250],
        ['name' => 'Adidas Originals Trefoil Black', 'price' => 200],
        ['name' => 'Vintage Champion Grey Heather', 'price' => 180],
        ['name' => 'Stussy 8-Ball Graphic Tee', 'price' => 350],
        ['name' => 'Polo Ralph Lauren Classic Fit', 'price' => 300],
        ['name' => 'Vintage Harley Davidson Eagle', 'price' => 450],
        ['name' => 'H&M Relaxed Fit Cotton Tee', 'price' => 120],
        ['name' => 'Supreme Motion Logo White', 'price' => 500],
    ],
    'Pants' => [
        ['name' => 'Levi\'s 501 Original Blue Jeans', 'price' => 450],
        ['name' => 'Dickies 874 Khaki Work Pants', 'price' => 350],
        ['name' => 'Vintage Carhartt Double Knee', 'price' => 480],
        ['name' => 'Wrangler Cowboy Cut Denim', 'price' => 380],
        ['name' => 'Uniqlo Selvedge Slim Fit', 'price' => 400],
        ['name' => 'Zara Straight Fit Chinos', 'price' => 280],
        ['name' => 'Vintage Military Cargo Pants', 'price' => 320],
        ['name' => 'H&M Corduroy Loose Fit', 'price' => 250],
        ['name' => 'Lee Rider Straight Jeans', 'price' => 300],
        ['name' => 'Gap Standard Fit Khakis', 'price' => 220],
    ],
    'Jackets' => [
        ['name' => 'Vintage Denim Jacket', 'price' => 550],
        ['name' => 'Nike Hoodie Black', 'price' => 450],
        ['name' => 'Vintage Levi\'s Trucker Jacket', 'price' => 600],
        ['name' => 'The North Face Nuptse 1996', 'price' => 1200],
        ['name' => 'Vintage Carhartt Detroit Jacket', 'price' => 850],
        ['name' => 'Bomber Jacket Black', 'price' => 400],
        ['name' => 'Windbreaker Sport Jacket', 'price' => 350],
        ['name' => 'Vintage Varsity Jacket', 'price' => 750],
        ['name' => 'Harrington Casual Jacket', 'price' => 500],
        ['name' => 'Puffer Jacket Navy', 'price' => 900],
    ],
    'Shoes' => [
        ['name' => 'Nike Air Force 1 Low White', 'price' => 500],
        ['name' => 'Adidas Stan Smith Classic', 'price' => 400],
        ['name' => 'Converse Chuck Taylor High', 'price' => 300],
        ['name' => 'Vans Old Skool Black/White', 'price' => 350],
        ['name' => 'New Balance 574 Heritage', 'price' => 450],
        ['name' => 'Nike Jordan 1 Retro', 'price' => 1500],
        ['name' => 'Adidas Superstar Original', 'price' => 420],
        ['name' => 'Puma Suede Classic', 'price' => 380],
        ['name' => 'Reebok Club C 85', 'price' => 400],
        ['name' => 'Vans Sk8-Hi Classic', 'price' => 360],
    ],
];

$tag_colors = ['red', 'blue', 'green', 'yellow'];
$statuses = ['available', 'available', 'available', 'available', 'available', 'available', 'available', 'sold', 'reserved']; 

$stmt = $pdo->prepare("INSERT INTO items (name, category, price, tag_color, image_url, status) VALUES (?, ?, ?, ?, ?, ?)");

// Insert T-Shirts last but first in UI sorting logic
$categories_to_seed = ['Pants', 'Jackets', 'Shoes', 'T-Shirts'];

foreach ($categories_to_seed as $category) {
    $shared_img = $category_images[$category];
    foreach ($items_data[$category] as $item) {
        $tag = $tag_colors[array_rand($tag_colors)];
        $status = $statuses[array_rand($statuses)];
        $stmt->execute([$item['name'], $category, $item['price'], $tag, $shared_img, $status]);
    }
}

echo "Database seeded with SHARED CATEGORY IMAGES and UNIQUE NAMES successfully!\n";
