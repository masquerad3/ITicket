// Ticket page: reply composer helpers (tabs + scroll)
(function () {
  const form = document.getElementById('composerForm');
  const body = document.getElementById('composerBody');
  const messageType = document.getElementById('messageType');
  const tabs = Array.from(document.querySelectorAll('.compose-tabs .tab-btn'));

  const composerAttachBtn = document.getElementById('composerAttachBtn');

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

  if (composerAttachBtn && uploadInput) {
    composerAttachBtn.addEventListener('click', () => uploadInput.click());
  }

  if (uploadInput && uploadForm) {
    uploadInput.addEventListener('change', () => {
      if (uploadInput.files && uploadInput.files.length > 0) {
        uploadForm.submit();
      }
    });
  }

  if (tabs.length > 0) {
    setActive(tabs[0]);
  }
})();
