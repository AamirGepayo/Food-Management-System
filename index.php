<?php
require 'db.php';

$allDishes   = $pdo->query("SELECT * FROM food_management ORDER BY id DESC")->fetchAll();
$totalDishes = count($allDishes);
$totalStock  = 0;
$totalValue  = 0;
$expiringSoon = 0;
$expiredCount = 0;
$lowStock     = 0;
$today        = new DateTime();

foreach ($allDishes as $d) {
    $totalStock += intval($d['stock']);
    $totalValue += floatval($d['price']) * intval($d['stock']);
    if (intval($d['stock']) <= 5) $lowStock++;
    if (!empty($d['expiration_date'])) {
        try {
            $exp  = new DateTime($d['expiration_date']);
            $diff = (int)$today->diff($exp)->format('%r%a');
            if ($diff < 0)           $expiredCount++;
            elseif ($diff <= 7)      $expiringSoon++;
        } catch (Exception $e) {}
    }
}

$msgs = [
    'added'   => ['✅ New dish added!',        'success'],
    'updated' => ['✏️ Dish updated!',          'info'],
    'deleted' => ['🗑️ Dish deleted.',          'warning'],
    'cleared' => ['🧹 All records cleared!',   'danger'],
];
$toast = $msgs[$_GET['msg'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>FoodVault — Food Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="asset\css\style.css"/>
</head>
<body>

<!-- ═══════════════════════════════════════
     ANIMATED BACKGROUND
═══════════════════════════════════════ -->
<div class="bg-wrap" aria-hidden="true">
  <div class="bg-gradient"></div>
  <div class="bg-grid"></div>
  <div class="orb o1"></div>
  <div class="orb o2"></div>
  <div class="orb o3"></div>
  <div class="orb o4"></div>
  <!-- Floating food particles -->
  <div class="particle" style="--x:5%;  --y:10%; --dur:8s;  --delay:0s;   --size:1.4rem">🍕</div>
  <div class="particle" style="--x:90%; --y:8%;  --dur:10s; --delay:1s;   --size:1.2rem">🍜</div>
  <div class="particle" style="--x:3%;  --y:60%; --dur:9s;  --delay:2s;   --size:1.5rem">🥗</div>
  <div class="particle" style="--x:92%; --y:55%; --dur:11s; --delay:.5s;  --size:1.1rem">🍰</div>
  <div class="particle" style="--x:50%; --y:3%;  --dur:7s;  --delay:3s;   --size:1.3rem">🥩</div>
  <div class="particle" style="--x:75%; --y:90%; --dur:12s; --delay:1.5s; --size:1.0rem">🍣</div>
  <div class="particle" style="--x:20%; --y:88%; --dur:8.5s;--delay:2.5s; --size:1.2rem">🍔</div>
  <div class="particle" style="--x:60%; --y:85%; --dur:9.5s;--delay:.8s;  --size:1.1rem">🧆</div>
</div>

<!-- ═══════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
  <div class="sb-top">
    <!-- Logo -->
    <div class="sb-logo">
      <div class="sb-logo-icon">
        <i class="fas fa-utensils"></i>
      </div>
      <div class="sb-logo-text">
        <span class="sb-name">FoodVault</span>
        <span class="sb-ver">Management System</span>
      </div>
    </div>

    <!-- User card -->
    <div class="sb-user">
      <div class="sb-avatar">👨‍🍳</div>
      <div class="sb-user-info">
        <span class="sb-username">Admin</span>
        <span class="sb-role">Restaurant Manager</span>
      </div>
      <div class="sb-status-dot"></div>
    </div>

    <!-- Nav -->
    <nav class="sb-nav">
      <span class="sb-nav-label">MAIN MENU</span>
      <a href="index.php" class="sb-link active">
        <div class="sb-link-icon"><i class="fas fa-th-large"></i></div>
        <span>Dashboard</span>
        <span class="sb-badge"><?= $totalDishes ?></span>
      </a>
      <a href="#" class="sb-link" onclick="openAddModal();return false">
        <div class="sb-link-icon"><i class="fas fa-plus"></i></div>
        <span>Add New Dish</span>
      </a>

      <span class="sb-nav-label" style="margin-top:10px">ALERTS</span>
      <a href="#" class="sb-link <?= $expiringSoon > 0 ? 'has-alert' : '' ?>">
        <div class="sb-link-icon"><i class="fas fa-clock"></i></div>
        <span>Expiring Soon</span>
        <?php if ($expiringSoon > 0): ?>
        <span class="sb-badge warn"><?= $expiringSoon ?></span>
        <?php endif; ?>
      </a>
      <a href="#" class="sb-link <?= $lowStock > 0 ? 'has-alert' : '' ?>">
        <div class="sb-link-icon"><i class="fas fa-box-open"></i></div>
        <span>Low Stock</span>
        <?php if ($lowStock > 0): ?>
        <span class="sb-badge danger"><?= $lowStock ?></span>
        <?php endif; ?>
      </a>
      <?php if ($expiredCount > 0): ?>
      <a href="#" class="sb-link has-alert">
        <div class="sb-link-icon"><i class="fas fa-skull-crossbones"></i></div>
        <span>Expired</span>
        <span class="sb-badge danger"><?= $expiredCount ?></span>
      </a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Sidebar footer -->
  <div class="sb-footer">
    <div class="sb-date">
      <i class="fas fa-calendar-alt"></i>
      <?= date('F j, Y') ?>
    </div>
  </div>
</aside>

<!-- ═══════════════════════════════════════
     MAIN AREA
═══════════════════════════════════════ -->
<div class="main-area">

  <!-- TOP BAR -->
  <header class="topbar">
    <button class="hamburger" onclick="toggleSidebar()">
      <i class="fas fa-bars"></i>
    </button>
    <div class="tb-page">
      <h1 class="tb-title">Dashboard</h1>
      <p class="tb-sub">Welcome back, Admin 👋</p>
    </div>
    <div class="tb-right">
      <div class="tb-search">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search dishes…" autocomplete="off"/>
        <button id="clearSearch" style="display:none" onclick="clearSearch()"><i class="fas fa-xmark"></i></button>
      </div>
      <button class="tb-add-btn" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        <span>Add Dish</span>
      </button>
      <div class="tb-notif" title="Alerts">
        <i class="fas fa-bell"></i>
        <?php if (($expiringSoon + $expiredCount + $lowStock) > 0): ?>
        <span class="notif-dot"></span>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- PAGE CONTENT -->
  <div class="page-content">

    <!-- TOAST -->
    <?php if ($toast): ?>
    <div class="toast toast-<?= $toast[1] ?>" id="toastEl">
      <i class="fas fa-circle-check toast-icon"></i>
      <span><?= htmlspecialchars($toast[0]) ?></span>
      <button onclick="document.getElementById('toastEl').remove()"><i class="fas fa-xmark"></i></button>
    </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="stats-grid">

      <div class="stat-glass stat-orange">
        <div class="sg-left">
          <div class="sg-num" data-target="<?= $totalDishes ?>">0</div>
          <div class="sg-label">Total Dishes</div>
          <div class="sg-bar"><div class="sg-fill" style="width:100%"></div></div>
        </div>
        <div class="sg-icon"><i class="fas fa-bowl-food"></i></div>
        <div class="sg-glow"></div>
      </div>

      <div class="stat-glass stat-purple">
        <div class="sg-left">
          <div class="sg-num" data-target="<?= $totalStock ?>">0</div>
          <div class="sg-label">Total Stock</div>
          <div class="sg-bar"><div class="sg-fill" style="width:<?= min(100, ($totalStock/500)*100) ?>%"></div></div>
        </div>
        <div class="sg-icon"><i class="fas fa-boxes-stacked"></i></div>
        <div class="sg-glow"></div>
      </div>

      <div class="stat-glass stat-green">
        <div class="sg-left">
          <div class="sg-num">₱<?= number_format($totalValue, 0) ?></div>
          <div class="sg-label">Inventory Value</div>
          <div class="sg-bar"><div class="sg-fill" style="width:75%"></div></div>
        </div>
        <div class="sg-icon"><i class="fas fa-peso-sign"></i></div>
        <div class="sg-glow"></div>
      </div>

      <div class="stat-glass stat-red">
        <div class="sg-left">
          <div class="sg-num" data-target="<?= $expiringSoon + $expiredCount ?>">0</div>
          <div class="sg-label">Needs Attention</div>
          <div class="sg-bar"><div class="sg-fill" style="width:<?= min(100,(($expiringSoon+$expiredCount)/max(1,$totalDishes))*100) ?>%"></div></div>
        </div>
        <div class="sg-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="sg-glow"></div>
      </div>

    </div><!-- /stats -->

    <!-- ALERT BANNERS -->
    <?php if ($expiredCount > 0): ?>
    <div class="alert-strip alert-red">
      <i class="fas fa-skull-crossbones"></i>
      <b><?= $expiredCount ?> dish<?= $expiredCount>1?'es':'' ?> already expired</b> — remove or replace them immediately.
      <button class="alert-x" onclick="this.parentElement.remove()"><i class="fas fa-xmark"></i></button>
    </div>
    <?php endif; ?>
    <?php if ($expiringSoon > 0): ?>
    <div class="alert-strip alert-yellow">
      <i class="fas fa-clock"></i>
      <b><?= $expiringSoon ?> dish<?= $expiringSoon>1?'es':'' ?> expiring within 7 days</b> — review your inventory.
      <button class="alert-x" onclick="this.parentElement.remove()"><i class="fas fa-xmark"></i></button>
    </div>
    <?php endif; ?>

    <!-- TABLE CARD -->
    <div class="table-glass">
      <!-- Toolbar -->
      <div class="tg-toolbar">
        <div class="tg-title">
          <i class="fas fa-utensils"></i>
          <span>Dish Inventory</span>
          <span class="tg-count"><?= $totalDishes ?> items</span>
        </div>
        <div class="tg-tools">
          <div class="view-switcher">
            <button class="vs-btn active" id="vsTable" onclick="switchView('table')" title="Table view">
              <i class="fas fa-table-list"></i>
            </button>
            <button class="vs-btn" id="vsGrid" onclick="switchView('grid')" title="Grid view">
              <i class="fas fa-grip"></i>
            </button>
          </div>
          <button class="del-all-btn" onclick="openDelAllModal()">
            <i class="fas fa-trash-can"></i> Delete All
          </button>
        </div>
      </div>

      <!-- TABLE VIEW -->
      <div id="tableView">
        <div class="table-scroll">
          <table id="dishTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Dish Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Expiration</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tableBody">
            <?php if (empty($allDishes)): ?>
              <tr><td colspan="9" class="empty-td">
                <div class="empty-box">
                  <div class="empty-emoji">🍽️</div>
                  <h3>No dishes yet</h3>
                  <p>Click <b>Add Dish</b> to get started</p>
                  <button class="tb-add-btn" onclick="openAddModal()" style="margin-top:12px">
                    <i class="fas fa-plus"></i> Add First Dish
                  </button>
                </div>
              </td></tr>
            <?php else: ?>
            <?php foreach ($allDishes as $i => $d):
              $exp = null; $diff = 999; $isPast = false; $isWarn = false;
              if (!empty($d['expiration_date'])) {
                try {
                  $exp  = new DateTime($d['expiration_date']);
                  $diff = (int)$today->diff($exp)->format('%r%a');
                  $isPast = $diff < 0;
                  $isWarn = !$isPast && $diff <= 7;
                } catch (Exception $e) {}
              }
              $expCls = $isPast ? 'ec-bad' : ($isWarn ? 'ec-warn' : 'ec-ok');
              $expTxt = $isPast
                ? 'Expired '.(abs($diff)).'d ago'
                : ($isWarn ? $diff.'d left' : date('M d, Y', strtotime($d['expiration_date'])));
              $stockInt = intval($d['stock']);
              $sCls = $stockInt <= 0 ? 'sc-zero' : ($stockInt <= 5 ? 'sc-low' : ($stockInt <= 20 ? 'sc-med' : 'sc-ok'));
              $statusTxt = $isPast ? 'Expired' : ($isWarn ? 'Warning' : 'Good');
              $statusCls = $isPast ? 'st-bad' : ($isWarn ? 'st-warn' : 'st-ok');
            ?>
            <tr class="dish-row"
                data-name="<?= strtolower(htmlspecialchars($d['dishes'])) ?>"
                data-cat="<?= strtolower(htmlspecialchars($d['category'])) ?>">
              <td class="td-n"><?= $i+1 ?></td>
              <td class="td-img">
                <?php if (!empty($d['image']) && file_exists($d['image'])): ?>
                  <img src="<?= htmlspecialchars($d['image']) ?>" class="dish-thumb"
                       onclick="openLightbox('<?= htmlspecialchars($d['image']) ?>','<?= addslashes(htmlspecialchars($d['dishes'])) ?>')"
                       alt="<?= htmlspecialchars($d['dishes']) ?>"/>
                <?php else: ?>
                  <div class="thumb-ph">🍴</div>
                <?php endif; ?>
              </td>
              <td class="td-name"><?= htmlspecialchars($d['dishes']) ?></td>
              <td><span class="cat-chip"><?= htmlspecialchars($d['category']) ?></span></td>
              <td class="td-price">₱<?= number_format(floatval($d['price']),2) ?></td>
              <td><span class="exp-chip <?= $expCls ?>"><?= $expTxt ?></span></td>
              <td>
                <div class="stock-wrap">
                  <span class="stock-num <?= $sCls ?>"><?= $stockInt ?></span>
                  <div class="stock-track"><div class="stock-bar <?= $sCls ?>" style="width:<?= min(100,($stockInt/100)*100) ?>%"></div></div>
                </div>
              </td>
              <td><span class="status-dot <?= $statusCls ?>"><?= $statusTxt ?></span></td>
              <td class="td-acts">
                <button class="act view-act" onclick="openViewModal(<?= $d['id'] ?>)" title="View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="act edit-act" onclick="openEditModal(<?= $d['id'] ?>)" title="Edit">
                  <i class="fas fa-pen"></i>
                </button>
                <button class="act del-act" onclick="openDelModal(<?= $d['id'] ?>,'<?= addslashes(htmlspecialchars($d['dishes'])) ?>')" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div id="noResults" class="no-results" style="display:none">
          <i class="fas fa-magnifying-glass"></i> No dishes match your search.
        </div>
        <div class="tg-footer">
          <span id="countLbl">Showing <?= $totalDishes ?> dish<?= $totalDishes!==1?'es':'' ?></span>
        </div>
      </div>

      <!-- GRID VIEW -->
      <div id="gridView" style="display:none">
        <div class="food-grid" id="foodGrid">
        <?php foreach ($allDishes as $d):
          $exp = null; $diff = 999; $isPast = false; $isWarn = false;
          if (!empty($d['expiration_date'])) {
            try {
              $exp  = new DateTime($d['expiration_date']);
              $diff = (int)$today->diff($exp)->format('%r%a');
              $isPast = $diff < 0; $isWarn = !$isPast && $diff <= 7;
            } catch(Exception $e) {}
          }
          $statusCls = $isPast ? 'st-bad' : ($isWarn ? 'st-warn' : 'st-ok');
          $statusTxt = $isPast ? 'Expired' : ($isWarn ? 'Expiring Soon' : 'Fresh');
        ?>
        <div class="food-card"
             data-name="<?= strtolower(htmlspecialchars($d['dishes'])) ?>"
             data-cat="<?= strtolower(htmlspecialchars($d['category'])) ?>">
          <div class="fc-img-wrap">
            <?php if (!empty($d['image']) && file_exists($d['image'])): ?>
              <img src="<?= htmlspecialchars($d['image']) ?>" alt="<?= htmlspecialchars($d['dishes']) ?>"
                   onclick="openLightbox('<?= htmlspecialchars($d['image']) ?>','<?= addslashes(htmlspecialchars($d['dishes'])) ?>')"/>
            <?php else: ?>
              <div class="fc-img-ph">🍴</div>
            <?php endif; ?>
            <span class="fc-status <?= $statusCls ?>"><?= $statusTxt ?></span>
          </div>
          <div class="fc-body">
            <div class="fc-cat"><?= htmlspecialchars($d['category']) ?></div>
            <h3 class="fc-name"><?= htmlspecialchars($d['dishes']) ?></h3>
            <div class="fc-meta">
              <span class="fc-price">₱<?= number_format(floatval($d['price']),2) ?></span>
              <span class="fc-stock <?= intval($d['stock'])<=5?'fc-low':'' ?>">
                <i class="fas fa-box"></i> <?= $d['stock'] ?>
              </span>
            </div>
            <div class="fc-exp <?= $isPast?'ec-bad':($isWarn?'ec-warn':'ec-ok') ?>">
              <i class="fas fa-calendar"></i>
              <?= !empty($d['expiration_date']) ? date('M d, Y', strtotime($d['expiration_date'])) : '—' ?>
            </div>
          </div>
          <div class="fc-actions">
            <button class="act view-act fc-btn" onclick="openViewModal(<?= $d['id'] ?>)"><i class="fas fa-eye"></i></button>
            <button class="act edit-act fc-btn" onclick="openEditModal(<?= $d['id'] ?>)"><i class="fas fa-pen"></i></button>
            <button class="act del-act fc-btn" onclick="openDelModal(<?= $d['id'] ?>,'<?= addslashes(htmlspecialchars($d['dishes'])) ?>')"><i class="fas fa-trash"></i></button>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($allDishes)): ?>
        <div class="empty-box" style="grid-column:1/-1;text-align:center;padding:60px 20px">
          <div class="empty-emoji">🍽️</div><h3>No dishes yet</h3>
          <p>Click <b>Add Dish</b> to get started</p>
        </div>
        <?php endif; ?>
        </div>
      </div>

    </div><!-- /table-glass -->
  </div><!-- /page-content -->
</div><!-- /main-area -->

<!-- ═══════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════ -->

<!-- ── ADD MODAL ──────────────────────── -->
<div class="modal-backdrop" id="addModal">
 <div class="modal">
  <div class="modal-hd hd-green">
    <div class="mhd-icon"><i class="fas fa-plus-circle"></i></div>
    <div><h2>Add New Dish</h2><p>Fill in the details below</p></div>
    <button class="mhd-close" onclick="closeModal('addModal')"><i class="fas fa-xmark"></i></button>
  </div>
  <form action="create.php" method="POST" enctype="multipart/form-data" class="modal-body">
    <div class="form-2col">
      <div class="form-col">
        <div class="fg"><label><i class="fas fa-bowl-food"></i> Dish Name <span class="req">*</span></label>
          <input type="text" name="dishes" placeholder="e.g. Chocolate Cake" required/></div>
        <div class="fg"><label><i class="fas fa-tag"></i> Category <span class="req">*</span></label>
          <select name="category" required>
            <option value="">— Select —</option>
            <?php foreach(['Main Course','Appetizer','Dessert','Beverage','Snack','Soup','Salad','Pasta','Seafood','Grilled','Street Food','Breakfast','Other'] as $c): ?>
            <option><?= $c ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="fgrow">
          <div class="fg"><label><i class="fas fa-peso-sign"></i> Price <span class="req">*</span></label>
            <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required/></div>
          <div class="fg"><label><i class="fas fa-boxes-stacked"></i> Stock <span class="req">*</span></label>
            <input type="number" name="stock" min="0" placeholder="0" required/></div>
        </div>
        <div class="fg"><label><i class="fas fa-calendar-day"></i> Expiration Date <span class="req">*</span></label>
          <input type="date" name="expiration_date" required/></div>
      </div>
      <div class="form-col">
        <div class="fg">
          <label><i class="fas fa-camera"></i> Dish Photo</label>
          <div class="drop-zone" id="addDZ" onclick="document.getElementById('addFile').click()"
               ondragover="dzOver(event,this)" ondragleave="dzLeave(this)" ondrop="dzDrop(event,this,'addFile','addPrev')">
            <i class="fas fa-cloud-arrow-up dz-icon"></i>
            <span class="dz-txt">Click or drag & drop</span>
            <span class="dz-sub">PNG, JPG, WEBP · Max 5MB</span>
            <input type="file" id="addFile" name="image" accept="image/*" style="display:none"
                   onchange="fileChosen(this,'addPrev',this.closest('.drop-zone'))"/>
          </div>
          <img id="addPrev" class="file-prev" style="display:none" alt=""/>
          <button type="button" id="addClear" class="clear-btn" style="display:none"
                  onclick="clearFile('addFile','addPrev','addDZ','addClear')">
            <i class="fas fa-xmark"></i> Remove photo
          </button>
        </div>
      </div>
    </div>
    <div class="modal-ft">
      <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
      <button type="submit" class="btn-save btn-green"><i class="fas fa-floppy-disk"></i> Save Dish</button>
    </div>
  </form>
 </div>
</div>

<!-- ── EDIT MODAL ─────────────────────── -->
<div class="modal-backdrop" id="editModal">
 <div class="modal">
  <div class="modal-hd hd-blue">
    <div class="mhd-icon"><i class="fas fa-pen-to-square"></i></div>
    <div><h2>Edit Dish</h2><p>Update the information below</p></div>
    <button class="mhd-close" onclick="closeModal('editModal')"><i class="fas fa-xmark"></i></button>
  </div>
  <div class="modal-loading" id="editLoad"><div class="spinner"></div><span>Loading…</span></div>
  <form action="update.php" method="POST" enctype="multipart/form-data" class="modal-body" id="editForm" style="display:none">
    <input type="hidden" name="id" id="eId"/>
    <div class="form-2col">
      <div class="form-col">
        <div class="fg"><label><i class="fas fa-bowl-food"></i> Dish Name <span class="req">*</span></label>
          <input type="text" name="dishes" id="eDishes" required/></div>
        <div class="fg"><label><i class="fas fa-tag"></i> Category <span class="req">*</span></label>
          <select name="category" id="eCategory" required>
            <option value="">— Select —</option>
            <?php foreach(['Main Course','Appetizer','Dessert','Beverage','Snack','Soup','Salad','Pasta','Seafood','Grilled','Street Food','Breakfast','Other'] as $c): ?>
            <option><?= $c ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="fgrow">
          <div class="fg"><label><i class="fas fa-peso-sign"></i> Price <span class="req">*</span></label>
            <input type="number" name="price" id="ePrice" step="0.01" min="0" required/></div>
          <div class="fg"><label><i class="fas fa-boxes-stacked"></i> Stock <span class="req">*</span></label>
            <input type="number" name="stock" id="eStock" min="0" required/></div>
        </div>
        <div class="fg"><label><i class="fas fa-calendar-day"></i> Expiration Date <span class="req">*</span></label>
          <input type="date" name="expiration_date" id="eExp" required/></div>
      </div>
      <div class="form-col">
        <div class="fg">
          <label><i class="fas fa-camera"></i> Replace Photo <small>(blank = keep current)</small></label>
          <div class="drop-zone" id="editDZ" onclick="document.getElementById('editFile').click()"
               ondragover="dzOver(event,this)" ondragleave="dzLeave(this)" ondrop="dzDrop(event,this,'editFile','editPrev')">
            <i class="fas fa-cloud-arrow-up dz-icon"></i>
            <span class="dz-txt">Click or drag & drop</span>
            <span class="dz-sub">PNG, JPG, WEBP · Max 5MB</span>
            <input type="file" id="editFile" name="image" accept="image/*" style="display:none"
                   onchange="fileChosen(this,'editPrev',this.closest('.drop-zone'))"/>
          </div>
          <img id="editPrev" class="file-prev" alt="Current photo"/>
          <button type="button" id="editClear" class="clear-btn" style="display:none"
                  onclick="clearFile('editFile','editPrev','editDZ','editClear')">
            <i class="fas fa-xmark"></i> Remove new photo
          </button>
        </div>
      </div>
    </div>
    <div class="modal-ft">
      <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
      <button type="submit" class="btn-save btn-blue"><i class="fas fa-floppy-disk"></i> Update Dish</button>
    </div>
  </form>
 </div>
</div>

<!-- ── VIEW MODAL ─────────────────────── -->
<div class="modal-backdrop" id="viewModal">
 <div class="modal modal-view">
  <div class="modal-hd hd-dark">
    <div class="mhd-icon"><i class="fas fa-eye"></i></div>
    <div><h2>Dish Details</h2><p>Full dish information</p></div>
    <button class="mhd-close" onclick="closeModal('viewModal')"><i class="fas fa-xmark"></i></button>
  </div>
  <div class="modal-loading" id="viewLoad"><div class="spinner"></div><span>Loading…</span></div>
  <div class="view-content" id="viewContent" style="display:none">
    <div class="vc-img"><img id="vImg" src="" alt=""/><div id="vImgPh" class="vc-ph">🍴</div></div>
    <div class="vc-info">
      <h2 class="vc-name" id="vName"></h2>
      <div class="vc-rows">
        <div class="vc-row"><span class="vcr-lbl"><i class="fas fa-tag"></i> Category</span><span id="vCat" class="vcr-val"></span></div>
        <div class="vc-row"><span class="vcr-lbl"><i class="fas fa-peso-sign"></i> Price</span><span id="vPrice" class="vcr-val vcr-price"></span></div>
        <div class="vc-row"><span class="vcr-lbl"><i class="fas fa-calendar"></i> Expiration</span><span id="vExp" class="vcr-val"></span></div>
        <div class="vc-row"><span class="vcr-lbl"><i class="fas fa-boxes-stacked"></i> Stock</span><span id="vStock" class="vcr-val"></span></div>
      </div>
      <div class="vc-actions">
        <button class="btn-save btn-blue" id="vEditBtn"><i class="fas fa-pen"></i> Edit This Dish</button>
      </div>
    </div>
  </div>
 </div>
</div>

<!-- ── LIGHTBOX ────────────────────────── -->
<div class="modal-backdrop" id="lbModal" onclick="if(event.target===this)closeModal('lbModal')">
  <div class="lb-wrap">
    <button class="lb-close" onclick="closeModal('lbModal')"><i class="fas fa-xmark"></i></button>
    <img id="lbImg" src="" alt="" class="lb-img"/>
    <p id="lbCap" class="lb-cap"></p>
  </div>
</div>

<!-- ── DELETE CONFIRM ─────────────────── -->
<div class="modal-backdrop" id="delModal">
 <div class="modal modal-sm">
  <div class="confirm-wrap">
    <div class="confirm-emoji">🗑️</div>
    <h2>Delete Dish?</h2>
    <p>Are you sure you want to delete<br/><strong id="delName"></strong>?</p>
    <p class="confirm-note">This cannot be undone.</p>
    <div class="confirm-btns">
      <button class="btn-cancel" onclick="closeModal('delModal')">Cancel</button>
      <a id="delLink" href="#" class="btn-save btn-red"><i class="fas fa-trash"></i> Delete</a>
    </div>
  </div>
 </div>
</div>

<!-- ── DELETE ALL CONFIRM ─────────────── -->
<div class="modal-backdrop" id="delAllModal">
 <div class="modal modal-sm">
  <div class="confirm-wrap">
    <div class="confirm-emoji">⚠️</div>
    <h2>Delete Everything?</h2>
    <p>This will permanently delete all <strong><?= $totalDishes ?> records</strong> and all uploaded photos.</p>
    <p class="confirm-note">This action is irreversible!</p>
    <div class="confirm-btns">
      <button class="btn-cancel" onclick="closeModal('delAllModal')">Cancel</button>
      <a href="delete_all.php" class="btn-save btn-red"><i class="fas fa-fire"></i> Wipe All</a>
    </div>
  </div>
 </div>
</div>

<script src="asset\js\script.js"></script>
</body>
</html>