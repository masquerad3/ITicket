(() => {
  // Copy anchor links in the article body (if present).
  document.querySelectorAll('.copy-link[data-target]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const target = btn.getAttribute('data-target');
      if (!target) return;

      const url = `${window.location.origin}${window.location.pathname}${target}`;

      try {
        await navigator.clipboard.writeText(url);
        btn.blur();
      } catch {
        // ignore
      }
    });
  });

  // Simple feedback buttons (if present).
  const fbYes = document.getElementById('fbYes');
  const fbNo = document.getElementById('fbNo');
  const fbMsg = document.getElementById('fbMsg');

  const setMsg = (text) => {
    if (fbMsg) fbMsg.textContent = text;
  };

  if (fbYes) fbYes.addEventListener('click', () => setMsg('Thanks! Glad it helped.'));
  if (fbNo) fbNo.addEventListener('click', () => setMsg('Thanks — consider creating a ticket so we can help.'));
})();
