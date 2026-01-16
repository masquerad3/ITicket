// Ticket page: reply composer helpers (tabs + scroll)
(function () {
  const form = document.getElementById('composerForm');
  const body = document.getElementById('composerBody');
  const messageType = document.getElementById('messageType');
  const tabs = Array.from(document.querySelectorAll('.compose-tabs .tab-btn'));

  const statusForm = document.getElementById('statusForm');
  const statusSelect = document.querySelector('select[form="statusForm"][name="status"]');

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

      statusSelect.disabled = true;

      // Use requestSubmit when available (better), fallback to submit.
      if (typeof statusForm.requestSubmit === 'function') {
        statusForm.requestSubmit();
      } else {
        statusForm.submit();
      }
    });
  }

  if (tabs.length > 0) {
    setActive(tabs[0]);
  }
})();
