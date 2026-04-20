/* ================================================================
   FoodVault — Food Management System
   script.js  ·  All interactive behaviour
   ================================================================ */

'use strict';

/* ════════════════════════════════════════════════════════════
   MODAL SYSTEM
════════════════════════════════════════════════════════════ */

function openModal(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.add('open');
  document.body.style.overflow = 'hidden';
  // Focus first input after animation
  setTimeout(function () {
    var first = el.querySelector('input:not([type=file]),select,textarea');
    if (first) first.focus();
  }, 350);
}

function closeModal(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('open');
  if (!document.querySelector('.modal-overlay.open')) {
    document.body.style.overflow = '';
  }
}

// Click backdrop to close
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    closeModal(e.target.id);
  }
});

// ESC to close
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
      closeModal(m.id);
    });
  }
});

/* ════════════════════════════════════════════════════════════
   ADD MODAL
════════════════════════════════════════════════════════════ */

function openAddModal() {
  // Reset the form
  var form = document.querySelector('#addModal .modal-form');
  if (form) form.reset();
  clearImage('add_image', 'addPreview', 'addDropZone', 'addDropInner', 'addClearImg');
  openModal('addModal');
}

/* ════════════════════════════════════════════════════════════
   EDIT MODAL
════════════════════════════════════════════════════════════ */

function openEditModal(id) {
  var loading = document.getElementById('editLoading');
  var form    = document.getElementById('editForm');

  // Show loading, hide form
  if (loading) loading.style.display = 'flex';
  if (form)    form.style.display    = 'none';

  openModal('editModal');

  fetch('edit.php?id=' + id)
    .then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function (d) {
      if (d.error) {
        closeModal('editModal');
        showFlash('Error: ' + d.error, 'danger');
        return;
      }

      document.getElementById('edit_id').value       = d.id          || '';
      document.getElementById('edit_dishes').value   = d.dishes      || '';
      document.getElementById('edit_price').value    = d.price       || '';
      document.getElementById('edit_stock').value    = d.stock       || '';
      document.getElementById('edit_exp').value      = d.expiration_date || '';

      // Category select
      var sel = document.getElementById('edit_category');
      if (sel) {
        for (var i = 0; i < sel.options.length; i++) {
          if (sel.options[i].text === d.category || sel.options[i].value === d.category) {
            sel.selectedIndex = i;
            break;
          }
        }
      }

      // Current image preview
      var prev = document.getElementById('editPreview');
      var fi   = document.getElementById('edit_image');
      if (fi) fi.value = '';

      if (d.image) {
        if (prev) { prev.src = d.image; prev.style.display = 'block'; }
        // Show the drop zone with smaller inner
        var inner = document.getElementById('editDropInner');
        if (inner) inner.style.padding = '10px';
      } else {
        if (prev) { prev.src = ''; prev.style.display = 'none'; }
        resetDropZone('editDropInner');
      }

      if (loading) loading.style.display = 'none';
      if (form)    form.style.display    = 'block';
    })
    .catch(function (err) {
      closeModal('editModal');
      console.error(err);
      showFlash('Could not load dish data. Is XAMPP running?', 'danger');
    });
}

/* ════════════════════════════════════════════════════════════
   VIEW MODAL
════════════════════════════════════════════════════════════ */

function openViewModal(id) {
  var loading = document.getElementById('viewLoading');
  var body    = document.getElementById('viewBody');

  if (loading) loading.style.display = 'flex';
  if (body)    body.style.display    = 'none';
  openModal('viewModal');

  fetch('edit.php?id=' + id)
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (d.error) { closeModal('viewModal'); return; }

      // Name
      var nameEl = document.getElementById('viewName');
      if (nameEl) nameEl.textContent = d.dishes || '';

      // Fields
      setText('viewCategory', d.category      || '—');
      setText('viewPrice',    d.price ? '₱' + parseFloat(d.price).toLocaleString('en-PH', {minimumFractionDigits:2}) : '—');
      setText('viewExp',      d.expiration_date || '—');
      setText('viewStock',    d.stock !== undefined ? d.stock + ' units' : '—');

      // Image
      var img  = document.getElementById('viewImg');
      var ph   = document.getElementById('viewImgPh');
      if (d.image) {
        if (img) { img.src = d.image; img.style.display = 'block'; }
        if (ph)  ph.style.display = 'none';
      } else {
        if (img) img.style.display = 'none';
        if (ph)  ph.style.display  = 'flex';
      }

      // Edit button inside view modal
      var editBtn = document.getElementById('viewEditBtn');
      if (editBtn) {
        editBtn.onclick = function () {
          closeModal('viewModal');
          setTimeout(function () { openEditModal(id); }, 200);
        };
      }

      if (loading) loading.style.display = 'none';
      if (body)    body.style.display    = 'flex';
    })
    .catch(function (err) {
      closeModal('viewModal');
      console.error(err);
    });
}

