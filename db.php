<?php
// ═══════════════════════════════════════════════
//  db.php — Database Connection
// ═══════════════════════════════════════════════

$host     = 'localhost';
$dbname   = 'food_management_system';
$username = 'root';
$password = '';

// Check if called from edit.php (AJAX) - return JSON error
$isAjax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    basename($_SERVER['PHP_SELF']) === 'edit.php'
);

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
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(['error' => 'DB Error: ' . $e->getMessage()]));
    }
    die('
    <!DOCTYPE html><html><head>
    <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{font-family:sans-serif;background:#0d0d1a;display:flex;align-items:center;
           justify-content:center;min-height:100vh}
      .box{background:#1a1a2e;border:1px solid rgba(255,255,255,.1);border-radius:16px;
           padding:40px;max-width:500px;width:90%;text-align:center;color:#f1f5f9}
      .icon{font-size:3rem;margin-bottom:16px}
      h2{color:#f87171;margin-bottom:10px;font-size:1.3rem}
      p{color:#94a3b8;line-height:1.7;font-size:.9rem;margin-top:8px}
      code{background:rgba(255,255,255,.08);padding:2px 8px;border-radius:6px;
           font-size:.85rem;color:#fb923c}
      .steps{text-align:left;margin-top:16px;background:rgba(255,255,255,.04);
             padding:16px;border-radius:10px;font-size:.85rem;color:#94a3b8}
      .steps li{margin-bottom:6px;list-style:decimal;margin-left:16px}
    </style></head><body>
    <div class="box">
      <div class="icon">⚠️</div>
      <h2>Database Connection Failed</h2>
      <p>' . htmlspecialchars($e->getMessage()) . '</p>
      <div class="steps">
        <strong style="color:#f1f5f9">How to fix:</strong>
        <ol style="margin-top:8px">
          <li>Open <strong>XAMPP Control Panel</strong></li>
          <li>Click <strong>Start</strong> on Apache and MySQL</li>
          <li>Go to <code>localhost/phpmyadmin</code></li>
          <li>Import <code>setup.sql</code> if not yet done</li>
          <li>Refresh this page</li>
        </ol>
      </div>
    </div></body></html>');
}
?>