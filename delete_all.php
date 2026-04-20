<?php
// ═══════════════════════════════════════════════
//  delete_all.php — Delete All Dishes
// ═══════════════════════════════════════════════
require 'db.php';

// Remove all uploaded images
$stmt = $pdo->query("SELECT image FROM food_management WHERE image != ''");
foreach ($stmt->fetchAll() as $row) {
    if (!empty($row['image']) && file_exists($row['image'])) {
        @unlink($row['image']);
    }
}

// Clear table and reset auto-increment
$pdo->exec("DELETE FROM food_management");
$pdo->exec("ALTER TABLE food_management AUTO_INCREMENT = 1");

header('Location: index.php?msg=cleared'); exit;
?>