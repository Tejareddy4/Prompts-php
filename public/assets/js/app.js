const BASE = window.BASE_PATH || '';

async function postData(url, data) {
  const fd = new FormData();
  Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  fd.append('_csrf', window.CSRF_TOKEN);
  const res = await fetch(BASE + url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  return res.json();
}

// Dropdown menus (replaces Bootstrap JS) — toggle .show, close on outside click / Esc
document.addEventListener('click', (e) => {
  const toggle = e.target.closest('[data-bs-toggle="dropdown"]');
  const openMenus = document.querySelectorAll('.dropdown-menu.show');
  if (toggle) {
    e.preventDefault();
    const menu = toggle.closest('.dropdown')?.querySelector('.dropdown-menu');
    const wasOpen = menu?.classList.contains('show');
    openMenus.forEach(m => m.classList.remove('show'));
    if (menu && !wasOpen) menu.classList.add('show');
  } else if (!e.target.closest('.dropdown-menu')) {
    openMenus.forEach(m => m.classList.remove('show'));
  }
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
});

// One-click copy directly from a prompt card (no navigation)
document.addEventListener('click', async (e) => {
  const copyBtn = e.target.closest('.js-card-copy');
  if (!copyBtn) return;
  e.preventDefault();
  e.stopPropagation();

  const text = copyBtn.dataset.copy || '';
  try {
    if (navigator.clipboard) await navigator.clipboard.writeText(text);
    else {
      const ta = document.createElement('textarea');
      ta.value = text; ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
      document.body.appendChild(ta); ta.focus(); ta.select();
      document.execCommand('copy'); document.body.removeChild(ta);
    }
  } catch (_) {}

  const orig = copyBtn.innerHTML;
  copyBtn.classList.add('is-copied');
  copyBtn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
  setTimeout(() => { copyBtn.classList.remove('is-copied'); copyBtn.innerHTML = orig; }, 1800);

  const id = copyBtn.dataset.id;
  if (id) { try { await postData('/prompts/copy', { prompt_id: id }); } catch (_) {} }
});

// Like / Save / Copy — delegated from data-prompt-id wrapper
document.addEventListener('click', async (e) => {
  const wrapper = e.target.closest('[data-prompt-id]');
  if (!wrapper) return;
  const id = wrapper.dataset.promptId;
  if (!id || id === 'not-counted') return;

  if (e.target.closest('.js-like')) {
    const btn = wrapper.querySelector('.js-like');
    const res = await postData('/prompts/like', { prompt_id: id });
    btn.querySelector('.count').textContent = res.count;
    const on = res.liked;
    btn.className = btn.className.replace(/btn-danger-\w+/, '').trim();
    btn.classList.add(on ? 'btn-danger-fill' : 'btn-danger-outline');
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    const icon = btn.querySelector('i');
    if (icon) { icon.className = on ? 'bi bi-heart-fill' : 'bi bi-heart'; }
    const lbl = btn.querySelector('span:not(.count)');
    if (lbl) lbl.textContent = on ? 'Liked' : 'Like';
  }

  if (e.target.closest('.js-save')) {
    const btn = wrapper.querySelector('.js-save');
    const res = await postData('/prompts/save', { prompt_id: id });
    btn.querySelector('.count').textContent = res.count;
    const on = res.saved;
    btn.className = btn.className.replace(/btn-save-\w+/, '').trim();
    btn.classList.add(on ? 'btn-save-fill' : 'btn-save-outline');
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    const icon = btn.querySelector('i');
    if (icon) { icon.className = on ? 'bi bi-bookmark-fill' : 'bi bi-bookmark'; }
    const lbl = btn.querySelector('span:not(.count)');
    if (lbl) lbl.textContent = on ? 'Saved' : 'Save';
  }

  if (e.target.closest('.js-copy')) {
    const txt = document.getElementById('prompt-text');
    if (txt) await navigator.clipboard.writeText(txt.innerText);
    const res = await postData('/prompts/copy', { prompt_id: id });
    const countEl = wrapper.querySelector('.js-copy .count');
    if (countEl) countEl.textContent = res.count;
    const btn = e.target.closest('.js-copy');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
    setTimeout(() => { btn.innerHTML = orig; }, 2000);
  }
});

// Load more
const lmBtn = document.getElementById('load-more');
if (lmBtn) {
  lmBtn.addEventListener('click', async () => {
    lmBtn.disabled = true;
    lmBtn.innerHTML = '<span style="width:14px;height:14px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin .6s linear infinite;"></span> Loading…';

    const page = Number(lmBtn.dataset.page);
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);
    if (lmBtn.dataset.cat) params.set('cat', lmBtn.dataset.cat);
    const res = await fetch(`${BASE}/prompts/load?${params}`);
    const json = await res.json();
    const grid = document.getElementById('prompt-grid');

    json.data.forEach(item => {
      const a = document.createElement('a');
      a.href = `${BASE}/prompt/${item.slug}`;
      a.className = 'pcard';
      const initials = (item.author || 'U').substring(0, 2).toUpperCase();
      a.innerHTML = `
        <div class="pcard-thumb">
          ${item.image_path ? `<img loading="lazy" src="${esc(item.image_path)}" alt="${esc(item.title)}">` : '<i class="bi bi-stars"></i>'}
          ${item.category_slug ? `<span class="pcard-cat cat-${esc(item.category_color)}"><i class="bi ${esc(item.category_icon)}"></i> ${esc(item.category_name)}</span>` : ''}
        </div>
        <div class="pcard-body">
          <div class="pcard-title">${esc(item.title)}</div>
          ${item.description ? `<div class="pcard-desc">${esc(item.description)}</div>` : ''}
          <div class="pcard-author">
            <span class="avatar avatar-xs">${initials}</span>
            ${esc(item.author || 'Unknown')}
          </div>
          <div class="pcard-stats">
            <span><i class="bi bi-heart-fill" style="color:#EF4444;"></i> ${item.likes_count ?? 0}</span>
            <span><i class="bi bi-bookmark-fill" style="color:#3B82F6;"></i> ${item.saves_count ?? 0}</span>
            <span style="margin-left:auto;"><i class="bi bi-eye-fill"></i> ${item.views_count ?? 0}</span>
          </div>
          ${item.prompt_text ? `<button type="button" class="pcard-copy js-card-copy" data-id="${item.id}" data-copy="${esc(item.prompt_text)}"><i class="bi bi-clipboard"></i> Copy prompt</button>` : ''}
        </div>`;
      grid.appendChild(a);
    });

    if (json.has_more) {
      lmBtn.dataset.page = json.next_page;
      lmBtn.disabled = false;
      lmBtn.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Load more';
    } else {
      lmBtn.remove();
    }
  });
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

// ── Search autocomplete: top prompts suggested while typing ─────────
document.querySelectorAll('.hero-search input[name="q"]').forEach(input => {
  const form = input.closest('form');
  form.style.position = 'relative';
  form.setAttribute('autocomplete', 'off');

  const box = document.createElement('div');
  box.className = 'search-suggest';
  box.hidden = true;
  form.appendChild(box);

  let items = [], active = -1, debounce = null, lastQ = '';

  const close = () => { box.hidden = true; box.innerHTML = ''; items = []; active = -1; };

  const highlight = (title, q) => {
    const i = title.toLowerCase().indexOf(q.toLowerCase());
    if (i < 0) return esc(title);
    return esc(title.slice(0, i)) + '<mark>' + esc(title.slice(i, i + q.length)) + '</mark>' + esc(title.slice(i + q.length));
  };

  const render = (data, q) => {
    if (!data.length) { close(); return; }
    box.innerHTML = data.map((s, i) => `
      <a class="ss-item" data-i="${i}" href="${BASE}/prompt/${encodeURIComponent(s.slug)}">
        <i class="bi bi-search ss-icon"></i>
        <span class="ss-title">${highlight(s.title, q)}</span>
        ${s.category_name ? `<span class="ss-cat cat-${esc(s.category_color)}"><i class="bi ${esc(s.category_icon)}"></i> ${esc(s.category_name)}</span>` : ''}
        ${Number(s.likes_count) > 0 ? `<span class="ss-likes"><i class="bi bi-heart-fill"></i> ${Number(s.likes_count)}</span>` : ''}
      </a>`).join('')
      + `<button type="submit" class="ss-item ss-all"><i class="bi bi-arrow-return-left ss-icon"></i> Search all results for "<strong>${esc(q)}</strong>"</button>`;
    items = [...box.querySelectorAll('.ss-item')];
    active = -1;
    box.hidden = false;
  };

  input.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(debounce);
    if (q.length < 2) { close(); return; }
    debounce = setTimeout(async () => {
      lastQ = q;
      try {
        const res = await fetch(`${BASE}/search/suggest?q=${encodeURIComponent(q)}`);
        const json = await res.json();
        if (q === lastQ) render(json.data || [], q);
      } catch (_) { close(); }
    }, 220);
  });

  input.addEventListener('keydown', (e) => {
    if (box.hidden || !items.length) return;
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      active = (active + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
      items.forEach((el, i) => el.classList.toggle('active', i === active));
      items[active].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter' && active >= 0 && !items[active].classList.contains('ss-all')) {
      e.preventDefault();
      window.location.href = items[active].href;
    } else if (e.key === 'Escape') {
      close();
    }
  });

  document.addEventListener('click', (e) => { if (!form.contains(e.target)) close(); });
});
