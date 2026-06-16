<?php
echo "<h1>Checking PHPMailer Folder</h1>";

$base = __DIR__;
echo "Base directory: " . $base . "<br><br>";

// Check if PHPMailer-master exists
$folder = $base . '/PHPMailer-master';
if (is_dir($folder)) {
    echo "✅ PHPMailer-master folder exists<br>";
    
    // List contents
    $items = scandir($folder);
    echo "Contents of PHPMailer-master:<br>";
    echo "<ul>";
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            echo "<li>" . $item;
            if (is_dir($folder . '/' . $item)) {
                echo " (folder)";
            }
            echo "</li>";
        }
    }
    echo "</ul>";
    
    // Check if src folder exists
    if (is_dir($folder . '/src')) {
        echo "✅ src folder exists!<br>";
        echo "Full path: " . $folder . "/src/<br>";
    } else {
        echo "❌ src folder NOT found!<br>";
    }
} else {
    echo "❌ PHPMailer-master folder NOT found!<br>";
}

// Also check for other possible names
$alternatives = ['phpmailer', 'PHPMailer', 'mailer'];
echo "<br><h3>Checking alternative names:</h3>";
foreach ($alternatives as $alt) {
    if (is_dir($base . '/' . $alt)) {
        echo "✅ Found: " . $alt . "<br>";
    } else {
        echo "❌ Not found: " . $alt . "<br>";
    }
}
?>