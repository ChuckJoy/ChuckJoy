<?php
// api.php

// --- 1. PREVENT HTML ERROR OUTPUT (Crucial for JSON APIs) ---
error_reporting(0);           // Disable ALL error reporting to screen
ini_set('display_errors', 0); 
ini_set('log_errors', 1);     
ini_set('error_log', __DIR__ . '/php_error.log'); 

// --- 2. CORS & HEADERS ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function json_response($data) {
    $json = json_encode($data);
    if ($json === false) {
        // Handle encoding error (e.g., non-UTF8 chars)
        http_response_code(500);
        echo json_encode(['error' => 'JSON Encoding Failed: ' . json_last_error_msg()]);
    } else {
        echo $json;
    }
    exit;
}

try {
    // Include database connection
    if (!file_exists('db_connect.php')) {
        throw new Exception("db_connect.php not found.");
    }
    require 'db_connect.php';

    // Determine the 'action'
    $action = $_GET['action'] ?? '';

    switch ($action) {

        // --- GET: List Presets ---
        case 'get_presets':
            $type = $_GET['type'] ?? ''; 
            
            if ($type) {
                $stmt = $pdo->prepare("SELECT id, name, type, data, created_at FROM presets WHERE type = ? ORDER BY created_at DESC");
                $stmt->execute([$type]);
            } else {
                $stmt = $pdo->query("SELECT id, name, type, data, created_at FROM presets ORDER BY created_at DESC");
            }
            
            json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        // --- GET: List Music ---
        case 'get_music':
            $stmt = $pdo->query("SELECT id, filename, filepath FROM music ORDER BY filename ASC");
            json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        // --- GET: List Images ---
        case 'get_images':
            $imgDir = __DIR__ . '/images';
            $result = [];
            if (is_dir($imgDir)) {
                $files = scandir($imgDir);
                foreach ($files as $f) {
                    if ($f === '.' || $f === '..') continue;
                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'])) {
                        $result[] = $f;
                    }
                }
            }
            json_response($result);
            break;

        // --- GET: Browse Files ---
        case 'browse':
            $baseDir = __DIR__ . '/music'; 
            $relPath = $_GET['dir'] ?? '';
            
            // Security: Prevent breaking out of root
            if (strpos($relPath, '..') !== false) $relPath = '';
            
            $currentPath = $relPath ? $baseDir . '/' . $relPath : $baseDir;
            
            if (!is_dir($currentPath)) {
                $currentPath = $baseDir;
                $relPath = '';
            }

            $files = scandir($currentPath);
            $result = [];
            
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                
                $fullPath = $currentPath . '/' . $f;
                $isDir = is_dir($fullPath);
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                
                if ($isDir || in_array($ext, ['mp3', 'wav', 'ogg'])) {
                    $result[] = [
                        'name' => $f,
                        'type' => $isDir ? 'dir' : 'file',
                        'path' => $relPath ? $relPath . '/' . $f : $f
                    ];
                }
            }
            
            usort($result, function($a, $b) {
                if ($a['type'] === $b['type']) return strcasecmp($a['name'], $b['name']);
                return $a['type'] === 'dir' ? -1 : 1;
            });

            $parent = $relPath ? dirname($relPath) : null;
            if ($parent === '.' || $parent === '/' || $parent === '\') $parent = '';

            json_response([
                'path' => $relPath,
                'parent' => $parent,
                'files' => $result
            ]);
            break;

        // --- POST: Save Preset ---
        case 'save_preset':
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (!$input) {
                throw new Exception("Invalid JSON input received.");
            }

            $name = $input['name'] ?? 'Untitled';
            $type = $input['type'] ?? 'Unknown';
            $data = json_encode($input['data']); 

            $stmt = $pdo->prepare("INSERT INTO presets (name, type, data) VALUES (?, ?, ?)");
            $stmt->execute([$name, $type, $data]);
            
            json_response(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;
            
        default:
            json_response(['error' => 'Invalid action specified: ' . $action]);
            break;
    }

} catch (Exception $e) {
    // CATCH ALL ERRORS and return as JSON
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
