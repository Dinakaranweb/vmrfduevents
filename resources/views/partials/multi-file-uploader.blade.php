<style>
.attachment-preview-card {
    width: 120px;
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    flex-shrink: 0;
}
.attachment-preview-img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    background: #e2e8f0;
}
.attachment-preview-icon {
    width: 100%;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #64748b;
    background: #f1f5f9;
}
.attachment-preview-info {
    padding: 6px;
    font-size: 0.65rem;
    line-height: 1.2;
}
.attachment-preview-name {
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.attachment-preview-size {
    color: #64748b;
    margin-top: 2px;
}
.attachment-preview-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.75rem;
    border: none;
    z-index: 10;
    transition: background 0.2s;
}
.attachment-preview-remove:hover {
    background: rgba(185, 28, 28, 1);
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    function setupMultiUploader(inputId, previewContainerId) {
        const input = document.getElementById(inputId);
        const previewContainer = document.getElementById(previewContainerId);
        if (!input || !previewContainer) return;

        // Use DataTransfer to accumulate files instead of replacing them
        let dataTransfer = new DataTransfer();

        input.addEventListener('change', function() {
            const newFiles = Array.from(input.files);
            
            // Add new files to our DataTransfer object
            newFiles.forEach(file => {
                // Prevent exact duplicates (by name and size)
                let isDuplicate = false;
                for (let i = 0; i < dataTransfer.items.length; i++) {
                    if (dataTransfer.items[i].getAsFile().name === file.name && dataTransfer.items[i].getAsFile().size === file.size) {
                        isDuplicate = true;
                        break;
                    }
                }
                
                if (!isDuplicate) {
                    dataTransfer.items.add(file);
                }
            });

            // Update the input files property
            input.files = dataTransfer.files;
            
            // Re-render previews
            renderPreviews();
        });

        function renderPreviews() {
            previewContainer.innerHTML = '';
            
            Array.from(dataTransfer.files).forEach((file, index) => {
                const isImage = file.type.startsWith('image/');
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                
                const card = document.createElement('div');
                card.className = 'attachment-preview-card';
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'attachment-preview-remove';
                removeBtn.innerHTML = '<i class="bi bi-x"></i>';
                removeBtn.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    removeFile(index);
                };
                
                let visualEl = '';
                if (isImage) {
                    visualEl = `<img src="${URL.createObjectURL(file)}" class="attachment-preview-img" alt="Preview">`;
                } else {
                    let iconClass = 'bi-file-earmark';
                    if (file.type === 'application/pdf') iconClass = 'bi-file-earmark-pdf text-danger';
                    else if (file.type.includes('word') || file.name.endsWith('.doc') || file.name.endsWith('.docx')) iconClass = 'bi-file-earmark-word text-primary';
                    else if (file.type.includes('excel') || file.name.endsWith('.xls') || file.name.endsWith('.xlsx')) iconClass = 'bi-file-earmark-excel text-success';
                    
                    visualEl = `<div class="attachment-preview-icon"><i class="bi ${iconClass}"></i></div>`;
                }
                
                card.innerHTML = `
                    ${visualEl}
                    <div class="attachment-preview-info">
                        <div class="attachment-preview-name" title="${file.name}">${file.name}</div>
                        <div class="attachment-preview-size">${sizeMB} MB • Ready</div>
                    </div>
                `;
                card.appendChild(removeBtn);
                
                previewContainer.appendChild(card);
            });
        }

        function removeFile(indexToRemove) {
            const newDataTransfer = new DataTransfer();
            Array.from(dataTransfer.files).forEach((file, index) => {
                if (index !== indexToRemove) {
                    newDataTransfer.items.add(file);
                }
            });
            dataTransfer = newDataTransfer;
            input.files = dataTransfer.files;
            renderPreviews();
        }
    }

    setupMultiUploader('imagesInput', 'imagePreviewContainer');
    setupMultiUploader('newImagesInput', 'newImagePreviewContainer');
    setupMultiUploader('geoTaggedInput', 'geoPreviewContainer');
    setupMultiUploader('supportDocsInput', 'supportDocsPreviewContainer');
});
</script>
