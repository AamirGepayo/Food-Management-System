<?php
// ═══════════════════════════════════════════════
//  index.php — Food Management System Main Page
// ═══════════════════════════════════════════════
require 'db.php';

// Fetch all dishes
$allDishes = $pdo->query("SELECT * FROM food_management ORDER BY id DESC")->fetchAll();

// Stats
$totalDishes = count($allDishes);
$totalStock  = 0;
$totalValue  = 0;
$expiringSoon = 0;
$today = new DateTime();

foreach ($allDishes as $d) {
    $totalStock += intval($d['stock']);
    $totalValue += floatval($d['price']) * intval($d['stock']);
    if (!empty($d['expiration_date'])) {
        try {
            $exp  = new DateTime($d['expiration_date']);
            $diff = (int) $today->diff($exp)->format('%r%a');
            if ($diff >= 0 && $diff <= 7) $expiringSoon++;
        } catch (Exception $e) {}
    }
}

// Toast message
$msgs = [
    'added'   => ['🍽️ New dish added successfully!',   'success'],
    'updated' => ['✏️  Dish updated successfully!',    'info'],
    'deleted' => ['🗑️ Dish deleted.',                  'warning'],
    'cleared' => ['🧹 All records cleared!',            'danger'],
];
$msgKey  = $_GET['msg'] ?? '';
$msgData = $msgs[$msgKey] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>🍴 Food Management System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="asset\css\style.css"/>
</head>
<body>

<!-- ░░ BACKGROUND DECORATION ░░░░░░░░░░░░░░░░░░░░░░░ -->
<div class="bg-art" aria-hidden="true">
  <svg class="bg-wave" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
    <defs>
      <radialGradient id="rg1" cx="20%" cy="20%"><stop offset="0%" stop-color="#ff9a3c" stop-opacity=".18"/><stop offset="100%" stop-color="transparent"/></radialGradient>
      <radialGradient id="rg2" cx="80%" cy="75%"><stop offset="0%" stop-color="#ff5e3a" stop-opacity=".13"/><stop offset="100%" stop-color="transparent"/></radialGradient>
      <radialGradient id="rg3" cx="55%" cy="45%"><stop offset="0%" stop-color="#ffc947" stop-opacity=".10"/><stop offset="100%" stop-color="transparent"/></radialGradient>
    </defs>
    <rect width="1440" height="900" fill="url(#rg1)"/>
    <rect width="1440" height="900" fill="url(#rg2)"/>
    <rect width="1440" height="900" fill="url(#rg3)"/>
  </svg>
  <div class="bg-dots"></div>
  <!-- Floating food emojis -->
  <span class="float-emoji" style="--x:8%;--y:15%;--d:6s;--s:1.4">🍕</span>
  <span class="float-emoji" style="--x:88%;--y:10%;--d:8s;--s:1.1">🍜</span>
  <span class="float-emoji" style="--x:5%;--y:70%;--d:7s;--s:1.6">🥗</span>
  <span class="float-emoji" style="--x:92%;--y:65%;--d:9s;--s:1.0">🍰</span>
  <span class="float-emoji" style="--x:50%;--y:5%;--d:5s;--s:1.3">🥩</span>
  <span class="float-emoji" style="--x:75%;--y:88%;--d:10s;--s:1.2">🍣</span>
  <span class="float-emoji" style="--x:20%;--y:90%;--d:6.5s;--s:0.9">🍔</span>
</div>

<!-- ░░ HEADER ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ -->
<header class="site-header">
  <div class="header-inner">
    <div class="brand">
      <div class="brand-emblem">🍴</div>
      <div class="brand-text">
        <span class="brand-name">FoodVault</span>
        <span class="brand-tagline">Food Management System</span>
      </div>
    </div>
    <nav class="header-nav">
      <div class="header-date">
        <i class="fas fa-calendar-alt"></i>
        <?= date('F j, Y') ?>
      </div>
      <button class="btn-add-hero" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add New Dish
      </button>
    </nav>
  </div>
</header>