function setText(id, val) {
  var el = document.getElementById(id);
  if (el) el.textContent = val;
}

/* ════════════════════════════════════════════════════════════
   PHOTO LIGHTBOX
════════════════════════════════════════════════════════════ */

function openPhotoModal(src, caption) {
  var img = document.getElementById('photoModalImg');
  var cap = document.getElementById('photoModalCaption');
  if (img) img.src = src;
  if (cap) cap.textContent = caption || '';
  openModal('photoModal');
}

/* ════════════════════════════════════════════════════════════
   DELETE CONFIRM
════════════════════════════════════════════════════════════ */

function openDeleteModal(id, name) {
  var nameEl = document.getElementById('deleteTargetName');
  var link   = document.getElementById('deleteConfirmLink');
  if (nameEl) nameEl.textContent = '"' + name + '"';
  if (link)   link.href = 'delete.php?id=' + id;
  openModal('deleteModal');
}

function openDeleteAllModal() {
  openModal('deleteAllModal');
}

/* ════════════════════════════════════════════════════════════
   IMAGE PREVIEW + DRAG & DROP
════════════════════════════════════════════════════════════ */

function previewFile(input, previewId, dropZoneId, dropInnerId) {
  if (!input.files || !input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function (e) {
    var prev = document.getElementById(previewId);
    if (prev) { prev.src = e.target.result; prev.style.display = 'block'; }

    var inner = document.getElementById(dropInnerId);
    if (inner) {
      inner.innerHTML =
        '<i class="fas fa-check-circle fdz-icon" style="color:var(--green)"></i>' +
        '<span class="fdz-txt">Photo selected!</span>' +
        '<span class="fdz-sub">' + input.files[0].name + '</span>';
      inner.style.padding = '12px';
    }

    // Show clear button (auto-detect)
    var clearBtnId = dropZoneId.replace('DropZone', 'ClearImg')
                                .replace('Drop', 'Clear');
    // Try both naming conventions
    var clearBtn = document.getElementById(dropZoneId.replace('DropZone','').toLowerCase() + 'ClearImg')
                || document.getElementById('addClearImg')
                || document.getElementById('editClearImg');
    // More reliable: find nearest clear btn by proximity
    var zone = document.getElementById(dropZoneId);
    if (zone) {
      var parent = zone.closest('.field-group');
      if (parent) {
        var cb = parent.querySelector('.btn-clear-img');
        if (cb) cb.style.display = 'inline-flex';
      }
    }
  };
  reader.readAsDataURL(input.files[0]);
}

function clearImage(inputId, previewId, dropZoneId, dropInnerId, clearBtnId) {
  var input = document.getElementById(inputId);
  var prev  = document.getElementById(previewId);
  var cb    = document.getElementById(clearBtnId);
  if (input) input.value = '';
  if (prev)  { prev.src = ''; prev.style.display = 'none'; }
  if (cb)    cb.style.display = 'none';
  resetDropZone(dropInnerId);
}

function resetDropZone(innerId) {
  var inner = document.getElementById(innerId);
  if (inner) {
    inner.innerHTML =
      '<i class="fas fa-cloud-arrow-up fdz-icon"></i>' +
      '<span class="fdz-txt">Click or drag to upload</span>' +
      '<span class="fdz-sub">JPG, PNG, WEBP · Max 5 MB</span>';
    inner.style.padding = '';
  }
}

function handleDragOver(e) {
  e.preventDefault();
  var zone = e.currentTarget;
  zone.classList.add('drag-over');
}

function handleDrop(e, inputId, previewId, dropZoneId) {
  e.preventDefault();
  var zone = document.getElementById(dropZoneId);
  if (zone) zone.classList.remove('drag-over');

  var files = e.dataTransfer.files;
  if (!files.length) return;

  var input = document.getElementById(inputId);
  if (!input) return;

  // Assign files via DataTransfer
  try {
    var dt = new DataTransfer();
    dt.items.add(files[0]);
    input.files = dt.files;
  } catch (ex) { /* Fallback: old browsers */ }

  var inner = dropZoneId.replace('DropZone', 'DropInner')
              .replace('Drop', 'DropInner');
  previewFile(input, previewId, dropZoneId, inner);
}

/* ════════════════════════════════════════════════════════════
   REAL-TIME SEARCH
════════════════════════════════════════════════════════════ */

function initSearch() {
  var input    = document.getElementById('searchInput');
  var clearBtn = document.getElementById('clearSearch');
  var noRes    = document.getElementById('noResults');
  var qSpan    = document.getElementById('noResultsQuery');
  var countLbl = document.getElementById('countLabel');
  var rows     = document.querySelectorAll('.dish-row');

  if (!input) return;

  function doFilter() {
    var q       = input.value.trim().toLowerCase();
    var visible = 0;

    rows.forEach(function (row) {
      var name = row.getAttribute('data-name') || '';
      var cat  = row.getAttribute('data-cat')  || '';
      var show = !q || name.includes(q) || cat.includes(q);
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    // Clear button visibility
    if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';

    // No results msg
    if (noRes) {
      if (visible === 0 && q) {
        if (qSpan) qSpan.textContent = q;
        noRes.style.display = 'block';
      } else {
        noRes.style.display = 'none';
      }
    }

    // Count label
    if (countLbl) {
      countLbl.textContent = 'Showing ' + visible + ' dish' + (visible !== 1 ? 'es' : '') + (q ? ' for "' + q + '"' : '');
    }
  }

  input.addEventListener('input', doFilter);

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      input.value = '';
      doFilter();
      input.focus();
    });
  }
}

