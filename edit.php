<?php
// ═══════════════════════════════════════════════
//  edit.php — Return dish data as JSON (for Edit Modal)
// ═══════════════════════════════════════════════
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM food_management WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Record not found']);
}
?>