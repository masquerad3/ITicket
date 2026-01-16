// Ticket page: reply composer helpers (tabs + scroll)
(function () {
  const replyScroll = document.getElementById('replyScroll');
  const form = document.getElementById('composerForm');
  const body = document.getElementById('composerBody');
  const messageType = document.getElementById('messageType');
  const tabs = Array.from(document.querySelectorAll('.compose-tabs .tab-btn'));

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

  if (replyScroll) {
    replyScroll.addEventListener('click', () => {
      if (form) {
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      if (body) body.focus();
    });
  }

  if (tabs.length > 0) {
    setActive(tabs[0]);
  }
})();
