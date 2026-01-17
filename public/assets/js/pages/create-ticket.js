// Create Ticket page helpers (counter + dropzone)
(function () {
  const form = document.getElementById('ticketForm');
  const desc = document.getElementById('description');
  const counter = document.getElementById('descCounter');
  const dropzone = document.getElementById('dropzone');
  const pickBtn = document.getElementById('pickFiles');
  const fileInput = document.getElementById('fileInput');
  const fileList = document.getElementById('fileList');

  const canUseDataTransfer = typeof DataTransfer !== 'undefined';
  let dt = canUseDataTransfer ? new DataTransfer() : null;

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

  function syncAndRender(files) {
    if (!fileList) return;
    fileList.innerHTML = '';
    if (!files || files.length === 0) return;

    const arr = Array.from(files);

    arr.forEach((f, idx) => {
      const li = document.createElement('li');

      const icon = document.createElement('i');
      icon.className = 'bx bx-file';
      icon.setAttribute('aria-hidden', 'true');

      const meta = document.createElement('div');
      meta.className = 'file-meta';

      const name = document.createElement('div');
      name.className = 'file-name';
      name.textContent = f.name;

      const size = document.createElement('div');
      size.className = 'file-size';
      size.textContent = formatSize(f.size);

      meta.appendChild(name);
      meta.appendChild(size);

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'file-remove';
      removeBtn.setAttribute('aria-label', `Remove ${f.name}`);
      removeBtn.innerHTML = "<i class='bx bx-x'></i>";
      removeBtn.addEventListener('click', () => {
        if (!fileInput) return;

        if (!canUseDataTransfer) {
          fileInput.value = '';
          syncAndRender([]);
          return;
        }

        const next = new DataTransfer();
        arr.forEach((file, i) => {
          if (i !== idx) next.items.add(file);
        });

        dt = next;
        try {
          fileInput.files = dt.files;
        } catch (_) {
          // no-op
        }

        syncAndRender(dt.files);
      });

      li.appendChild(icon);
      li.appendChild(meta);
      li.appendChild(removeBtn);
      fileList.appendChild(li);
    });
  }

  function mergeFiles(existing, incoming) {
    const next = new DataTransfer();
    const seen = new Set();

    const add = (file) => {
      const key = `${file.name}|${file.size}|${file.lastModified}`;
      if (seen.has(key)) return;
      seen.add(key);
      next.items.add(file);
    };

    Array.from(existing || []).forEach(add);
    Array.from(incoming || []).forEach(add);
    return next;
  }

  function addSelectedFiles(fileListObj) {
    if (!fileInput) return;

    if (!canUseDataTransfer) {
      // Browser will replace the selection; we at least render it.
      syncAndRender(fileListObj);
      return;
    }

    dt = mergeFiles(dt?.files, fileListObj);

    try {
      fileInput.files = dt.files;
    } catch (_) {
      // no-op
    }

    syncAndRender(dt.files);
  }

  function setFilesFromDrop(fileListObj) {
    addSelectedFiles(fileListObj);
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
    fileInput.addEventListener('change', () => {
      addSelectedFiles(fileInput.files);
    });
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