<!-- ░░ MAIN ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ -->
<main class="main">

  <!-- ── Toast Notification ──────────────────────── -->
  <?php if ($msgData): ?>
  <div class="toast toast-<?= $msgData[1] ?>" id="toast" role="alert">
    <span class="toast-icon">
      <?php $icons=['success'=>'check-circle','info'=>'pen-to-square','warning'=>'trash-can','danger'=>'broom'];
            echo '<i class="fas fa-' . ($icons[$msgData[1]] ?? 'bell') . '"></i>'; ?>
    </span>
    <span class="toast-msg"><?= htmlspecialchars($msgData[0]) ?></span>
    <button class="toast-x" onclick="this.closest('.toast').remove()" aria-label="Close">
      <i class="fas fa-xmark"></i>
    </button>
  </div>
  <?php endif; ?>

  <!-- ── Stats Row ───────────────────────────────── -->
  <section class="stats-row">
    <div class="stat-card" style="--accent:#ff7c38">
      <div class="stat-icon-wrap" style="background:rgba(255,124,56,.15)">
        <i class="fas fa-bowl-food" style="color:#ff7c38"></i>
      </div>
      <div class="stat-info">
        <span class="stat-num" data-count="<?= $totalDishes ?>"><?= $totalDishes ?></span>
        <span class="stat-lbl">Total Dishes</span>
      </div>
    </div>
    <div class="stat-card" style="--accent:#22b07d">
      <div class="stat-icon-wrap" style="background:rgba(34,176,125,.15)">
        <i class="fas fa-boxes-stacked" style="color:#22b07d"></i>
      </div>
      <div class="stat-info">
        <span class="stat-num" data-count="<?= $totalStock ?>"><?= number_format($totalStock) ?></span>
        <span class="stat-lbl">Units in Stock</span>
      </div>
    </div>
    <div class="stat-card" style="--accent:#3b82f6">
      <div class="stat-icon-wrap" style="background:rgba(59,130,246,.15)">
        <i class="fas fa-peso-sign" style="color:#3b82f6"></i>
      </div>
      <div class="stat-info">
        <span class="stat-num">₱<?= number_format($totalValue, 0) ?></span>
        <span class="stat-lbl">Inventory Value</span>
      </div>
    </div>
    <div class="stat-card" style="--accent:#f59e0b">
      <div class="stat-icon-wrap" style="background:rgba(245,158,11,.15)">
        <i class="fas fa-clock" style="color:#f59e0b"></i>
      </div>
      <div class="stat-info">
        <span class="stat-num" data-count="<?= $expiringSoon ?>"><?= $expiringSoon ?></span>
        <span class="stat-lbl">Expiring Soon</span>
      </div>
    </div>
  </section>

  <!-- ── Table Card ──────────────────────────────── -->
  <section class="table-card">

    <!-- Toolbar -->
    <div class="toolbar">
      <h2 class="toolbar-title">
        <i class="fas fa-utensils"></i> Dish Inventory
        <span class="toolbar-count"><?= $totalDishes ?> items</span>
      </h2>
      <div class="toolbar-controls">
        <div class="search-box">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" id="searchInput"
                 placeholder="Search dishes or category…"
                 autocomplete="off"/>
          <button id="clearSearch" class="search-clear" style="display:none" title="Clear">
            <i class="fas fa-xmark"></i>
          </button>
        </div>
        <button class="btn-delete-all" onclick="openDeleteAllModal()" title="Delete all records">
          <i class="fas fa-trash-can"></i> Delete All
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table id="dishTable">
        <thead>
          <tr>
            <th class="th-no">#</th>
            <th class="th-img">Photo</th>
            <th class="th-dish">Dish Name</th>
            <th class="th-cat">Category</th>
            <th class="th-price">Price</th>
            <th class="th-exp">Expiration</th>
            <th class="th-stock">Stock</th>
            <th class="th-act">Actions</th>
          </tr>
        </thead>
        <tbody id="tableBody">
        <?php if (empty($allDishes)): ?>
          <tr class="empty-row">
            <td colspan="8">
              <div class="empty-state">
                <div class="empty-icon">🍽️</div>
                <h3>No dishes yet</h3>
                <p>Click <strong>Add New Dish</strong> to populate your inventory.</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($allDishes as $i => $d):
            // Expiration logic
            $expStatus  = '';
            $expClass   = '';
            $expLabel   = '';
            if (!empty($d['expiration_date'])) {
                try {
                    $exp  = new DateTime($d['expiration_date']);
                    $diff = (int) $today->diff($exp)->format('%r%a');
                    if ($diff < 0)      { $expClass = 'exp-bad';  $expLabel = 'Expired';       }
                    elseif ($diff <= 3) { $expClass = 'exp-crit'; $expLabel = $diff.'d left';   }
                    elseif ($diff <= 7) { $expClass = 'exp-warn'; $expLabel = $diff.'d left';   }
                    else                { $expClass = 'exp-ok';   $expLabel = date('M d, Y', strtotime($d['expiration_date'])); }
                } catch (Exception $e) {
                    $expLabel = htmlspecialchars($d['expiration_date']);
                }
            }
            // Stock level
            $stockInt = intval($d['stock']);
            $stockCls = $stockInt <= 0 ? 'stock-zero' : ($stockInt <= 5 ? 'stock-low' : ($stockInt <= 20 ? 'stock-med' : 'stock-ok'));
          ?>
          <tr class="dish-row" data-name="<?= strtolower(htmlspecialchars($d['dishes'])) ?>" data-cat="<?= strtolower(htmlspecialchars($d['category'])) ?>">
            <td class="td-no"><?= $i + 1 ?></td>
            <td class="td-img">
              <?php if (!empty($d['image']) && file_exists($d['image'])): ?>
                <img src="<?= htmlspecialchars($d['image']) ?>"
                     alt="<?= htmlspecialchars($d['dishes']) ?>"
                     class="dish-thumb"
                     onclick="openPhotoModal('<?= htmlspecialchars($d['image']) ?>','<?= addslashes(htmlspecialchars($d['dishes'])) ?>')"
                     title="View photo"/>
              <?php else: ?>
                <div class="dish-thumb-ph">🍴</div>
              <?php endif; ?>
            </td>
            <td class="td-name">
              <span class="dish-name-txt"><?= htmlspecialchars($d['dishes']) ?></span>
            </td>
            <td class="td-cat">
              <span class="cat-pill"><?= htmlspecialchars($d['category']) ?></span>
            </td>
            <td class="td-price">
              <span class="price-txt">₱<?= number_format(floatval($d['price']), 2) ?></span>
            </td>
            <td class="td-exp">
              <span class="exp-tag <?= $expClass ?>"><?= $expLabel ?></span>
            </td>
            <td class="td-stock">
              <span class="stock-pill <?= $stockCls ?>"><?= htmlspecialchars($d['stock']) ?></span>
            </td>
            <td class="td-actions">
              <button class="act-btn view-btn"
                      onclick="openViewModal(<?= $d['id'] ?>)"
                      title="View Details">
                <i class="fas fa-eye"></i>
              </button>
              <button class="act-btn edit-btn"
                      onclick="openEditModal(<?= $d['id'] ?>)"
                      title="Edit Dish">
                <i class="fas fa-pen"></i>
              </button>
              <button class="act-btn del-btn"
                      onclick="openDeleteModal(<?= $d['id'] ?>, '<?= addslashes(htmlspecialchars($d['dishes'])) ?>')"
                      title="Delete Dish">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- No results -->
    <div id="noResults" class="no-results" style="display:none">
      <i class="fas fa-magnifying-glass"></i>
      No dishes match "<span id="noResultsQuery"></span>"
    </div>

    <!-- Footer -->
    <div class="table-footer">
      <span id="countLabel">Showing <?= $totalDishes ?> dish<?= $totalDishes !== 1 ? 'es' : '' ?></span>
    </div>

  </section>
