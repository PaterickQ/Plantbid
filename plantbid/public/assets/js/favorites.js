document.addEventListener('DOMContentLoaded', () => {
  const storageKey = 'plantbid.favorites';
  const baseUrl = document.body.dataset.baseUrl || '';
  const useQueryRoutes = document.body.dataset.useQueryRoutes === '1';

  const loadFavorites = () => {
    try {
      const raw = localStorage.getItem(storageKey);
      return raw ? JSON.parse(raw) : [];
    } catch (err) {
      return [];
    }
  };

  const saveFavorites = (ids) => {
    localStorage.setItem(storageKey, JSON.stringify(ids));
  };

  const isFavorite = (id, ids) => ids.includes(id);

  const toggleFavorite = (id) => {
    const ids = loadFavorites();
    const index = ids.indexOf(id);
    if (index >= 0) {
      ids.splice(index, 1);
    } else {
      ids.push(id);
    }
    saveFavorites(ids);
    updateButtons();
    renderFavoritesList();
  };

  const updateButtons = () => {
    const ids = loadFavorites();
    document.querySelectorAll('.favorite-toggle').forEach((btn) => {
      const id = parseInt(btn.dataset.auctionId, 10);
      const active = isFavorite(id, ids);
      btn.innerHTML = active ? '&#9733;' : '&#9734;';
      btn.classList.toggle('btn-warning', active);
      btn.classList.toggle('btn-outline-warning', !active);
    });
  };

  const buildApiUrl = (ids) => {
    if (useQueryRoutes) {
      return `${baseUrl}/index.php?route=api/auctions&ids=${encodeURIComponent(ids)}`;
    }
    return `${baseUrl}/api/auctions?ids=${encodeURIComponent(ids)}`;
  };

  const buildAuctionUrl = (id) => {
    if (useQueryRoutes) {
      return `${baseUrl}/index.php?route=auction&id=${id}`;
    }
    return `${baseUrl}/auction?id=${id}`;
  };

  const renderFavoritesList = () => {
    const container = document.querySelector('[data-favorites-list]');
    const emptyState = document.getElementById('favorites-empty');
    if (!container) {
      return;
    }

    const ids = loadFavorites();
    if (!ids.length) {
      if (emptyState) emptyState.classList.remove('d-none');
      container.innerHTML = '';
      return;
    }

    if (emptyState) emptyState.classList.add('d-none');
    fetch(buildApiUrl(ids.join(',')))
      .then((response) => response.json())
      .then((payload) => {
        const items = payload.data || [];
        container.innerHTML = '';
        items.forEach((item) => {
          const imgSrc = item.image_url
            ? item.image_url
            : item.image_path
            ? item.image_path.startsWith('http')
              ? item.image_path
              : `uploads/${item.image_path}`
            : 'uploads/nophoto.png';

          const col = document.createElement('div');
          col.className = 'col-md-4 mb-4';
          col.innerHTML = `
            <div class="card h-100 shadow-sm">
              <img src="${imgSrc}" class="card-img-top" alt="Obrazek" style="height:200px; object-fit:cover;">
              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start">
                  <h5 class="card-title">
                    <a href="${buildAuctionUrl(item.id)}">${item.title}</a>
                  </h5>
                  <button type="button" class="btn btn-sm btn-outline-warning favorite-toggle" data-auction-id="${item.id}">
                    &#9734;
                  </button>
                </div>
                <p class="mt-auto fw-bold">Aktualni cena: ${Number(item.current_price).toFixed(2)} Kc</p>
              </div>
            </div>
          `;
          container.appendChild(col);
        });
        updateButtons();
      })
      .catch(() => {
        container.innerHTML = '';
        if (emptyState) emptyState.classList.remove('d-none');
      });
  };

  document.addEventListener('click', (event) => {
    const btn = event.target.closest('.favorite-toggle');
    if (!btn) return;
    event.preventDefault();
    const id = parseInt(btn.dataset.auctionId, 10);
    if (!Number.isNaN(id)) {
      toggleFavorite(id);
    }
  });

  updateButtons();
  renderFavoritesList();
});
