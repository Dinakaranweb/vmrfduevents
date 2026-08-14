        // Registrar Modal Handler
        const submitRegistrarModalEl = document.getElementById('submitRegistrarModal');
        if (submitRegistrarModalEl) {
            submitRegistrarModalEl.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const title = button.getAttribute('data-bs-title') || '';
                const caption = button.getAttribute('data-bs-caption') || '';
                const imgCount = parseInt(button.getAttribute('data-bs-images-count') || '0', 10);

                const form = document.getElementById('registrarSubmitForm');
                form.action = `/media-editor/${id}/submit-registrar`;
                document.getElementById('regModalTitle').textContent = title;
                document.getElementById('regModalCaption').value = caption;

                const photoLabel = document.getElementById('regPhotoLabel');
                const photoInput = document.getElementById('regPhotoInput');
                const photoWarning = document.getElementById('regPhotoWarning');
            });

            const regForm = document.getElementById('registrarSubmitForm');
            if (regForm) {
                regForm.addEventListener('submit', async function(e) {
                    if (regForm.dataset.submitting === "true") {
                        e.preventDefault();
                        return;
                    }
                    e.preventDefault();
                    const regPhotoInput = document.getElementById('regPhotoInput');
                    const success = await window.VMRFDU_ImageOptimizer.optimizeFileList(
                        regPhotoInput,
                        'regUploadProgressBanner',
                        'regUploadProgressText',
                        'regUploadProgressBar',
                        'regSubmitBtn'
                    );
                    if (success) {
                        regForm.dataset.submitting = "true";
                        regForm.submit();
                    }
                });
            }
        }

        // Publish Modal Handler
        const publishModalEl = document.getElementById('publishModal');
        if (publishModalEl) {
            publishModalEl.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');

                const form = document.getElementById('publishForm');
                form.action = `/media-editor/${id}/publish`;
                
                // Reset form state on open
                form.reset();
                document.querySelectorAll('.url-field-group').forEach(el => el.classList.add('d-none'));
                document.querySelectorAll('.url-field-group input').forEach(el => el.required = false);
            });

            // Handle dynamic URL fields
            const platformMap = {
                'Instagram': { group: 'groupInsta', input: 'urlInsta' },
                'Facebook': { group: 'groupFb', input: 'urlFb' },
                'LinkedIn': { group: 'groupLinked', input: 'urlLinked' },
                'X (Twitter)': { group: 'groupX', input: 'urlX' }
            };

            document.querySelectorAll('.platform-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const mapping = platformMap[this.value];
                    if (mapping) {
                        const groupEl = document.getElementById(mapping.group);
                        const inputEl = document.getElementById(mapping.input);
                        
                        if (this.checked) {
                            groupEl.classList.remove('d-none');
                            inputEl.required = true;
                        } else {
                            groupEl.classList.add('d-none');
                            inputEl.required = false;
                            inputEl.value = ''; // clear value if unchecked
                        }
                    }
                });
            });
        }

        // AI Phrasing Generator
        const editorAiBtn = document.getElementById('editorGenerateAiBtn');
        if (editorAiBtn) {
            editorAiBtn.addEventListener('click', function() {
                const title = document.getElementById('regModalTitle').textContent || "Institutional Activity";
                const captionBox = document.getElementById('regModalCaption');
                editorAiBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
                editorAiBtn.disabled = true;

                fetch("{{ route('api.ai.captions') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ title: title, category: "Institutional Event", college: "VMRF(DU)", tags: "Excellence, Broadcast" })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.caption) {
                        captionBox.value = data.caption;
                    } else if (data.captions) {
                        captionBox.value = "📱 INSTAGRAM SUGGESTION:\n" + (data.captions.instagram || '') + "\n\n----------------------------------------\n\n💼 LINKEDIN SUGGESTION:\n" + (data.captions.linkedin || '');
                    }
                })
                .catch(err => {
                    captionBox.value = `✨ Official Highlights: "${title}" organized at VMRF(DU).\n\n#VMRFDU #UniversityUpdate #Excellence`;
                })
                .finally(() => {
                    editorAiBtn.innerHTML = '<i class="bi bi-magic"></i> AI Generate Phrasing';
                    editorAiBtn.disabled = false;
                });
            });
        }
