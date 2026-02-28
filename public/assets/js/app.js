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
    wrapper.querySelector('.js-like .count').textContent = result.count;
  }

  if (event.target.closest('.js-save')) {
    const result = await postData('/prompts/save', { prompt_id: promptId });
    wrapper.querySelector('.js-save .count').textContent = result.count;
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
    const res = await fetch(`/prompts/load?page=${page}`);
    const json = await res.json();
    const grid = document.getElementById('prompt-grid');

    json.data.forEach((item) => {
      const card = document.createElement('div');
      card.className = 'col-6 col-md-4 col-lg-3';
      card.innerHTML = `
        <div class="card h-100 shadow-sm">
          ${item.image_path ? `<img loading="lazy" src="${item.image_path}" class="card-img-top" alt="${item.title}">` : ''}
          <div class="card-body">
            <h6><a href="/prompt/${item.slug}">${item.title}</a></h6>
          </div>
        </div>`;
      grid.appendChild(card);
    });

    loadMoreBtn.dataset.page = json.next_page;
    if (!json.has_more) loadMoreBtn.remove();
  });
}
