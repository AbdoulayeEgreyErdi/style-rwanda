<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Login Diagnostic</h1>";

// Test 1: Check database connection
echo "<h3>1. Testing Database Connection...</h3>";
try {
    require_once '../includes/db.php';
    echo "✅ Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check users table
echo "<h3>2. Checking Users Table...</h3>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists<br>";
    } else {
        echo "❌ Users table does not exist!<br>";
        echo "Please import the database.sql file<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check admin user
echo "<h3>3. Checking Admin User...</h3>";
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@style.rw']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Admin user found!<br>";
        echo "User ID: " . $user['id'] . "<br>";
        echo "Name: " . $user['name'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Password hash: " . substr($user['password'], 0, 30) . "...<br>";
        
        // Test password
        $test_password = 'Admin2026';
        if (password_verify($test_password, $user['password'])) {
            echo "✅ Password 'Admin2026' is CORRECT!<br>";
        } else {
            echo "❌ Password 'Admin2026' is INCORRECT!<br>";
            
            // Fix the password
            $new_hash = password_hash('Admin2026', PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->execute([$new_hash, 'admin@style.rw']);
            echo "✅ Password has been RESET to 'Admin2026'<br>";
        }
    } else {
        echo "❌ Admin user NOT found!<br>";
        
        // Create admin user
        $password_hash = password_hash('Admin2026', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $result = $insert->execute(['Administrator', 'admin@style.rw', $password_hash, 'admin']);
        
        if ($result) {
            echo "✅ Admin user CREATED successfully!<br>";
            echo "Email: admin@style.rw<br>";
            echo "Password: Admin2026<br>";
        } else {
            echo "❌ Failed to create admin user<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 4: Test login function
echo "<h3>4. Testing Login Function...</h3>";
?>
<form method="POST" action="test-login.php">
    <input type="email" name="test_email" placeholder="Email" value="admin@style.rw">
    <input type="password" name="test_password" placeholder="Password" value="Admin2026">
    <button type="submit">Test Login</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $email = $_POST['test_email'];
    $password = $_POST['test_password'];
    
    echo "<h3>Login Test Results:</h3>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User found in database<br>";
        if (password_verify($password, $user['password'])) {
            echo "✅ LOGIN SUCCESSFUL!<br>";
            echo "Would redirect to: admin/index.php<br>";
            
            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            echo "✅ Session variables set<br>";
            echo "<a href='index.php'>Click here to go to Admin Dashboard</a>";
        } else {
            echo "❌ Password verification failed<br>";
            echo "Entered password: " . $password . "<br>";
        }
    } else {
        echo "❌ User not found with email: " . $email . "<br>";
    }
}

echo "<h3>5. Session Test:</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session ID: " . session_id() . "<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>