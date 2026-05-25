<?php
// ═══════════════════════════════════════════════
//  edit.php — Return dish data as JSON
// ═══════════════════════════════════════════════

// Always return JSON - never HTML
header('Content-Type: application/json; charset=utf-8');

// Catch DB errors and return JSON error instead of HTML
try {
    require 'db.php';
} catch (Throwable $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM food_management WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Record not found']);
    }
} catch (Throwable $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
?>