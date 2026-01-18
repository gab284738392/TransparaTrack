<?php
// m_addproject.php - modal HTML for adding a project
// Form posts to add_project_script.php and shows a confirmation when Publish is clicked.
?>

<div id="addProjectModal" class="modal-overlay">
    <div class="modal-content">
    
        <div class="modal-header">
            <h2 class="modal-title">Add New Project</h2>
            <button type="button" id="add-show-cancel-dialog-x" class="modal-close-btn">&times;</button>
        </div>

        <div class="modal-body">
            <form id="addProjectForm" action="add_project_script.php" method="POST" class="modal-form" enctype="multipart/form-data">

                <div class="form-group form-grid-span-2">
                    <label for="add_project_title">Project Title</label>
                    <input type="text" id="add_project_title" name="project_title" placeholder="Enter project title" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_status">Status</label>
                        <select id="add_status" name="status" required>
                            <option value="" disabled selected>Select status</option>
                            <option value="Not Started">Not Started</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Delayed">Delayed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_budget">Budget (₱)</label>
                        <!-- Changed to type="number" for numerical input only -->
                        <input type="number" id="add_budget" name="budget" placeholder="Enter budget amount" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="add_start_date">Start Date</label>
                        <!-- Changed to type="date" for calendar input -->
                        <input type="date" id="add_start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_end_date">End Date</label>
                        <!-- Changed to type="date" for calendar input -->
                        <input type="date" id="add_end_date" name="end_date">
                    </div>

                    <div class="form-group">
                        <label for="add_category">Category</label>
                        <select id="add_category" name="category" required>
                            <option value="" disabled selected>Select category</option>
                            <option value="Imprastraktura at Pampublikong Gawa">Imprastraktura at Pampublikong Gawa (Infrastructure)</option>
                            <option value="Kalusugan, Nutrisyon, at Serbisyo Sosyal">Kalusugan, Nutrisyon, at Serbisyo Sosyal (Health & Social Services)</option>
                            <option value="Kapayapaan, Kaayusan, at Pampublikong Kaligtasan">Kapayapaan, Kaayusan, at Pampublikong Kaligtasan (Peace, Order & Public Safety)</option>
                            <option value="Paghahanda at Pagtugon sa Sakuna">Paghahanda at Pagtugon sa Sakuna (Disaster Risk Reduction - DRRM)</option>
                            <option value="Pamamahala sa Kapaligiran">Pamamahala sa Kapaligiran (Environmental Management)</option>
                            <option value="Pangkabuhayan at Pagpapaunlad ng Ekonomiya">Pangkabuhayan at Pagpapaunlad ng Ekonomiya (Livelihood & Economic Dev't)</option>
                            <option value="Kabataan at Pagpapaunlad ng Sports">Kabataan at Pagpapaunlad ng Sports (SK Projects)</option>
                            <option value="Pamamahala at Operasyon">Pamamahala at Operasyon (Governance & Admin)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="add_department">Department</label>
                        <select id="add_department" name="department" required>
                            <option value="" disabled selected>Select department</option>
                            <option value="Punong Barangay">Punong Barangay</option>
                            <option value="Sangguniang Barangay">Sangguniang Barangay</option>
                            <option value="Barangay Development Council (BDC)">Barangay Development Council (BDC)</option>
                            <option value="Barangay Peace and Order Committee (BPOC)">Barangay Peace and Order Committee (BPOC)</option>
                            <option value="Barangay Disaster Risk Reduction and Management Committee (BDRRMC)">Barangay Disaster Risk Reduction and Management Committee (BDRRMC)</option>
                            <option value="Barangay Anti-Drug Abuse Council (BADAC)">Barangay Anti-Drug Abuse Council (BADAC)</option>
                            <option value="Barangay Council for the Protection of Children (BCPC)">Barangay Council for the Protection of Children (BCPC)</option>
                            <option value="Barangay Ecological Solid Waste Management Committee (BESWMC)">Barangay Ecological Solid Waste Management Committee (BESWMC)</option>
                            <option value="Lupon Tagapamayapa (Barangay Justice System)">Lupon Tagapamayapa (Barangay Justice System)</option>
                            <option value="Barangay Health Workers (BHWs)">Barangay Health Workers (BHWs)</option>
                            <option value="Barangay Public Safety Officers (BPSO)">Barangay Public Safety Officers (BPSO)</option>
                            <option value="Committee on Health and Sanitation">Committee on Health and Sanitation</option>
                            <option value="Committee on Livelihood and Cooperatives">Committee on Livelihood and Cooperatives</option>
                            <option value="Committee on Infrastructure">Committee on Infrastructure</option>
                            <option value="Committee on Rules and Ordinances">Committee on Rules and Ordinances</option>
                        </select>
                    </div>
                </div>

                <div class="form-group form-grid-span-2">
                    <label for="add_description">Description</label>
                    <textarea id="add_description" name="description" placeholder="Enter project description"></textarea>
                </div>

                <div class="form-group form-grid-span-2">
                    <label>Add Photos</label>
                    <div class="file-drop-zone" id="photosDrop" data-type="photos">
                        Drag or Choose Images
                        <span class="file-info">(JPG, PNG only, max 5MB each)</span>
                        <input type="file" id="photosInput" name="photos[]" accept="image/*" multiple style="display: none;"> 
                    </div>
                    <div class="file-preview-grid" id="photosPreview">
                    </div>
                </div>

                <div class="form-group form-grid-span-2">
                    <label>Add Attachments</label>
                    <div class="file-drop-zone" id="attachmentsDrop" data-type="attachments">
                        Drag or Choose Files
                        <span class="file-info">(PDF, DOCX, DOC, ZIP; max 10MB each)</span>
                        <input type="file" id="attachmentsInput" name="attachments[]" multiple style="display: none;">
                    </div>
                    <div class="file-preview-grid" id="attachmentsPreview">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="add-show-cancel-dialog" class="modal-btn modal-btn-secondary">Cancel</button>
                    <button type="button" id="add-show-publish-dialog" class="modal-btn modal-btn-primary">Publish</button>
                </div>

            </form>
        </div>
                
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('addProjectModal');
    const form = document.getElementById('addProjectForm');
    const publishBtn = document.getElementById('add-show-publish-dialog');
    const cancelBtn = document.getElementById('add-show-cancel-dialog');
    const closeX = document.getElementById('add-show-cancel-dialog-x');

    const photosInput = document.getElementById('photosInput');
    const attachmentsInput = document.getElementById('attachmentsInput');
    const photosPreview = document.getElementById('photosPreview');
    const attachmentsPreview = document.getElementById('attachmentsPreview');
    const photosDrop = document.getElementById('photosDrop');
    const attachmentsDrop = document.getElementById('attachmentsDrop');

    // Arrays to hold accepted files (to accumulate across selections/drops)
    let acceptedPhotos = [];
    let acceptedAttachments = [];

    // Close handlers (adapt to your app's modal logic)
    function closeModal() {
        if (modal) modal.style.display = 'none';
    }
    // When user clicks Cancel, show central cancel dialog first
    cancelBtn?.addEventListener('click', function(e){
        e.preventDefault();
        if (window.showCancelDialog) {
            window.showCancelDialog(function(){
                closeModal();
            });
        } else {
            closeModal();
        }
    });
    // X closes immediately
    closeX?.addEventListener('click', closeModal);

    // Utility: create DataTransfer from array of Files (to update <input>.files)
    function filesToDataTransfer(fileArray) {
        const dt = new DataTransfer();
        fileArray.forEach(f => dt.items.add(f));
        return dt;
    }

    // Remove file by index from accepted array and update input/preview
    function removeFile(type, removeIndex) {
        if (type === 'photo') {
            acceptedPhotos.splice(removeIndex, 1);
            const dt = filesToDataTransfer(acceptedPhotos);
            photosInput.files = dt.files;
            updatePreview(acceptedPhotos, photosPreview, 'photo');
        } else {
            acceptedAttachments.splice(removeIndex, 1);
            const dt = filesToDataTransfer(acceptedAttachments);
            attachmentsInput.files = dt.files;
            updatePreview(acceptedAttachments, attachmentsPreview, 'attachment');
        }
    }

    // Format bytes
    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Update preview for images or attachments
    function updatePreview(fileArray, previewContainer, type) {
        if (!previewContainer) return; // Exit if preview element doesn't exist
        previewContainer.innerHTML = ''; // clear
        fileArray.forEach((file, idx) => {
            const item = document.createElement('div');
            item.className = 'file-preview-item';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-file-btn';
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', function () {
                removeFile(type, idx);
            });

            if (type === 'photo' && file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'preview-thumb';
                img.alt = file.name;
                // create object URL for immediate preview
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);
                const caption = document.createElement('div');
                caption.className = 'file-caption';
                caption.textContent = file.name + ' • ' + formatBytes(file.size);

                item.appendChild(img);
                item.appendChild(caption);
                item.appendChild(removeBtn);
            } else {
                // attachment preview (icon + name)
                const icon = document.createElement('div');
                icon.className = 'file-icon';
                icon.textContent = '📎';
                const caption = document.createElement('div');
                caption.className = 'file-caption';
                caption.textContent = file.name + ' • ' + formatBytes(file.size);

                item.appendChild(icon);
                item.appendChild(caption);
                item.appendChild(removeBtn);
            }
            previewContainer.appendChild(item);
        });
    }

    // Basic allowed type/size checks on client (not a replacement for server validation)
    const allowedAttachmentTypes = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
        'application/msword',
        'application/zip',
        'application/x-zip-compressed'
    ];
    const maxPhotoSize = 5 * 1024 * 1024; // 5MB
    const maxAttachmentSize = 10 * 1024 * 1024; // 10MB

    /**
     * Validates and adds files to the accepted array, updates input.files and preview.
     * @param {FileList|File[]} filesList - The list of files to add.
     * @param {string} type - 'photo' or 'attachment'.
     */
    function validateAndAddFiles(filesList, type) {
        const newFiles = Array.from(filesList || []);
        const accepted = [];
        const rejected = [];
        newFiles.forEach(f => {
            if (type === 'photo') {
                if (!f.type.startsWith('image/')) {
                    rejected.push(f.name + ' (not an image)');
                    return;
                }
                if (f.size > maxPhotoSize) {
                    rejected.push(f.name + ' (too large)');
                    return;
                }
            } else {
                // attachments
                if (allowedAttachmentTypes.indexOf(f.type) === -1) {
                    const name = f.name.toLowerCase();
                    if (!(/\.(pdf|docx?|zip)$/).test(name)) {
                        rejected.push(f.name + ' (unsupported type)');
                        return;
                    }
                }
                if (f.size > maxAttachmentSize) {
                    rejected.push(f.name + ' (too large)');
                    return;
                }
            }
            accepted.push(f);
        });

        if (rejected.length) {
            // IMPORTANT: Using console.error instead of alert/confirm
            console.error('Files rejected:', rejected.join('\n'));
        }

        // Add accepted files to the array
        if (type === 'photo') {
            acceptedPhotos = acceptedPhotos.concat(accepted);
            const dt = filesToDataTransfer(acceptedPhotos);
            photosInput.files = dt.files;
            updatePreview(acceptedPhotos, photosPreview, 'photo');
        } else {
            acceptedAttachments = acceptedAttachments.concat(accepted);
            const dt = filesToDataTransfer(acceptedAttachments);
            attachmentsInput.files = dt.files;
            updatePreview(acceptedAttachments, attachmentsPreview, 'attachment');
        }
    }

    // Event listeners for file inputs
    if (photosInput) {
        photosInput.addEventListener('change', function () {
            validateAndAddFiles(photosInput.files, 'photo');
        });
    }
    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function () {
            validateAndAddFiles(attachmentsInput.files, 'attachment');
        });
    }

    // Drop zone handlers (both for photos and attachments)
    function setupDropZone(zoneEl, inputEl, type) {
        if (!zoneEl || !inputEl) return; // Exit if elements don't exist
        
        // Ensure click handler is simple
        zoneEl.addEventListener('click', () => inputEl.click());
        
        zoneEl.addEventListener('dragover', (e) => {
            e.preventDefault();
            zoneEl.classList.add('drag-over');
        });
        zoneEl.addEventListener('dragleave', () => {
            zoneEl.classList.remove('drag-over');
        });
        zoneEl.addEventListener('drop', (e) => {
            e.preventDefault();
            zoneEl.classList.remove('drag-over');
            const dtFiles = e.dataTransfer.files;
            if (dtFiles && dtFiles.length) {
                validateAndAddFiles(dtFiles, type);
            }
        });
    }
    setupDropZone(photosDrop, photosInput, 'photo');
    setupDropZone(attachmentsDrop, attachmentsInput, 'attachment');

    // Initialize previews (in case of pre-loaded files, but here empty)
    updatePreview(acceptedPhotos, photosPreview, 'photo');
    updatePreview(acceptedAttachments, attachmentsPreview, 'attachment');

    // Publish: open central publish dialog and submit on confirm
    publishBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        const titleEl = document.getElementById('add_project_title');
        if (!titleEl || !titleEl.value.trim()) {
            // IMPORTANT: Using console.error instead of alert/confirm
            console.error('Project title is required.');
            return;
        }
        if (window.showPublishDialog) {
            window.showPublishDialog(function(){
                if(form) form.submit();
            });
        } else {
            // IMPORTANT: Using console.error instead of alert/confirm
            console.error('Publish Confirmation required.');
            form?.submit();
        }
    });
})();
</script>

<style>
/* Minimal styles for preview items - adapt to your app's CSS */
.file-preview-grid {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 8px;
}
.file-preview-item {
    position: relative;
    width: 120px;
    border: 1px solid #e0e0e0;
    padding: 6px;
    border-radius: 4px;
    background: #fff;
    text-align: center;
}
.preview-thumb {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 2px;
    background: #f8f8f8;
}
.file-caption {
    font-size: 12px;
    margin-top: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.remove-file-btn {
    position: absolute;
    top: 2px;
    right: 4px;
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
}
.file-icon {
    font-size: 40px;
}
.file-drop-zone {
    border: 2px dashed #ddd;
    padding: 12px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-block;
}
.file-drop-zone.drag-over {
    border-color: #3b82f6;
    background: #f0f8ff;
}
</style>            