</main>

<!-- ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
     MODALS
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ -->

<!-- ═══ ADD MODAL ════════════════════════════════════ -->
<div class="modal-overlay" id="addModal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
  <div class="modal modal-form-type">

    <div class="modal-header mh-green">
      <div class="mh-left">
        <div class="mh-icon"><i class="fas fa-plus-circle"></i></div>
        <div>
          <h2 id="addModalTitle">Add New Dish</h2>
          <p>Fill in the details below to add to your inventory</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('addModal')" aria-label="Close">
        <i class="fas fa-xmark"></i>
      </button>
    </div>

    <form action="create.php" method="POST" enctype="multipart/form-data" class="modal-form" autocomplete="off">
      <div class="form-grid">

        <div class="form-col">
          <div class="field-group">
            <label for="add_dishes">
              <i class="fas fa-bowl-food"></i> Dish Name <span class="req">*</span>
            </label>
            <input type="text" id="add_dishes" name="dishes"
                   placeholder="e.g. Chocolate Cake" required/>
          </div>
          <div class="field-group">
            <label for="add_category">
              <i class="fas fa-tag"></i> Category <span class="req">*</span>
            </label>
            <select id="add_category" name="category" required>
              <option value="">— Choose a category —</option>
              <option>Main Course</option>
              <option>Appetizer</option>
              <option>Dessert</option>
              <option>Beverage</option>
              <option>Snack</option>
              <option>Soup</option>
              <option>Salad</option>
              <option>Pasta</option>
              <option>Seafood</option>
              <option>Grilled</option>
              <option>Street Food</option>
              <option>Breakfast</option>
              <option>Other</option>
            </select>
          </div>
          <div class="field-row">
            <div class="field-group">
              <label for="add_price">
                <i class="fas fa-peso-sign"></i> Price <span class="req">*</span>
              </label>
              <input type="number" id="add_price" name="price"
                     placeholder="0.00" step="0.01" min="0" required/>
            </div>
            <div class="field-group">
              <label for="add_stock">
                <i class="fas fa-boxes-stacked"></i> Stock <span class="req">*</span>
              </label>
              <input type="number" id="add_stock" name="stock"
                     placeholder="0" min="0" required/>
            </div>
          </div>
          <div class="field-group">
            <label for="add_exp">
              <i class="fas fa-calendar-day"></i> Expiration Date <span class="req">*</span>
            </label>
            <input type="date" id="add_exp" name="expiration_date" required/>
          </div>
        </div>

        <div class="form-col">
          <div class="field-group">
            <label><i class="fas fa-camera"></i> Dish Photo</label>
            <div class="file-drop-zone" id="addDropZone"
                 onclick="document.getElementById('add_image').click()"
                 ondragover="handleDragOver(event)" ondrop="handleDrop(event,'add_image','addPreview','addDropZone')">
              <div class="fdz-inner" id="addDropInner">
                <i class="fas fa-cloud-arrow-up fdz-icon"></i>
                <span class="fdz-txt">Click or drag to upload</span>
                <span class="fdz-sub">JPG, PNG, WEBP · Max 5 MB</span>
              </div>
              <input type="file" id="add_image" name="image" accept="image/*"
                     onchange="previewFile(this,'addPreview','addDropZone','addDropInner')"
                     style="display:none"/>
            </div>
            <img id="addPreview" class="upload-preview" style="display:none" alt="Preview"/>
            <button type="button" id="addClearImg" class="btn-clear-img" style="display:none"
                    onclick="clearImage('add_image','addPreview','addDropZone','addDropInner','addClearImg')">
              <i class="fas fa-xmark"></i> Remove photo
            </button>
          </div>
        </div>

      </div><!-- /form-grid -->

      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('addModal')">
          <i class="fas fa-xmark"></i> Cancel
        </button>
        <button type="submit" class="btn-submit btn-green">
          <i class="fas fa-floppy-disk"></i> Save Dish
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ═══ EDIT MODAL ═══════════════════════════════════ -->
<div class="modal-overlay" id="editModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
  <div class="modal modal-form-type">

    <div class="modal-header mh-blue">
      <div class="mh-left">
        <div class="mh-icon"><i class="fas fa-pen-to-square"></i></div>
        <div>
          <h2 id="editModalTitle">Edit Dish</h2>
          <p>Update the information below</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('editModal')" aria-label="Close">
        <i class="fas fa-xmark"></i>
      </button>
    </div>

    <div class="modal-loading" id="editLoading">
      <div class="spinner"></div>
      <span>Loading dish data…</span>
    </div>

    <form action="update.php" method="POST" enctype="multipart/form-data"
          class="modal-form" id="editForm" style="display:none" autocomplete="off">
      <input type="hidden" name="id" id="edit_id"/>

      <div class="form-grid">
        <div class="form-col">
          <div class="field-group">
            <label for="edit_dishes">
              <i class="fas fa-bowl-food"></i> Dish Name <span class="req">*</span>
            </label>
            <input type="text" id="edit_dishes" name="dishes" required/>
          </div>
          <div class="field-group">
            <label for="edit_category">
              <i class="fas fa-tag"></i> Category <span class="req">*</span>
            </label>
            <select id="edit_category" name="category" required>
              <option value="">— Choose a category —</option>
              <option>Main Course</option>
              <option>Appetizer</option>
              <option>Dessert</option>
              <option>Beverage</option>
              <option>Snack</option>
              <option>Soup</option>
              <option>Salad</option>
              <option>Pasta</option>
              <option>Seafood</option>
              <option>Grilled</option>
              <option>Street Food</option>
              <option>Breakfast</option>
              <option>Other</option>
            </select>
          </div>
          <div class="field-row">
            <div class="field-group">
              <label for="edit_price">
                <i class="fas fa-peso-sign"></i> Price <span class="req">*</span>
              </label>
              <input type="number" id="edit_price" name="price" step="0.01" min="0" required/>
            </div>
            <div class="field-group">
              <label for="edit_stock">
                <i class="fas fa-boxes-stacked"></i> Stock <span class="req">*</span>
              </label>
              <input type="number" id="edit_stock" name="stock" min="0" required/>
            </div>
          </div>
          <div class="field-group">
            <label for="edit_exp">
              <i class="fas fa-calendar-day"></i> Expiration Date <span class="req">*</span>
            </label>
            <input type="date" id="edit_exp" name="expiration_date" required/>
          </div>
        </div>

        <div class="form-col">
          <div class="field-group">
            <label><i class="fas fa-camera"></i> Dish Photo <small>(blank = keep current)</small></label>
            <div class="file-drop-zone" id="editDropZone"
                 onclick="document.getElementById('edit_image').click()"
                 ondragover="handleDragOver(event)" ondrop="handleDrop(event,'edit_image','editPreview','editDropZone')">
              <div class="fdz-inner" id="editDropInner">
                <i class="fas fa-cloud-arrow-up fdz-icon"></i>
                <span class="fdz-txt">Click or drag to replace photo</span>
                <span class="fdz-sub">JPG, PNG, WEBP · Max 5 MB</span>
              </div>
              <input type="file" id="edit_image" name="image" accept="image/*"
                     onchange="previewFile(this,'editPreview','editDropZone','editDropInner')"
                     style="display:none"/>
            </div>
            <img id="editPreview" class="upload-preview" alt="Current photo"/>
            <button type="button" id="editClearImg" class="btn-clear-img" style="display:none"
                    onclick="clearImage('edit_image','editPreview','editDropZone','editDropInner','editClearImg')">
              <i class="fas fa-xmark"></i> Remove new photo
            </button>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('editModal')">
          <i class="fas fa-xmark"></i> Cancel
        </button>
        <button type="submit" class="btn-submit btn-blue">
          <i class="fas fa-floppy-disk"></i> Update Dish
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ═══ VIEW MODAL ═══════════════════════════════════ -->
<div class="modal-overlay" id="viewModal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
  <div class="modal modal-view-type">

    <div class="modal-header mh-dark">
      <div class="mh-left">
        <div class="mh-icon"><i class="fas fa-eye"></i></div>
        <div>
          <h2 id="viewModalTitle">Dish Details</h2>
          <p>Full information for this item</p>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal('viewModal')" aria-label="Close">
        <i class="fas fa-xmark"></i>
      </button>
    </div>

    <div class="modal-loading" id="viewLoading">
      <div class="spinner"></div>
      <span>Loading…</span>
    </div>

    <div class="view-body" id="viewBody" style="display:none">
      <div class="view-img-wrap">
        <img id="viewImg" src="" alt="" class="view-dish-img"/>
        <div class="view-img-ph" id="viewImgPh">🍴</div>
      </div>
      <div class="view-details">
        <div class="view-dish-name" id="viewName"></div>
        <div class="view-fields">
          <div class="vf-row">
            <span class="vf-label"><i class="fas fa-tag"></i> Category</span>
            <span class="vf-val" id="viewCategory"></span>
          </div>
          <div class="vf-row">
            <span class="vf-label"><i class="fas fa-peso-sign"></i> Price</span>
            <span class="vf-val vf-price" id="viewPrice"></span>
          </div>
          <div class="vf-row">
            <span class="vf-label"><i class="fas fa-calendar-day"></i> Expiration</span>
            <span class="vf-val" id="viewExp"></span>
          </div>
          <div class="vf-row">
            <span class="vf-label"><i class="fas fa-boxes-stacked"></i> Stock</span>
            <span class="vf-val" id="viewStock"></span>
          </div>
        </div>
        <div class="view-actions">
          <button class="btn-submit btn-blue" id="viewEditBtn">
            <i class="fas fa-pen"></i> Edit This Dish
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ═══ PHOTO LIGHTBOX ════════════════════════════════ -->
<div class="modal-overlay" id="photoModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this) closeModal('photoModal')">
  <div class="modal modal-photo-type">
    <button class="modal-close photo-close" onclick="closeModal('photoModal')">
      <i class="fas fa-xmark"></i>
    </button>
    <img id="photoModalImg" src="" alt="" class="photo-modal-img"/>
    <div id="photoModalCaption" class="photo-caption"></div>
  </div>
