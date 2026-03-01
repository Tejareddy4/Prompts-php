async function postData(url, data) {
  const formData = new FormData();
  Object.entries(data).forEach(([k, v]) => formData.append(k, v));
  formData.append('_csrf', window.CSRF_TOKEN);
  const res = await fetch(url, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  return res.json();
}

document.addEventListener('click', async (event) => {
  const wrapper = event.target.closest('[data-prompt-id]');
  if (!wrapper) return;
  const promptId = wrapper.dataset.promptId;

  if (event.target.closest('.js-like')) {
    const result = await postData('/prompts/like', { prompt_id: promptId });
    const btn = wrapper.querySelector('.js-like');
    btn.querySelector('.count').textContent = result.count;
    btn.classList.toggle('btn-danger', result.liked);
    btn.classList.toggle('btn-outline-danger', !result.liked);
    btn.setAttribute('aria-pressed', result.liked ? 'true' : 'false');
  }

  if (event.target.closest('.js-save')) {
    const result = await postData('/prompts/save', { prompt_id: promptId });
    const btn = wrapper.querySelector('.js-save');
    btn.querySelector('.count').textContent = result.count;
    btn.classList.toggle('btn-secondary', result.saved);
    btn.classList.toggle('btn-outline-secondary', !result.saved);
    btn.setAttribute('aria-pressed', result.saved ? 'true' : 'false');
  }

  if (event.target.closest('.js-copy')) {
    await navigator.clipboard.writeText(document.getElementById('prompt-text').innerText);
    const result = await postData('/prompts/copy', { prompt_id: promptId });
    wrapper.querySelector('.js-copy .count').textContent = result.count;
  }
});

const loadMoreBtn = document.getElementById('load-more');
if (loadMoreBtn) {
  loadMoreBtn.addEventListener('click', async () => {
    const page = Number(loadMoreBtn.dataset.page);
    const params = new URLSearchParams(window.location.search);
    params.set('page', String(page));
    const res = await fetch(`/prompts/load?${params.toString()}`);
    const json = await res.json();
    const grid = document.getElementById('prompt-grid');

    json.data.forEach((item) => {
      const card = document.createElement('div');
      card.className = 'col-12 col-md-6 col-lg-4';
      card.innerHTML = `
        <div class="card h-100 shadow-sm border-0 prompt-card">
          ${item.image_path ? `<img loading="lazy" src="${item.image_path}" class="card-img-top" alt="${item.title}">` : ''}
          <div class="card-body">
            <h6 class="mb-1"><a class="text-decoration-none" href="/prompt/${item.slug}">${item.title}</a></h6>
            <div class="small text-muted d-flex flex-wrap gap-2">
                <span><i class="bi bi-heart-fill text-danger"></i> ${item.likes_count ?? 0}</span>
                <span><i class="bi bi-bookmark-fill"></i> ${item.saves_count ?? 0}</span>
                <span><i class="bi bi-eye-fill"></i> ${item.views_count ?? 0}</span>
            </div>
          </div>
        </div>`;
      grid.appendChild(card);
    });

    loadMoreBtn.dataset.page = json.next_page;
    if (!json.has_more) loadMoreBtn.remove();
  });
}
