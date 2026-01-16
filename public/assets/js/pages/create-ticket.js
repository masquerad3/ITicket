// Create Ticket page helpers (counter + dropzone)
(function () {
  const form = document.getElementById('ticketForm');
  const desc = document.getElementById('description');
  const counter = document.getElementById('descCounter');
  const dropzone = document.getElementById('dropzone');
  const pickBtn = document.getElementById('pickFiles');
  const fileInput = document.getElementById('fileInput');
  const fileList = document.getElementById('fileList');

  const MAX = 1000;

  function formatSize(bytes) {
    if (typeof bytes !== 'number') return '';
    const kb = bytes / 1024;
    if (kb < 1024) return `${Math.round(kb)} KB`;
    return `${(kb / 1024).toFixed(1)} MB`;
  }

  function updateCounter() {
    if (!desc || !counter) return;
    const len = (desc.value || '').length;
    counter.textContent = `${len} / ${MAX}`;
  }

  function renderFiles(files) {
    if (!fileList) return;
    fileList.innerHTML = '';
    if (!files || files.length === 0) return;

    Array.from(files).forEach((f) => {
      const li = document.createElement('li');
      li.textContent = `${f.name} (${formatSize(f.size)})`;
      fileList.appendChild(li);
    });
  }

  function setFilesFromDrop(fileListObj) {
    if (!fileInput) return;

    // Chrome allows assignment; if it fails, we still render the list.
    try {
      fileInput.files = fileListObj;
    } catch (_) {
      // no-op
    }

    renderFiles(fileListObj);
  }

  if (desc) {
    desc.maxLength = MAX;
    desc.addEventListener('input', updateCounter);
    updateCounter();
  }

  if (pickBtn && fileInput) {
    pickBtn.addEventListener('click', () => fileInput.click());
  }

  if (fileInput) {
    fileInput.addEventListener('change', () => renderFiles(fileInput.files));
  }

  if (dropzone) {
    const stop = (e) => {
      e.preventDefault();
      e.stopPropagation();
    };

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((evt) => {
      dropzone.addEventListener(evt, stop);
    });

    dropzone.addEventListener('dragenter', () => dropzone.classList.add('is-dragover'));
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('is-dragover'));
    dropzone.addEventListener('drop', (e) => {
      dropzone.classList.remove('is-dragover');
      const dt = e.dataTransfer;
      if (!dt || !dt.files) return;
      setFilesFromDrop(dt.files);
    });

    dropzone.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        if (fileInput) fileInput.click();
      }
    });
  }

  // Safety: ensure browser validation is enabled on submit
  if (form) {
    form.addEventListener('submit', () => {
      // let the server validate; nothing special here.
    });
  }
})();
