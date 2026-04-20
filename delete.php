<?php
// ═══════════════════════════════════════════════
//  delete.php — Delete Single Dish
// ═══════════════════════════════════════════════
require 'db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Remove associated image file
    $stmt = $pdo->prepare("SELECT image FROM food_management WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && !empty($row['image']) && file_exists($row['image'])) {
        @unlink($row['image']);
    }

    $stmt = $pdo->prepare("DELETE FROM food_management WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php?msg=deleted'); exit;
?>