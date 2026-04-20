<?php
// ═══════════════════════════════════════════════
//  db.php — Database Connection
//  Database: food_management_system
// ═══════════════════════════════════════════════

$host     = 'localhost';
$dbname   = 'food_management_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('
    <!DOCTYPE html><html><head>
    <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{font-family:sans-serif;background:#fff8f0;display:flex;align-items:center;
           justify-content:center;min-height:100vh}
      .box{background:#fff;border-radius:16px;padding:40px;max-width:480px;width:90%;
           box-shadow:0 8px 32px rgba(0,0,0,.12);text-align:center}
      .icon{font-size:3rem;margin-bottom:16px}
      h2{color:#e84545;margin-bottom:10px;font-size:1.4rem}
      p{color:#666;line-height:1.6;font-size:.9rem}
      code{background:#f5f5f5;padding:2px 8px;border-radius:6px;font-size:.85rem}
    </style></head><body>
    <div class="box">
      <div class="icon">⚠️</div>
      <h2>Database Connection Failed</h2>
      <p>' . htmlspecialchars($e->getMessage()) . '</p>
      <p style="margin-top:12px">
        Please make sure XAMPP is running and import
        <code>setup.sql</code> in phpMyAdmin.
      </p>
    </div></body></html>');
}
?>