// Ticket page: reply composer helpers (tabs + scroll)
(function () {
  const form = document.getElementById('composerForm');
  const body = document.getElementById('composerBody');
  const messageType = document.getElementById('messageType');
  const tabs = Array.from(document.querySelectorAll('.compose-tabs .tab-btn'));

  const statusForm = document.getElementById('statusForm');
  const statusSelect = document.getElementById('statusSelect');
  const statusHidden = document.getElementById('statusHidden');

  const composerAttachBtn = document.getElementById('composerAttachBtn');
  const composerFilesInput = document.getElementById('composerFilesInput');
  const composerFilesHint = document.getElementById('composerFilesHint');

  const uploadBtn = document.getElementById('uploadAttachmentsBtn');
  const uploadInput = document.getElementById('uploadAttachmentsInput');
  const uploadForm = document.getElementById('uploadAttachmentsForm');

  function setActive(tab) {
    tabs.forEach((t) => t.classList.toggle('active', t === tab));
    const type = tab && tab.dataset ? tab.dataset.tab : 'public';
    if (messageType) messageType.value = type === 'internal' ? 'internal' : 'public';
    if (body) {
      body.placeholder = type === 'internal'
        ? 'Write an internal note (visible to staff only)...'
        : 'Write your message...';
    }
  }

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => setActive(tab));
  });

  if (uploadBtn && uploadInput) {
    uploadBtn.addEventListener('click', () => uploadInput.click());
  }

  if (composerAttachBtn && composerFilesInput) {
    composerAttachBtn.addEventListener('click', () => composerFilesInput.click());
  }

  if (composerFilesInput && composerFilesHint) {
    composerFilesInput.addEventListener('change', () => {
      const n = composerFilesInput.files ? composerFilesInput.files.length : 0;
      if (n > 0) {
        composerFilesHint.textContent = n === 1 ? '1 file selected' : `${n} files selected`;
        composerFilesHint.style.display = 'inline-flex';
      } else {
        composerFilesHint.textContent = '';
        composerFilesHint.style.display = 'none';
      }
    });
  }

  if (uploadInput && uploadForm) {
    uploadInput.addEventListener('change', () => {
      if (uploadInput.files && uploadInput.files.length > 0) {
        uploadForm.submit();
      }
    });
  }

  if (statusForm && statusSelect) {
    statusSelect.addEventListener('change', () => {
      const val = (statusSelect.value || '').trim();
      if (!val) return;

      if (statusHidden) {
        statusHidden.value = val;
      }

      statusSelect.disabled = true;

      statusForm.submit();
    });
  }

  if (tabs.length > 0) {
    setActive(tabs[0]);
  }
})();

// Ticket page: dismissible alerts + message action menus
(function () {
  document.addEventListener('click', (e) => {
    const dismissBtn = e.target && e.target.closest ? e.target.closest('[data-dismiss="alert"]') : null;
    if (dismissBtn) {
      const alert = dismissBtn.closest('.alert');
      if (alert) alert.remove();
      return;
    }

    const menuBtn = e.target && e.target.closest ? e.target.closest('.msg-menu-btn') : null;

    // Close menus when clicking outside
    if (!menuBtn) {
      document.querySelectorAll('.msg-menu.open').forEach((m) => m.classList.remove('open'));
      document.querySelectorAll('.msg-menu-btn[aria-expanded="true"]').forEach((b) => b.setAttribute('aria-expanded', 'false'));
      return;
    }

    const actions = menuBtn.closest('.msg-actions');
    const menu = actions ? actions.querySelector('.msg-menu') : null;
    if (!menu) return;

    // Toggle this menu, close others.
    const willOpen = !menu.classList.contains('open');
    document.querySelectorAll('.msg-menu.open').forEach((m) => m.classList.remove('open'));
    document.querySelectorAll('.msg-menu-btn[aria-expanded="true"]').forEach((b) => b.setAttribute('aria-expanded', 'false'));

    menu.classList.toggle('open', willOpen);
    menuBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.msg-menu.open').forEach((m) => m.classList.remove('open'));
    document.querySelectorAll('.msg-menu-btn[aria-expanded="true"]').forEach((b) => b.setAttribute('aria-expanded', 'false'));
  });
})();
