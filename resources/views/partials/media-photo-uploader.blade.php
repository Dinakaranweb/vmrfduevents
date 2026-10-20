<style>
.media-upload-card { width: 150px; }
.media-upload-card .progress { background-color: #e2e8f0; }
</style>
<script>
window.VMRFDU_MediaUploader = window.VMRFDU_MediaUploader || {};
window.VMRFDU_MediaUploader.uploadUrl = "{{ route('submissions.upload.photo') }}";

window.VMRFDU_MediaUploader.init = function(opts) {
    const input = document.getElementById(opts.inputId);
    const cardsContainer = document.getElementById(opts.cardsContainerId);
    const hiddenContainer = document.getElementById(opts.hiddenContainerId);
    if (!input || !cardsContainer || !hiddenContainer) return;

    let pendingCount = 0;
    let seq = 0;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function setPending(delta) {
        pendingCount += delta;
        if (opts.onPendingChange) opts.onPendingChange(pendingCount);
    }

    function tokenCount() {
        return hiddenContainer.querySelectorAll('input[type="hidden"]').length;
    }

    function notifyCount() {
        if (opts.onCountChange) opts.onCountChange(tokenCount(), cardsContainer.children.length);
    }

    function uploadFile(fileToUpload, card, progressBar, statusEl, retryBtn, originalFile) {
        setPending(1);
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-danger', 'bg-success');
        progressBar.classList.add('progress-bar-animated', 'progress-bar-striped', 'bg-primary');
        statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> Starting upload...';
        retryBtn.classList.add('d-none');

        const existingHidden = hiddenContainer.querySelector('input[data-card-id="' + card.id + '"]');
        if (existingHidden) existingHidden.remove();

        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        formData.append('photo', fileToUpload, originalFile.name);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.max(1, Math.round((e.loaded / e.total) * 100));
                progressBar.style.width = percent + '%';
                statusEl.innerHTML = '<i class="bi bi-cloud-arrow-up-fill text-primary me-1"></i> Uploading... ' + percent + '%';
            }
        });

        xhr.addEventListener('load', function() {
            setPending(-1);
            let data = null;
            try { data = JSON.parse(xhr.responseText); } catch (e) {}

            if (xhr.status >= 200 && xhr.status < 300 && data && data.status === 'success') {
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                progressBar.classList.add('bg-success');
                statusEl.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Uploaded successfully';

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = opts.tokenFieldName;
                hidden.value = data.token;
                hidden.dataset.cardId = card.id;
                hiddenContainer.appendChild(hidden);
                notifyCount();
                return;
            }

            let reason = 'HTTP ' + xhr.status;
            if (xhr.status === 419) {
                reason = 'Your session expired (CSRF token invalid). Please refresh the page and try again.';
            } else if (xhr.status === 401 || xhr.status === 302 || (xhr.status === 200 && !data)) {
                reason = 'Your session has expired. Please log in again and retry.';
            } else if (data && data.errors && data.errors.photo) {
                reason = data.errors.photo[0];
            } else if (data && data.message) {
                reason = data.message;
            } else if (xhr.status === 0) {
                reason = 'Network error — could not reach the server.';
            }

            console.error('Photo upload failed:', xhr.status, xhr.responseText);
            handleFailure(reason);
        });

        xhr.addEventListener('error', function() {
            setPending(-1);
            console.error('Photo upload network error for', originalFile.name);
            handleFailure('Network error — could not reach the server.');
        });
        xhr.addEventListener('abort', function() {
            setPending(-1);
            handleFailure('Upload cancelled.');
        });

        function handleFailure(reason) {
            progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            progressBar.classList.add('bg-danger');
            statusEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> ' + (reason || 'Upload failed');
            retryBtn.classList.remove('d-none');
            notifyCount();
        }

        xhr.open('POST', window.VMRFDU_MediaUploader.uploadUrl);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    }

    async function startUpload(originalFile, card, progressBar, statusEl, retryBtn) {
        if (originalFile.size > 20 * 1024 * 1024) {
            statusEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Exceeds 20 MB limit';
            progressBar.classList.add('bg-danger');
            return;
        }

        let fileToUpload = originalFile;
        if (originalFile.type.startsWith('image/') && originalFile.size > 400 * 1024 && window.VMRFDU_ImageOptimizer) {
            statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> Optimizing image...';
            try {
                fileToUpload = await window.VMRFDU_ImageOptimizer.compressSingleImage(originalFile, 1600, 0.78);
            } catch (e) {
                fileToUpload = originalFile;
            }
        }

        uploadFile(fileToUpload, card, progressBar, statusEl, retryBtn, originalFile);
    }

    function addFileCard(file) {
        seq++;
        const cardId = opts.inputId + '_card_' + seq;
        const url = URL.createObjectURL(file);

        const card = document.createElement('div');
        card.className = 'media-upload-card position-relative border rounded-3 p-2 bg-white shadow-sm';
        card.id = cardId;
        card.innerHTML =
            '<div class="position-relative mb-2">' +
                '<img src="' + url + '" class="rounded-2 w-100 object-fit-cover preview-open-btn" style="height:100px;cursor:pointer;" title="Click to preview in a new tab">' +
                '<button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center shadow p-0 remove-photo-btn" style="width:22px;height:22px;transform:translate(30%,-30%);"><i class="bi bi-x"></i></button>' +
            '</div>' +
            '<div class="text-truncate text-xxs fw-semibold text-dark mb-1" title="' + file.name.replace(/"/g, '&quot;') + '">' + file.name + '</div>' +
            '<div class="progress mb-1" style="height:6px;"><div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width:0%"></div></div>' +
            '<div class="upload-status text-xxs"></div>' +
            '<button type="button" class="btn btn-outline-danger btn-sm w-100 mt-1 retry-upload-btn d-none text-xxs py-0"><i class="bi bi-arrow-clockwise"></i> Retry</button>';

        cardsContainer.appendChild(card);

        const progressBar = card.querySelector('.progress-bar');
        const statusEl = card.querySelector('.upload-status');
        const retryBtn = card.querySelector('.retry-upload-btn');
        const removeBtn = card.querySelector('.remove-photo-btn');
        const previewImg = card.querySelector('.preview-open-btn');
        if (previewImg) {
            previewImg.onclick = function() {
                window.open(url, '_blank');
            };
        }

        removeBtn.onclick = function(e) {
            e.preventDefault();
            const hidden = hiddenContainer.querySelector('input[data-card-id="' + card.id + '"]');
            if (hidden) hidden.remove();
            card.remove();
            notifyCount();
        };

        retryBtn.onclick = function(e) {
            e.preventDefault();
            startUpload(file, card, progressBar, statusEl, retryBtn);
        };

        startUpload(file, card, progressBar, statusEl, retryBtn);
        notifyCount();
    }

    input.addEventListener('change', function() {
        Array.from(input.files).forEach(function(file) {
            if (file.type.startsWith('image/')) {
                addFileCard(file);
            }
        });
        input.value = '';
    });

    return {
        tokenCount: tokenCount,
        photoCount: function() { return cardsContainer.children.length; },
        hasPending: function() { return pendingCount > 0; }
    };
};
</script>
