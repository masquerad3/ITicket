(() => {
  const form = document.querySelector('.kb-search');
  if (!form) return;

  const input = form.querySelector('input[name="q"]');
  const clearBtn = form.querySelector('.btn-clear');

  if (clearBtn && input) {
    clearBtn.addEventListener('click', () => {
      input.value = '';
      form.submit();
    });
  }
})();
