<?php
// api_test_debug.php
// Run this to see the raw error message that is breaking the JSON.

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>API Debug Test</h1>";
echo "<p>Testing Database Connection...</p>";

try {
    if (!file_exists('db_connect.php')) {
        throw new Exception("db_connect.php is MISSING.");
    }
    
    // Manual require to catch syntax errors
    require 'db_connect.php';
    
    if (!isset($pdo)) {
        throw new Exception("\$pdo variable is not defined after requiring db_connect.php");
    }
    
    echo "<p style='color:green'>Database Connection Successful!</p>";
    
    // Test Query
    echo "<p>Testing AOM Presets Query...</p>";
    $stmt = $pdo->prepare("SELECT id, name, type, data FROM presets WHERE type = 'AOM' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        echo "<p style='color:orange'>No AOM presets found to test.</p>";
    } else {
        echo "<p>Found Preset: " . $row['name'] . "</p>";
        echo "<p>Testing JSON encoding of row...</p>";
        $json = json_encode($row);
        if ($json === false) {
            echo "<p style='color:red'>JSON Encoding Failed: " . json_last_error_msg() . "</p>";
        } else {
            echo "<p style='color:green'>JSON Encoding Successful!</p>";
            echo "<pre>" . substr($json, 0, 200) . "...</pre>";
        }
    }
    
} catch (Throwable $e) {
    echo "<h2 style='color:red'>ERROR DETECTED</h2>";
    echo "<div style='border:1px solid red; padding:10px; background:#ffeeee'>";
    echo "<b>Type:</b> " . get_class($e) . "<br>";
    echo "<b>Message:</b> " . $e->getMessage() . "<br>";
    echo "<b>File:</b> " . $e->getFile() . "<br>";
    echo "<b>Line:</b> " . $e->getLine() . "<br>";
    echo "</div>";
    
    // Also print raw trace
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
