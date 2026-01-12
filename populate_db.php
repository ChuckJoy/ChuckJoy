<?php
// populate_db.php
// Scans the file system (presets/ and music/ directories) and populates the MySQL database.
// Run this ONCE to ingest your existing files.

require 'db_connect.php';

echo "<h1>Database Population Utility</h1>";
echo "<p>Connecting to database... Success.</p>";

// --- 1. POPULATE PRESETS ---
echo "<h2>Importing Presets...</h2>";

$presetsDir = __DIR__ . '/presets';
if (!is_dir($presetsDir)) {
    echo "<p style='color:red'>Error: 'presets' directory not found at $presetsDir</p>";
} else {
    $files = glob($presetsDir . '/*.json');
    $count = 0;
    $newCount = 0;

    foreach ($files as $filepath) {
        $filename = basename($filepath);
        $jsonContent = file_get_contents($filepath);
        
        // Simple heuristic for 'type' based on filename, defaults to 'General'
        $type = 'General';
        if (stripos($filename, 'AOM') !== false) $type = 'AOM';
        elseif (stripos($filename, 'LIS') !== false || stripos($filename, 'Lost') !== false) $type = 'LIS';
        elseif (stripos($filename, 'DIFF') !== false) $type = 'DIFFCAM';

        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM presets WHERE name = ?");
        $stmt->execute([$filename]);
        
        if ($stmt->rowCount() == 0) {
            // Insert
            try {
                $insert = $pdo->prepare("INSERT INTO presets (name, type, data) VALUES (?, ?, ?)");
                $insert->execute([$filename, $type, $jsonContent]);
                echo "<li>Imported: <b>$filename</b> (Type: $type)</li>";
                $newCount++;
            } catch (PDOException $e) {
                echo "<li style='color:red'>Failed to import $filename: " . $e->getMessage() . "</li>";
            }
        } else {
            // echo "<li style='color:gray'>Skipped (already exists): $filename</li>";
        }
        $count++;
    }
    echo "<p>Scanned $count files. Imported $newCount new presets.</p>";
}

// --- 2. POPULATE MUSIC ---
echo "<h2>Importing Music...</h2>";

$musicDir = __DIR__ . '/music';
if (!is_dir($musicDir)) {
    echo "<p style='color:red'>Error: 'music' directory not found at $musicDir</p>";
} else {
    // Scan for mp3 and wav
    $files = glob($musicDir . '/*.{mp3,wav}', GLOB_BRACE);
    $count = 0;
    $newCount = 0;

    foreach ($files as $filepath) {
        $filename = basename($filepath);
        // Relative path for web access
        $webPath = 'music/' . $filename;

        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM music WHERE filename = ?");
        $stmt->execute([$filename]);

        if ($stmt->rowCount() == 0) {
            // Insert
            try {
                $insert = $pdo->prepare("INSERT INTO music (filename, filepath) VALUES (?, ?)");
                $insert->execute([$filename, $webPath]);
                echo "<li>Imported: <b>$filename</b></li>";
                $newCount++;
            } catch (PDOException $e) {
                echo "<li style='color:red'>Failed to import $filename: " . $e->getMessage() . "</li>";
            }
        } else {
            // echo "<li style='color:gray'>Skipped: $filename</li>";
        }
        $count++;
    }
    echo "<p>Scanned $count files. Imported $newCount new music tracks.</p>";
}

echo "<hr><p><b>Done.</b> <a href='index.html'>Go to Dashboard</a></p>";
?>
