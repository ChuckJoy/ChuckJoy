<?php
// api_v2.php - Fresh version to bypass potential corruption/caching
error_reporting(0);
ini_set('display_errors', 0);

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Helper
function send_json($data) {
    echo json_encode($data);
    exit;
}

try {
    if (!file_exists('db_connect.php')) {
        throw new Exception("DB Config Missing");
    }
    require 'db_connect.php';

    $action = $_GET['action'] ?? '';

    if ($action === 'get_presets') {
        $type = $_GET['type'] ?? '';
        $sql = "SELECT id, name, type, data, created_at FROM presets";
        $params = [];
        if ($type) {
            $sql .= " WHERE type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        send_json($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($action === 'get_music') {
        $stmt = $pdo->query("SELECT id, filename, filepath FROM music ORDER BY filename ASC");
        send_json($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($action === 'get_images') {
        // Simple image scan
        $dir = __DIR__ . '/images';
        $res = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) as $f) {
                if ($f[0] !== '.' && preg_match('/\.(png|jpg|gif)$/i', $f)) $res[] = $f;
            }
        }
        send_json($res);
    }
    elseif ($action === 'save_preset') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("Invalid JSON");
        
        $name = $input['name'] ?? 'Untitled';
        $type = $input['type'] ?? 'General';
        $data = json_encode($input['data'] ?? []);
        $imagePath = null;

        // Handle Image Saving
        if (isset($input['image_data'])) {
            $imageData = $input['image_data'];
            // Remove header (data:image/png;base64,)
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $decodedData = base64_decode($imageData);
            
            if ($decodedData !== false) {
                // Sanitize filename
                $safeName = preg_replace('/[^a-z0-9_-]/i', '', $name);
                $filename = 'preset_' . $safeName . '_' . time() . '.png';
                $targetDir = __DIR__ . '/images/';
                
                if (file_put_contents($targetDir . $filename, $decodedData)) {
                    $imagePath = 'images/' . $filename;
                }
            }
        }

        $stmt = $pdo->prepare("INSERT INTO presets (name, type, data, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $type, $data, $imagePath]);
        
        send_json(['success' => true, 'id' => $pdo->lastInsertId(), 'image' => $imagePath]);
    }
    else {
        send_json(['error' => 'Unknown action']);
    }

} catch (Exception $e) {
    http_response_code(500);
    send_json(['error' => $e->getMessage()]);
}
?>