/* ════════════════════════════════════════════════════════════
   COUNT-UP ANIMATION
════════════════════════════════════════════════════════════ */

function countUp(el) {
  var target  = parseInt(el.getAttribute('data-count'), 10);
  if (isNaN(target) || target < 1) return;
  var start    = 0;
  var duration = 1000;
  var startTs  = null;

  function step(ts) {
    if (!startTs) startTs = ts;
    var progress = Math.min((ts - startTs) / duration, 1);
    var ease     = 1 - Math.pow(1 - progress, 3); // ease-out cubic
    el.textContent = Math.round(ease * target).toLocaleString();
    if (progress < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

/* ════════════════════════════════════════════════════════════
   FLASH MESSAGE (programmatic toast)
════════════════════════════════════════════════════════════ */

function showFlash(msg, type) {
  type = type || 'info';
  var icons = { success:'check-circle', info:'circle-info', warning:'triangle-exclamation', danger:'circle-xmark' };
  var t = document.createElement('div');
  t.className = 'toast toast-' + type;
  t.innerHTML =
    '<span class="toast-icon"><i class="fas fa-' + (icons[type]||'bell') + '"></i></span>' +
    '<span class="toast-msg">' + msg + '</span>' +
    '<button class="toast-x" onclick="this.closest(\'.toast\').remove()"><i class="fas fa-xmark"></i></button>';

  var main = document.querySelector('.main');
  if (main) main.prepend(t);
  setTimeout(function () { if (t.parentNode) t.remove(); }, 4500);
}

/* ════════════════════════════════════════════════════════════
   AUTO-DISMISS EXISTING TOAST
════════════════════════════════════════════════════════════ */

function initToast() {
  var toast = document.getElementById('toast');
  if (!toast) return;
  setTimeout(function () {
    toast.style.transition = 'opacity .4s, transform .4s';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    setTimeout(function () { if (toast.parentNode) toast.remove(); }, 420);
  }, 4500);
}

/* ════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function () {
  // Count-up on stat numbers
  document.querySelectorAll('.stat-num[data-count]').forEach(countUp);

  // Real-time search
  initSearch();

  // Auto-dismiss toast
  initToast();

  // Drag-leave reset on file drop zones
  document.querySelectorAll('.file-drop-zone').forEach(function (zone) {
    zone.addEventListener('dragleave', function (e) {
      if (!zone.contains(e.relatedTarget)) {
        zone.classList.remove('drag-over');
      }
    });
  });
});