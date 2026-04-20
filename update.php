<?php
// ═══════════════════════════════════════════════
//  update.php — Update Existing Dish
// ═══════════════════════════════════════════════
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$id              = intval($_POST['id']            ?? 0);
$dishes          = trim($_POST['dishes']          ?? '');
$category        = trim($_POST['category']        ?? '');
$price           = trim($_POST['price']           ?? '');
$expiration_date = trim($_POST['expiration_date'] ?? '');
$stock           = trim($_POST['stock']           ?? '');

// ── Fetch current image ──────────────────────────
$stmt = $pdo->prepare("SELECT image FROM food_management WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetch();
$image   = $current['image'] ?? '';

// ── Handle New Image Upload ──────────────────────
if (!empty($_FILES['image']['name'])) {
    $uploadDir = 'assets/img/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    if (in_array($ext, $allowed) && $_FILES['image']['size'] <= $maxSize) {
        $newName = 'dish_' . uniqid() . '.' . $ext;
        $target  = $uploadDir . $newName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Delete old image
            if ($image && file_exists($image)) @unlink($image);
            $image = $target;
        }
    }
}

$stmt = $pdo->prepare("
    UPDATE food_management
    SET dishes = ?, category = ?, price = ?, expiration_date = ?, stock = ?, image = ?
    WHERE id = ?
");
$stmt->execute([$dishes, $category, $price, $expiration_date, $stock, $image, $id]);

header('Location: index.php?msg=updated'); exit;
?>