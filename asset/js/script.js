/* ============================================================
   FoodVault — Dark Glassmorphism Theme
   script.js
   ============================================================ */
'use strict';

/* ── Modal system ─────────────────────── */
function openModal(id){
  var el=document.getElementById(id);
  if(!el)return;
  el.classList.add('open');
  document.body.style.overflow='hidden';
}
function closeModal(id){
  var el=document.getElementById(id);
  if(!el)return;
  el.classList.remove('open');
  if(!document.querySelector('.modal-backdrop.open'))
    document.body.style.overflow='';
}
document.addEventListener('click',function(e){
  if(e.target.classList.contains('modal-backdrop'))
    closeModal(e.target.id);
});
document.addEventListener('keydown',function(e){
  if(e.key==='Escape')
    document.querySelectorAll('.modal-backdrop.open')
      .forEach(function(m){closeModal(m.id);});
});

/* ── Sidebar ──────────────────────────── */
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
}
document.addEventListener('click',function(e){
  var sb=document.getElementById('sidebar');
  var hb=document.querySelector('.hamburger');
  if(sb&&hb&&!sb.contains(e.target)&&!hb.contains(e.target)&&window.innerWidth<=900)
    sb.classList.remove('open');
});

/* ── Add Modal ────────────────────────── */
function openAddModal(){
  var form=document.querySelector('#addModal form');
  if(form)form.reset();
  resetDZ('addFile','addPrev','addDZ','addClear');
  openModal('addModal');
}

/* ── Edit Modal ───────────────────────── */
function openEditModal(id){
  var load=document.getElementById('editLoad');
  var form=document.getElementById('editForm');
  if(load)load.style.display='flex';
  if(form)form.style.display='none';
  openModal('editModal');

  fetch('edit.php?id='+id)
    .then(function(r){
      return r.text().then(function(txt){
        try{return JSON.parse(txt);}
        catch(e){throw new Error('Server error: '+txt.substring(0,100));}
      });
    })
    .then(function(d){
      if(d.error){closeModal('editModal');flash('\u274C '+d.error,'danger');return;}
      document.getElementById('eId').value     = d.id||'';
      document.getElementById('eDishes').value = d.dishes||'';
      document.getElementById('ePrice').value  = d.price||'';
      document.getElementById('eStock').value  = d.stock||'';
      document.getElementById('eExp').value    = d.expiration_date||'';
      var sel=document.getElementById('eCategory');
      if(sel){for(var i=0;i<sel.options.length;i++){
        if(sel.options[i].text===d.category||sel.options[i].value===d.category){sel.selectedIndex=i;break;}
      }}
      var prev=document.getElementById('editPrev');
      var fi=document.getElementById('editFile');
      if(fi)fi.value='';
      if(d.image){if(prev){prev.src=d.image;prev.style.display='block';}}
      else{if(prev){prev.src='';prev.style.display='none';}}
      var clear=document.getElementById('editClear');
      if(clear)clear.style.display='none';
      if(load)load.style.display='none';
      if(form)form.style.display='block';
    })
    .catch(function(err){
      closeModal('editModal');
      console.error(err);
      flash('\u274C '+err.message,'danger');
    });
}

