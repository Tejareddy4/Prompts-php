async function postData(url, data) {
  const fd = new FormData();
  Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  fd.append('_csrf', window.CSRF_TOKEN);
  const res = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  return res.json();
}

document.addEventListener('click', async (event) => {
  const wrapper = event.target.closest('[data-prompt-id]');
  if (!wrapper) return;
  const promptId = wrapper.dataset.promptId;
  if (!promptId || promptId === 'not-counted') return;

  if (event.target.closest('.js-like')) {
    const btn = wrapper.querySelector('.js-like');
    const result = await postData('/prompts/like', { prompt_id: promptId });
    btn.querySelector('.count').textContent = result.count;
    const liked = result.liked;
    btn.classList.toggle('btn-danger', liked);
    btn.classList.toggle('btn-outline-danger', !liked);
    btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
    const label = btn.querySelector('span:not(.count)');
    if (label) label.textContent = liked ? 'Liked' : 'Like';
  }

  if (event.target.closest('.js-save')) {
    const btn = wrapper.querySelector('.js-save');
    const result = await postData('/prompts/save', { prompt_id: promptId });
    btn.querySelector('.count').textContent = result.count;
    const saved = result.saved;
    btn.classList.toggle('btn-primary', saved);
    btn.classList.toggle('btn-outline-primary', !saved);
    btn.setAttribute('aria-pressed', saved ? 'true' : 'false');
    const label = btn.querySelector('span:not(.count)');
    if (label) label.textContent = saved ? 'Saved' : 'Save';
  }

  if (event.target.closest('.js-copy')) {
    const promptTextEl = document.getElementById('prompt-text');
    if (promptTextEl) {
      await navigator.clipboard.writeText(promptTextEl.innerText);
    }
    const result = await postData('/prompts/copy', { prompt_id: promptId });
    const countEl = wrapper.querySelector('.js-copy .count');
    if (countEl) countEl.textContent = result.count;

    const btn = event.target.closest('.js-copy');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
    setTimeout(() => { btn.innerHTML = orig; }, 2000);
  }
});

// Load more
const loadMoreBtn = document.getElementById('load-more');
if (loadMoreBtn) {
  loadMoreBtn.addEventListener('click', async () => {
    loadMoreBtn.disabled = true;
    loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading…';

    const page = Number(loadMoreBtn.dataset.page);
    const params = new URLSearchParams(window.location.search);
    params.set('page', String(page));
    const res = await fetch(`/prompts/load?${params.toString()}`);
    const json = await res.json();
    const grid = document.getElementById('prompt-grid');

    json.data.forEach((item) => {
      const authorInitials = (item.author || 'U').substring(0, 2).toUpperCase();
      const col = document.createElement('div');
      col.className = 'col-12 col-md-6 col-lg-4';
      col.innerHTML = `
        <div class="prompt-card">
          <div class="card-img-wrap">
            ${item.image_path
              ? `<img loading="lazy" src="${item.image_path}" alt="${escHtml(item.title)}">`
              : `<span class="card-img-placeholder"><i class="bi bi-stars"></i></span>`
            }
          </div>
          <div class="card-body">
            <h6 class="card-title">
              <a href="/prompt/${item.slug}">${escHtml(item.title)}</a>
            </h6>
            ${item.description ? `<p class="card-desc">${escHtml(item.description)}</p>` : ''}
            <div class="card-author">
              <span class="avatar-xs">${authorInitials}</span>
              <span class="small text-muted">${escHtml(item.author || 'Unknown')}</span>
            </div>
            <div class="card-stats">
              <span title="Likes"><i class="bi bi-heart-fill text-danger"></i> ${item.likes_count ?? 0}</span>
              <span title="Saves"><i class="bi bi-bookmark-fill text-primary"></i> ${item.saves_count ?? 0}</span>
              <span title="Copies"><i class="bi bi-clipboard-fill text-secondary"></i> ${item.copies_count ?? 0}</span>
              <span title="Views" class="ms-auto"><i class="bi bi-eye-fill"></i> ${item.views_count ?? 0}</span>
            </div>
          </div>
        </div>`;
      grid.appendChild(col);
    });

    if (json.has_more) {
      loadMoreBtn.dataset.page = json.next_page;
      loadMoreBtn.disabled = false;
      loadMoreBtn.innerHTML = '<i class="bi bi-arrow-down-circle me-1"></i> Load more prompts';
    } else {
      loadMoreBtn.remove();
    }
  });
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}
