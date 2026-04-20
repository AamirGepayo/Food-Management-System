<?php
// ═══════════════════════════════════════════════
//  create.php — Add New Dish
// ═══════════════════════════════════════════════
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$dishes          = trim($_POST['dishes']          ?? '');
$category        = trim($_POST['category']        ?? '');
$price           = trim($_POST['price']           ?? '');
$expiration_date = trim($_POST['expiration_date'] ?? '');
$stock           = trim($_POST['stock']           ?? '');
$image           = '';

// ── Handle Image Upload ──────────────────────────
if (!empty($_FILES['image']['name'])) {
    $uploadDir = 'assets/img/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5 MB

    if (in_array($ext, $allowed) && $_FILES['image']['size'] <= $maxSize) {
        $newName = 'dish_' . uniqid() . '.' . $ext;
        $target  = $uploadDir . $newName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image = $target;
        }
    }
}

$stmt = $pdo->prepare("
    INSERT INTO food_management (dishes, category, price, expiration_date, stock, image)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$dishes, $category, $price, $expiration_date, $stock, $image]);

header('Location: index.php?msg=added'); exit;
?>