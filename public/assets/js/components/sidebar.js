// Sidebar open/close + backdrop handling
    (function(){
      const menuBtn = document.querySelector('.menu-button');
      const closeBtn = document.querySelector('.menu-close');
      const sidebar = document.querySelector('.slide-menu');
      const backdrop = document.querySelector('.backdrop');

      if (!menuBtn || !sidebar || !closeBtn || !backdrop) return; // Ensure all elements exist

      function openMenu(){
        sidebar.classList.add('is-open');
        backdrop.classList.add('is-visible');
        closeBtn.focus();   // Move focus to the close button
        document.body.style.overflow = 'hidden';
      }

      function closeMenu(){
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
        menuBtn.focus();    // Return focus to the menu button
        document.body.style.overflow = '';
      }

      menuBtn?.addEventListener('click', openMenu);
      closeBtn?.addEventListener('click', closeMenu);
      backdrop?.addEventListener('click', closeMenu);
      // Close on ESC
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
    })();