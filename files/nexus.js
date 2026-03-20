/* ═══════════════════════════════════════════════
   NEXUS — Sistema de Gestão de Produtos
   @author  Jaraujo
   @version 1.0.0 © 2024
   ═══════════════════════════════════════════════ */

'use strict';

/* ── CSRF Token for all AJAX ── */
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

/* ────────────────────────────────
   SIDEBAR
   ──────────────────────────────── */
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('overlay');
const sbToggle = document.getElementById('sidebarToggle');
const sbClose  = document.getElementById('sidebarClose');

function openSidebar() {
  sidebar?.classList.add('open');
  overlay?.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeSidebar() {
  sidebar?.classList.remove('open');
  overlay?.classList.remove('show');
  document.body.style.overflow = '';
}

sbToggle?.addEventListener('click', () => {
  if (window.innerWidth < 992) {
    sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
  }
});

sbClose?.addEventListener('click', closeSidebar);
overlay?.addEventListener('click', () => { closeSidebar(); closeNotifPanel(); });

/* ────────────────────────────────
   LIVE SEARCH (AJAX)
   ──────────────────────────────── */
const searchInput   = document.getElementById('liveSearch');
const searchResults = document.getElementById('searchResults');
let   searchTimer;

function renderSearchResults(results) {
  if (!results.length) {
    searchResults.innerHTML = `<div class="nx-search-empty"><i class="bi bi-search" style="font-size:24px;display:block;margin-bottom:8px"></i>Nenhum produto encontrado</div>`;
  } else {
    searchResults.innerHTML = results.map(p => `
      <a href="${p.url}" class="nx-search-item">
        <div class="nx-search-thumb">
          ${p.image ? `<img src="${p.image}" alt="${p.name}">` : '📦'}
        </div>
        <div style="flex:1;min-width:0">
          <div class="nx-search-item-name">${highlight(p.name, searchInput.value)}</div>
          <div class="nx-search-item-meta">${p.category} · SKU: ${p.sku || '—'} · ${p.stock} un.</div>
        </div>
        <div class="nx-search-item-price">${p.price}</div>
      </a>`).join('');
  }
  searchResults.classList.add('show');
}

function highlight(text, term) {
  if (!term) return text;
  const re = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  return text.replace(re, '<mark style="background:rgba(34,211,238,.25);color:#22d3ee;border-radius:2px;padding:0 2px">$1</mark>');
}

searchInput?.addEventListener('input', () => {
  clearTimeout(searchTimer);
  const q = searchInput.value.trim();

  if (!q) { searchResults.classList.remove('show'); return; }

  searchTimer = setTimeout(async () => {
    try {
      const res  = await fetch(`/products/api/search?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();
      renderSearchResults(data);
    } catch (e) {
      console.error('Search error:', e);
    }
  }, 300);
});

document.addEventListener('click', e => {
  if (!e.target.closest('#searchWrap')) {
    searchResults?.classList.remove('show');
  }
});

/* CMD+K → focus search */
document.addEventListener('keydown', e => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    searchInput?.focus();
  }
  if (e.key === 'Escape') {
    searchResults?.classList.remove('show');
    searchInput?.blur();
    closeNotifPanel();
  }
});

/* ────────────────────────────────
   TOASTS
   ──────────────────────────────── */
function showToast({ type = 'success', title = '', message = '' }) {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const icons = {
    success: { icon: 'check-circle-fill', color: '#4ade80' },
    warning: { icon: 'exclamation-triangle-fill', color: '#fb923c' },
    error:   { icon: 'x-circle-fill', color: '#f87171' },
    info:    { icon: 'info-circle-fill', color: '#22d3ee' },
  };

  const { icon, color } = icons[type] || icons.info;

  const el = document.createElement('div');
  el.className = `nx-toast ${type}`;
  el.innerHTML = `
    <span class="nx-toast-icon" style="color:${color}"><i class="bi bi-${icon}"></i></span>
    <div style="flex:1">
      <div class="nx-toast-title">${title}</div>
      ${message ? `<div class="nx-toast-msg">${message}</div>` : ''}
    </div>
    <button class="nx-toast-close" onclick="this.closest('.nx-toast').remove()">
      <i class="bi bi-x"></i>
    </button>`;

  container.appendChild(el);
  requestAnimationFrame(() => { requestAnimationFrame(() => el.classList.add('show')); });
  setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400); }, 4500);
}

window.showToast = showToast;

/* ────────────────────────────────
   NOTIFICATIONS
   ──────────────────────────────── */
const notifBtn   = document.getElementById('notifBtn');
const notifPanel = document.getElementById('notifPanel');
const notifClose = document.getElementById('notifClose');

function openNotifPanel()  { notifPanel?.classList.add('open'); overlay?.classList.add('show'); }
function closeNotifPanel() { notifPanel?.classList.remove('open'); overlay?.classList.remove('show'); }

notifBtn?.addEventListener('click',  openNotifPanel);
notifClose?.addEventListener('click', closeNotifPanel);

/* ────────────────────────────────
   DELETE CONFIRMATION
   ──────────────────────────────── */
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-confirm-delete]');
  if (!btn) return;
  e.preventDefault();

  const name = btn.dataset.confirmDelete;
  if (!confirm(`Tem a certeza que quer eliminar "${name}"?\n\nEsta ação não pode ser desfeita.`)) return;

  const form = btn.closest('form') || document.getElementById(btn.dataset.formId);
  form?.submit();
});

/* ────────────────────────────────
   TOGGLE FEATURED (AJAX)
   ──────────────────────────────── */
document.addEventListener('click', async e => {
  const btn = e.target.closest('[data-toggle-featured]');
  if (!btn) return;

  const productId = btn.dataset.toggleFeatured;
  const url = `/products/${productId}/featured`;

  try {
    const res  = await fetch(url, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    const data = await res.json();

    const icon = btn.querySelector('i');
    if (data.featured) {
      icon?.classList.replace('bi-star', 'bi-star-fill');
      btn.style.color = '#fbbf24';
    } else {
      icon?.classList.replace('bi-star-fill', 'bi-star');
      btn.style.color = '';
    }

    showToast({ type: 'success', title: data.message });
  } catch (err) {
    showToast({ type: 'error', title: 'Erro', message: 'Não foi possível atualizar o destaque.' });
  }
});

/* ────────────────────────────────
   IMAGE PREVIEW
   ──────────────────────────────── */
const imageInput   = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');

imageInput?.addEventListener('change', () => {
  const file = imageInput.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    if (imagePreview) imagePreview.src = e.target.result;
    document.getElementById('imagePreviewWrap')?.classList.remove('d-none');
  };
  reader.readAsDataURL(file);
});

/* ────────────────────────────────
   TAGS INPUT
   ──────────────────────────────── */
(function initTagsInput() {
  const wrap  = document.getElementById('tagsWrap');
  const input = document.getElementById('tagInput');
  const hidden= document.getElementById('tagsHidden');
  if (!wrap || !input) return;

  let tags = [];

  try { tags = JSON.parse(hidden?.value || '[]'); } catch {}
  tags.forEach(addTagEl);

  function addTag(val) {
    val = val.trim().toLowerCase();
    if (!val || tags.includes(val)) return;
    tags.push(val);
    addTagEl(val);
    syncHidden();
  }

  function addTagEl(val) {
    const el = document.createElement('span');
    el.className = 'nx-tag';
    el.innerHTML = `${val} <button type="button" onclick="this.parentElement.remove(); removeTag('${val}')" style="background:none;border:none;color:inherit;margin-left:4px;cursor:pointer;font-size:10px">✕</button>`;
    wrap.insertBefore(el, input);
  }

  window.removeTag = (val) => {
    tags = tags.filter(t => t !== val);
    syncHidden();
  };

  function syncHidden() {
    if (hidden) hidden.value = JSON.stringify(tags);
  }

  input.addEventListener('keydown', e => {
    if (['Enter', ',', 'Tab'].includes(e.key)) {
      e.preventDefault();
      addTag(input.value);
      input.value = '';
    }
    if (e.key === 'Backspace' && !input.value && tags.length) {
      const last = tags.pop();
      wrap.querySelector(`.nx-tag:last-of-type`)?.remove();
      syncHidden();
    }
  });
})();

/* ────────────────────────────────
   ANIMATE ON LOAD
   ──────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.nx-animate').forEach((el, i) => {
    el.style.animationDelay = (i * 0.05) + 's';
  });
});

/* ────────────────────────────────
   POLLED NOTIFICATIONS (demo: every 60s)
   ──────────────────────────────── */
function pollNotifications() {
  // In production: fetch('/api/notifications') and update badge
  console.log('[NEXUS] Polling notifications…');
}

setInterval(pollNotifications, 60000);