</div>

<!-- ═══ DELETE CONFIRM MODAL ═════════════════════════ -->
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true">
  <div class="modal modal-confirm-type">
    <div class="confirm-art">🗑️</div>
    <h2 class="confirm-title">Delete Dish?</h2>
    <p class="confirm-msg">You are about to delete <strong id="deleteTargetName"></strong>.</p>
    <p class="confirm-sub">This action cannot be undone.</p>
    <div class="confirm-btns">
      <button class="btn-cancel" onclick="closeModal('deleteModal')">
        <i class="fas fa-arrow-left"></i> Keep It
      </button>
      <a id="deleteConfirmLink" href="#" class="btn-submit btn-red">
        <i class="fas fa-trash"></i> Yes, Delete
      </a>
    </div>
  </div>
</div>

<!-- ═══ DELETE ALL CONFIRM MODAL ══════════════════════ -->
<div class="modal-overlay" id="deleteAllModal" role="dialog" aria-modal="true">
  <div class="modal modal-confirm-type">
    <div class="confirm-art">⚠️</div>
    <h2 class="confirm-title">Delete Everything?</h2>
    <p class="confirm-msg">This will permanently erase <strong>all <?= $totalDishes ?> dish records</strong> from the database.</p>
    <p class="confirm-sub">All photos and data will be wiped. This cannot be undone.</p>
    <div class="confirm-btns">
      <button class="btn-cancel" onclick="closeModal('deleteAllModal')">
        <i class="fas fa-arrow-left"></i> Cancel
      </button>
      <a href="delete_all.php" class="btn-submit btn-red">
        <i class="fas fa-fire"></i> Wipe All Records
      </a>
    </div>
  </div>
</div>

<script src="asset\js\script.js"></script>
</body>
</html>