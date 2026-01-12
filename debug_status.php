<?php
// debug_status.php
require 'db_connect.php';

try {
    echo "Database Connection: OK\n";
    
    // Check Music
    $stmt = $pdo->query("SELECT COUNT(*) FROM music");
    $musicCount = $stmt->fetchColumn();
    echo "Music Tracks: $musicCount\n";
    
    if ($musicCount > 0) {
        $stmt = $pdo->query("SELECT * FROM music LIMIT 3");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($rows);
    }

    // Check Presets
    $stmt = $pdo->query("SELECT COUNT(*) FROM presets");
    $presetCount = $stmt->fetchColumn();
    echo "Presets: $presetCount\n";

    if ($presetCount > 0) {
        $stmt = $pdo->query("SELECT id, name, type FROM presets LIMIT 3");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($rows);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
