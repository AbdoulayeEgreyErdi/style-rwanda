<?php
echo "<h1>PHPMailer Detailed Check</h1>";

$base = __DIR__;
echo "Base directory: " . $base . "<br><br>";

// Check phpmailer folder
$phpmailer_dir = $base . '/phpmailer';
if (is_dir($phpmailer_dir)) {
    echo "✅ phpmailer folder exists<br>";
    
    // List ALL contents
    echo "<h3>Contents of phpmailer folder:</h3>";
    $items = scandir($phpmailer_dir);
    echo "<ul>";
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $full = $phpmailer_dir . '/' . $item;
            if (is_dir($full)) {
                echo "<li><strong>$item</strong> (folder)";
                
                // If it's a folder, list its contents
                $subitems = scandir($full);
                echo "<ul>";
                foreach ($subitems as $sub) {
                    if ($sub != '.' && $sub != '..') {
                        echo "<li>$sub</li>";
                    }
                }
                echo "</ul>";
                echo "</li>";
            } else {
                echo "<li>$item (file)</li>";
            }
        }
    }
    echo "</ul>";
    
} else {
    echo "❌ phpmailer folder NOT found<br>";
}

// Check alternative locations
echo "<h3>Checking alternative locations:</h3>";
$alternatives = ['PHPMailer-master', 'PHPMailer', 'vendor/phpmailer/phpmailer'];
foreach ($alternatives as $alt) {
    $full = $base . '/' . $alt;
    if (is_dir($full)) {
        echo "✅ Found: $alt<br>";
    } else {
        echo "❌ Not found: $alt<br>";
    }
}
?>