/* ── View Modal ───────────────────────── */
function openViewModal(id){
  var load=document.getElementById('viewLoad');
  var cont=document.getElementById('viewContent');
  if(load)load.style.display='flex';
  if(cont)cont.style.display='none';
  openModal('viewModal');

  fetch('edit.php?id='+id)
    .then(function(r){
      return r.text().then(function(txt){
        try{return JSON.parse(txt);}
        catch(e){throw new Error('Server error: '+txt.substring(0,100));}
      });
    })
    .then(function(d){
      if(d.error){closeModal('viewModal');flash('\u274C '+d.error,'danger');return;}
      setTxt('vName',d.dishes||'—');
      setTxt('vCat', d.category||'—');
      setTxt('vPrice',d.price?'₱'+parseFloat(d.price).toLocaleString('en-PH',{minimumFractionDigits:2}):'—');
      setTxt('vExp', d.expiration_date||'—');
      setTxt('vStock',(d.stock!==undefined?d.stock+' units':'—'));
      var img=document.getElementById('vImg');
      var ph=document.getElementById('vImgPh');
      if(d.image){if(img){img.src=d.image;img.style.display='block';}if(ph)ph.style.display='none';}
      else{if(img)img.style.display='none';if(ph)ph.style.display='flex';}
      var eb=document.getElementById('vEditBtn');
      if(eb)eb.onclick=function(){closeModal('viewModal');setTimeout(function(){openEditModal(id);},200);};
      if(load)load.style.display='none';
      if(cont)cont.style.display='flex';
    })
    .catch(function(err){closeModal('viewModal');flash('\u274C '+(err.message||'Could not load data'),'danger');});
}
function setTxt(id,val){var el=document.getElementById(id);if(el)el.textContent=val;}

/* ── Delete ───────────────────────────── */
function openDelModal(id,name){
  setTxt('delName','"'+name+'"');
  document.getElementById('delLink').href='delete.php?id='+id;
  openModal('delModal');
}
function openDelAllModal(){openModal('delAllModal');}

/* ── Lightbox ─────────────────────────── */
function openLightbox(src,caption){
  var img=document.getElementById('lbImg');
  var cap=document.getElementById('lbCap');
  if(img)img.src=src;
  if(cap)cap.textContent=caption||'';
  openModal('lbModal');
}

/* ── File / image handling ────────────── */
function fileChosen(input,prevId,zone){
  if(!input.files||!input.files[0])return;
  var reader=new FileReader();
  reader.onload=function(e){
    var prev=document.getElementById(prevId);
    if(prev){prev.src=e.target.result;prev.style.display='block';}
    if(zone){
      zone.style.padding='8px';
      var icon=zone.querySelector('.dz-icon');
      var txt =zone.querySelector('.dz-txt');
      var sub =zone.querySelector('.dz-sub');
      if(icon){icon.className='fas fa-check-circle dz-icon';icon.style.color='var(--green)';}
      if(txt)txt.textContent=input.files[0].name;
      if(sub)sub.textContent='Photo ready ✓';
    }
    // Show clear button
    var clearId=input.id==='addFile'?'addClear':'editClear';
    var cb=document.getElementById(clearId);
    if(cb)cb.style.display='inline-flex';
  };
  reader.readAsDataURL(input.files[0]);
}
function clearFile(inputId,prevId,dzId,clearId){
  var input=document.getElementById(inputId);
  var prev=document.getElementById(prevId);
  var cb=document.getElementById(clearId);
  if(input)input.value='';
  if(prev){prev.src='';prev.style.display='none';}
  if(cb)cb.style.display='none';
  resetDZbyId(dzId);
}
function resetDZ(inputId,prevId,dzId,clearId){
  var input=document.getElementById(inputId);
  var prev=document.getElementById(prevId);
  var cb=document.getElementById(clearId);
  if(input)input.value='';
  if(prev&&inputId==='addFile'){prev.src='';prev.style.display='none';}
  if(cb)cb.style.display='none';
  resetDZbyId(dzId);
}
function resetDZbyId(dzId){
  var dz=document.getElementById(dzId);
  if(!dz)return;
  dz.style.padding='';
  var icon=dz.querySelector('.dz-icon');
  var txt =dz.querySelector('.dz-txt');
  var sub =dz.querySelector('.dz-sub');
  if(icon){icon.className='fas fa-cloud-arrow-up dz-icon';icon.style.color='';}
  if(txt)txt.textContent='Click or drag & drop';
  if(sub)sub.textContent='PNG, JPG, WEBP · Max 5MB';
}
function dzOver(e,el){e.preventDefault();el.classList.add('dz-over');}
function dzLeave(el){el.classList.remove('dz-over');}
function dzDrop(e,zone,inputId,prevId){
  e.preventDefault();zone.classList.remove('dz-over');
  var files=e.dataTransfer.files;if(!files.length)return;
  var input=document.getElementById(inputId);if(!input)return;
  try{var dt=new DataTransfer();dt.items.add(files[0]);input.files=dt.files;}catch(ex){}
  fileChosen(input,prevId,zone);
}

