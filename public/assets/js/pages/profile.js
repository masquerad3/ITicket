// Profile page: Change Password modal
(function () {
  const openBtn = document.getElementById('openPwModal');
  const backdrop = document.getElementById('pwModalBackdrop');

  if (!openBtn || !backdrop) return;

  const modal = backdrop.querySelector('.modal');
  const closeBtn = document.getElementById('closePwModal');
  const cancelBtn = document.getElementById('cancelPwModal');

  let lastActiveElement = null;

  function isOpen() {
    return backdrop.classList.contains('is-open');
  }

  function getFocusable(container) {
    if (!container) return [];
    const selector = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])'
    ].join(',');

    return Array.from(container.querySelectorAll(selector)).filter((el) => el instanceof HTMLElement);
  }

  function openModal() {
    if (isOpen()) return;

    lastActiveElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;

    backdrop.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');

    // Use rAF so the browser has a chance to apply initial hidden styles
    // before we add the class that triggers transitions.
    requestAnimationFrame(() => {
      backdrop.classList.add('is-open');

      const firstInput = document.getElementById('current_password');
      if (firstInput instanceof HTMLElement) {
        firstInput.focus();
        return;
      }

      if (modal instanceof HTMLElement) modal.focus();
    });
  }

  function closeModal() {
    if (!isOpen()) return;

    backdrop.classList.remove('is-open');
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');

    if (lastActiveElement) lastActiveElement.focus();
  }

  function onBackdropClick(e) {
    if (e.target === backdrop) closeModal();
  }

  function onKeydown(e) {
    if (!isOpen()) return;

    if (e.key === 'Escape') {
      e.preventDefault();
      closeModal();
      return;
    }

    if (e.key !== 'Tab') return;

    const focusables = getFocusable(modal);
    if (focusables.length === 0) {
      e.preventDefault();
      if (modal instanceof HTMLElement) modal.focus();
      return;
    }

    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    const active = document.activeElement;

    if (e.shiftKey && active === first) {
      e.preventDefault();
      last.focus();
      return;
    }

    if (!e.shiftKey && active === last) {
      e.preventDefault();
      first.focus();
    }
  }

  function setPasswordVisible(input, visible, toggleButton) {
    input.type = visible ? 'text' : 'password';

    const icon = toggleButton.querySelector('i');
    if (icon) {
      icon.classList.toggle('bx-show', !visible);
      icon.classList.toggle('bx-hide', visible);
    }

    toggleButton.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
  }

  function wirePasswordToggles() {
    const toggles = backdrop.querySelectorAll('[data-toggle-password]');
    toggles.forEach((toggle) => {
      if (!(toggle instanceof HTMLButtonElement)) return;

      const inputId = toggle.getAttribute('data-toggle-password');
      if (!inputId) return;

      const input = document.getElementById(inputId);
      if (!(input instanceof HTMLInputElement)) return;

      toggle.addEventListener('click', () => {
        const visible = input.type === 'password';
        setPasswordVisible(input, visible, toggle);
        input.focus();
      });
    });
  }

  openBtn.addEventListener('click', openModal);
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

  backdrop.addEventListener('click', onBackdropClick);
  document.addEventListener('keydown', onKeydown);

  wirePasswordToggles();
})();
