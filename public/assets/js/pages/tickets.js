// Tickets list page: search + filter + sort + simple client-side paging
(function () {
  const searchInput = document.getElementById('ticketSearch');
  const searchClear = document.getElementById('ticketSearchClear');
  const statusFilter = document.getElementById('statusFilter');
  const priorityFilter = document.getElementById('priorityFilter');
  const sortFilter = document.getElementById('sortFilter');

  const resultsCount = document.getElementById('resultsCount');
  const emptyState = document.querySelector('.empty-state');

  const pagerPrevTop = document.getElementById('pagerPrevTop');
  const pagerNextTop = document.getElementById('pagerNextTop');
  const pageInfoTop = document.getElementById('pageInfoTop');

  const pagerPrevBottom = document.getElementById('pagerPrevBottom');
  const pagerNextBottom = document.getElementById('pagerNextBottom');
  const pageInfoBottom = document.getElementById('pageInfoBottom');

  const allCards = Array.from(document.querySelectorAll('.tickets-list .ticket-card'));

  const state = {
    page: 1,
    pageSize: 10,
  };

  function normalize(s) {
    return String(s || '').toLowerCase().trim();
  }

  function updatePagerUI(filteredCount) {
    const totalPages = Math.max(1, Math.ceil(filteredCount / state.pageSize));
    if (state.page > totalPages) state.page = totalPages;

    const start = filteredCount === 0 ? 0 : (state.page - 1) * state.pageSize + 1;
    const end = Math.min(filteredCount, state.page * state.pageSize);
    const label = `${start} - ${end} of ${filteredCount}`;

    if (pageInfoTop) pageInfoTop.textContent = label;
    if (pageInfoBottom) pageInfoBottom.textContent = label;

    const hasPrev = state.page > 1;
    const hasNext = state.page < totalPages;

    [pagerPrevTop, pagerPrevBottom].forEach((btn) => {
      if (!btn) return;
      btn.disabled = !hasPrev;
    });

    [pagerNextTop, pagerNextBottom].forEach((btn) => {
      if (!btn) return;
      btn.disabled = !hasNext;
    });
  }

  function apply() {
    const q = normalize(searchInput && searchInput.value);
    const status = statusFilter && statusFilter.value ? statusFilter.value : '';
    const priority = priorityFilter && priorityFilter.value ? priorityFilter.value : '';
    const sort = sortFilter && sortFilter.value ? sortFilter.value : 'latest';

    let filtered = allCards.filter((card) => {
      const ds = card.dataset || {};
      if (status && ds.status !== status) return false;
      if (priority && ds.priority !== priority) return false;
      if (q) {
        const blob = normalize(ds.search);
        if (!blob.includes(q)) return false;
      }
      return true;
    });

    filtered.sort((a, b) => {
      const ta = Number(a.dataset.createdTs || 0);
      const tb = Number(b.dataset.createdTs || 0);
      return sort === 'oldest' ? ta - tb : tb - ta;
    });

    // Hide all, then show page slice.
    allCards.forEach((c) => (c.hidden = true));

    const startIndex = (state.page - 1) * state.pageSize;
    const pageItems = filtered.slice(startIndex, startIndex + state.pageSize);
    pageItems.forEach((c) => (c.hidden = false));

    if (resultsCount) {
      const n = filtered.length;
      resultsCount.textContent = `Showing ${n} Ticket${n === 1 ? '' : 's'}`;
    }

    if (emptyState) {
      emptyState.hidden = filtered.length > 0;
    }

    updatePagerUI(filtered.length);
  }

  function go(delta) {
    state.page += delta;
    if (state.page < 1) state.page = 1;
    apply();
  }

  // Events
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      state.page = 1;
      apply();
    });
  }

  if (searchClear && searchInput) {
    searchClear.addEventListener('click', () => {
      searchInput.value = '';
      state.page = 1;
      apply();
      searchInput.focus();
    });
  }

  [statusFilter, priorityFilter, sortFilter].forEach((el) => {
    if (!el) return;
    el.addEventListener('change', () => {
      state.page = 1;
      apply();
    });
  });

  [pagerPrevTop, pagerPrevBottom].forEach((btn) => {
    if (!btn) return;
    btn.addEventListener('click', () => go(-1));
  });

  [pagerNextTop, pagerNextBottom].forEach((btn) => {
    if (!btn) return;
    btn.addEventListener('click', () => go(1));
  });

  // Initial render
  apply();
})();