/* ── Search ───────────────────────────── */
function clearSearch(){
  var si=document.getElementById('searchInput');
  if(si){si.value='';si.dispatchEvent(new Event('input'));}
  var cb=document.getElementById('clearSearch');
  if(cb)cb.style.display='none';
}
function initSearch(){
  var si=document.getElementById('searchInput');
  var cb=document.getElementById('clearSearch');
  var nr=document.getElementById('noResults');
  var cl=document.getElementById('countLbl');
  if(!si)return;
  si.addEventListener('input',function(){
    var q=this.value.trim().toLowerCase();
    var rows=document.querySelectorAll('.dish-row');
    var cards=document.querySelectorAll('.food-card');
    var vis=0;
    function show(el){
      var n=(el.getAttribute('data-name')||'').toLowerCase();
      var c=(el.getAttribute('data-cat')||'').toLowerCase();
      var ok=!q||n.includes(q)||c.includes(q);
      el.style.display=ok?'':'none';
      if(ok)vis++;
    }
    rows.forEach(show);cards.forEach(show);
    if(cb)cb.style.display=q?'block':'none';
    if(nr)nr.style.display=(vis===0&&q)?'block':'none';
    if(cl)cl.textContent='Showing '+vis+' dish'+(vis!==1?'es':'')+(q?' for "'+q+'"':'');
  });
}

/* ── View switcher ────────────────────── */
function switchView(mode){
  var tv=document.getElementById('tableView');
  var gv=document.getElementById('gridView');
  var bt=document.getElementById('vsTable');
  var bg=document.getElementById('vsGrid');
  if(mode==='table'){
    tv.style.display='';gv.style.display='none';
    bt.classList.add('active');bg.classList.remove('active');
  } else {
    tv.style.display='none';gv.style.display='';
    bg.classList.add('active');bt.classList.remove('active');
  }
  localStorage.setItem('fv_view',mode);
}

/* ── Count-up animation ───────────────── */
function countUp(el){
  var target=parseInt(el.getAttribute('data-target'),10);
  if(isNaN(target)||target<1)return;
  var start=null,dur=1000;
  function step(ts){
    if(!start)start=ts;
    var p=Math.min((ts-start)/dur,1);
    var ease=1-Math.pow(1-p,3);
    el.textContent=Math.round(ease*target).toLocaleString();
    if(p<1)requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

/* ── Flash toast (programmatic) ──────── */
function flash(msg,type){
  type=type||'info';
  var icons={success:'circle-check',info:'circle-info',warning:'triangle-exclamation',danger:'circle-xmark'};
  var t=document.createElement('div');
  t.className='toast toast-'+type;
  t.innerHTML='<i class="fas fa-'+icons[type]+' toast-icon"></i><span>'+msg+'</span>'
    +'<button onclick="this.parentElement.remove()"><i class="fas fa-xmark"></i></button>';
  var pc=document.querySelector('.page-content');
  if(pc)pc.prepend(t);
  setTimeout(function(){if(t.parentNode){t.style.transition='opacity .4s';t.style.opacity='0';setTimeout(function(){t.remove();},420);}},4500);
}

/* ── Init ─────────────────────────────── */
document.addEventListener('DOMContentLoaded',function(){
  // Restore view
  var savedView=localStorage.getItem('fv_view');
  if(savedView==='grid')switchView('grid');

  // Count-up
  document.querySelectorAll('.sg-num[data-target]').forEach(countUp);

  // Search
  initSearch();

  // Auto-dismiss toast
  var toast=document.getElementById('toastEl');
  if(toast){
    setTimeout(function(){
      toast.style.transition='opacity .4s,transform .4s';
      toast.style.opacity='0';
      toast.style.transform='translateY(-10px)';
      setTimeout(function(){if(toast.parentNode)toast.remove();},420);
    },4500);
  }
});