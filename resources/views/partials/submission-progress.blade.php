<script>
function submitFormWithProgress(form) {
    const banner = document.getElementById('uploadProgressBanner');
    const statusText = document.getElementById('uploadProgressText');
    const progressBar = document.getElementById('uploadProgressBar');
    const submitBtn = document.getElementById('submitSubmissionBtn') || form.querySelector('button[type="submit"]');

    if (banner) {
        banner.classList.remove('d-none');
        banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    if (progressBar) {
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-danger');
        progressBar.classList.add('progress-bar-striped', 'progress-bar-animated', 'bg-primary');
        progressBar.textContent = '';
    }
    if (statusText) statusText.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Submitting activity... 0%';
    if (submitBtn) {
        submitBtn.disabled = true;
        if (!submitBtn.dataset.originalHtml) submitBtn.dataset.originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Submitting... 0%';
    }

    const xhr = new XMLHttpRequest();
    const formData = new FormData(form);

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            // Cap at 99% until fully loaded
            const percent = Math.min(99, Math.round((e.loaded / e.total) * 100));
            if (progressBar) progressBar.style.width = percent + '%';
            if (statusText) statusText.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Submitting activity... ' + percent + '%';
            if (submitBtn) submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Submitting... ' + percent + '%';
        }
    });

    xhr.addEventListener('load', function() {
        let data = null;
        try { data = JSON.parse(xhr.responseText); } catch (err) {}

        if (xhr.status >= 200 && xhr.status < 300 && data && data.status === 'success') {
            if (progressBar) progressBar.style.width = '100%';
            if (statusText) statusText.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Submitted successfully! Redirecting...';
            if (submitBtn) submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Redirecting...';
            window.location.href = data.redirect || '/submissions';
            return;
        }

        if (xhr.status === 422 && data && data.errors) {
            showSubmitErrors(Object.values(data.errors).map(function(arr) { return arr[0]; }));
        } else {
            showSubmitErrors(['Something went wrong while submitting. Please try again.']);
        }

        form.dataset.submitting = 'false';
        if (banner) banner.classList.add('d-none');
        if (submitBtn) {
            submitBtn.disabled = false;
            if (submitBtn.dataset.originalHtml) submitBtn.innerHTML = submitBtn.dataset.originalHtml;
        }
    });

    xhr.addEventListener('error', function() {
        showSubmitErrors(['Network error – could not reach the server. Please check your connection and try again.']);
        form.dataset.submitting = 'false';
        if (banner) banner.classList.add('d-none');
        if (submitBtn) {
            submitBtn.disabled = false;
            if (submitBtn.dataset.originalHtml) submitBtn.innerHTML = submitBtn.dataset.originalHtml;
        }
    });

    // Handle method spoofing for Laravel PUT requests if action contains PUT
    let method = form.method.toUpperCase();
    const actionUrl = form.action;
    
    // Check if _method exists
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput && methodInput.value.toUpperCase() === 'PUT') {
        method = 'POST'; // Keep as POST for FormData spoofing
    }

    xhr.open(method, actionUrl);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    // CSRF token is already in FormData
    xhr.send(formData);
}

function showSubmitErrors(messages) {
    let box = document.getElementById('ajaxErrorsBox');
    if (!box) {
        box = document.createElement('div');
        box.id = 'ajaxErrorsBox';
        box.className = 'alert alert-danger shadow-sm rounded-4 p-4 mb-4';
        const card = document.querySelector('.portal-card');
        if (card && card.parentNode) card.parentNode.insertBefore(box, card);
    }
    box.innerHTML = '<h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please correct the following errors:</h6><ul class="mb-0 small">' +
        messages.map(function(m) { return '<li>' + m + '</li>'; }).join('') +
        '</ul>';
    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
