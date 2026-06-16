<?php
/**
 * Database Connection - SQLite for Render
 * No MySQL needed - works immediately!
 */

// SQLite database file
$db_file = __DIR__ . '/../database.sqlite';

// Create SQLite connection
try {
    $pdo = new PDO("sqlite:$db_file");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'customer',
            phone TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            category TEXT NOT NULL,
            image_url TEXT,
            sizes TEXT,
            colors TEXT,
            stock INTEGER DEFAULT 0,
            is_featured INTEGER DEFAULT 0,
            is_new INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT UNIQUE NOT NULL,
            customer_name TEXT NOT NULL,
            customer_phone TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            delivery_address TEXT NOT NULL,
            city TEXT NOT NULL,
            total_amount REAL NOT NULL,
            shipping_cost REAL DEFAULT 2000,
            payment_method TEXT NOT NULL,
            payment_status TEXT DEFAULT 'pending',
            order_status TEXT DEFAULT 'pending',
            transaction_id TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            product_price REAL NOT NULL,
            quantity INTEGER NOT NULL,
            size TEXT,
            color TEXT
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT 'active'
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL,
            token TEXT NOT NULL,
            expires_at DATETIME NOT NULL,
            used INTEGER DEFAULT 0
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS saved_carts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            cart_data TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Insert default admin if not exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@style.rw']);
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Administrator', 'admin@style.rw', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
    }
    
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>