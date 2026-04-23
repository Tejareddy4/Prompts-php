async function postData(url, data) {
  const fd = new FormData();
  Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  fd.append('_csrf', window.CSRF_TOKEN);
  const res = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  return res.json();
}

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
    const res = await fetch(`/prompts/load?${params}`);
    const json = await res.json();
    const grid = document.getElementById('prompt-grid');

    json.data.forEach(item => {
      const a = document.createElement('a');
      a.href = `/prompt/${item.slug}`;
      a.className = 'pcard';
      const initials = (item.author || 'U').substring(0, 2).toUpperCase();
      a.innerHTML = `
        <div class="pcard-thumb">
          ${item.image_path ? `<img loading="lazy" src="${esc(item.image_path)}" alt="${esc(item.title)}">` : '<i class="bi bi-stars"></i>'}
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
