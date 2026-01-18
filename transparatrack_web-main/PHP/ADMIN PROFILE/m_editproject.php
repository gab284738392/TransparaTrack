<?php
// m_editproject.php - edit modal updated to match add project layout
// IMPORTANT: PHP logic to populate these fields (e.g., fetching current project data) must be implemented 
// separately in adminprofile.php or a related script that handles the modal opening.
?>

<div id="editProjectModal" class="modal-overlay">
    <div class="modal-content">
    
        <div class="modal-header">
            <h2 class="modal-title">Update Project</h2>
            <button type="button" id="show-cancel-dialog-x" class="modal-close-btn">&times;</button>
        </div>

        <div class="modal-body">
            <form id="editProjectForm" action="edit_project_script.php" method="POST" class="modal-form" enctype="multipart/form-data">

                <div class="form-group form-grid-span-2">
                    <label for="edit_project_title">Project Title</label>
                    <input type="text" id="edit_project_title" name="project_title" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <!-- Changed to select dropdown -->
                        <select id="edit_status" name="status" required>
                            <option value="Not Started">Not Started</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Delayed">Delayed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_budget">Budget (₱)</label>
                        <!-- Changed to type="number" for numerical input only -->
                        <input type="number" id="edit_budget" name="budget" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="edit_start_date">Start Date</label>
                        <!-- Changed to type="date" for calendar input -->
                        <input type="date" id="edit_start_date" name="start_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_end_date">End Date</label>
                        <!-- Changed to type="date" for calendar input -->
                        <input type="date" id="edit_end_date" name="end_date">
                    </div>

                    <div class="form-group">
                        <label for="edit_category">Category</label>
                        <!-- Changed to select dropdown -->
                        <select id="edit_category" name="category" required>
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
                        <label for="edit_department">Department</label>
                        <!-- Changed to select dropdown -->
                        <select id="edit_department" name="department" required>
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
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description"></textarea>
                </div>

                <div class="form-group form-grid-span-2">
                    <label>Add Photos</label>
                    <div class="file-drop-zone" id="editPhotosDrop" data-type="photos">
                        Drag or Choose Images
                        <span class="file-info">(JPG, PNG only, max 5MB each)</span>
                        <input type="file" id="editPhotosInput" name="photos[]" accept="image/*" multiple  style="display:none;">
                    </div>
                    <div class="file-preview-grid" id="editPhotosPreview">
                    </div>
                </div>

                <div class="form-group form-grid-span-2">
                    <label>Add Attachments</label>
                    <div class="file-drop-zone" id="editAttachmentsDrop" data-type="attachments">
                        Drag or Choose Files
                        <span class="file-info">(PDF, DOCX, DOC, ZIP; max 10MB each)</span>
                        <input type="file" id="editAttachmentsInput" name="attachments[]" multiple style="display:none;">
                    </div>
                    <div class="file-preview-grid" id="editAttachmentsPreview">
                    </div>
                </div>

                <input type="hidden" id="edit_project_id" name="project_id">

                <div class="modal-footer">
                    <button type="button" id="show-cancel-dialog" class="modal-btn modal-btn-secondary">Cancel</button>
                    <button type="button" id="show-save-dialog" class="modal-btn modal-btn-primary">Save Changes</button>
                </div>

            </form>
        </div>
                
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('editProjectModal');
    const form = document.getElementById('editProjectForm');
    const saveBtn = document.getElementById('show-save-dialog');
    const cancelBtn = document.getElementById('show-cancel-dialog');
    const closeX = document.getElementById('show-cancel-dialog-x');

    const photosInput = document.getElementById('editPhotosInput');
    const attachmentsInput = document.getElementById('editAttachmentsInput');
    const photosPreview = document.getElementById('editPhotosPreview');
    const attachmentsPreview = document.getElementById('editAttachmentsPreview');
    const photosDrop = document.getElementById('editPhotosDrop');
    const attachmentsDrop = document.getElementById('editAttachmentsDrop');

    // Close handlers
    function closeModal() {
        if (modal) modal.style.display = 'none';
    }
    // Cancel should show central cancel dialog
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

    // Remove file by index from an input's FileList (returns new FileList)
    function removeFileFromInput(inputEl, removeIndex) {
        const oldFiles = Array.from(inputEl.files || []);
        oldFiles.splice(removeIndex, 1);
        const dt = filesToDataTransfer(oldFiles);
        inputEl.files = dt.files;
        return dt.files;
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
    function updatePreview(inputEl, previewContainer, type) {
        if (!previewContainer) return; // Exit if preview element doesn't exist
        previewContainer.innerHTML = ''; // clear
        const files = Array.from(inputEl.files || []);
        files.forEach((file, idx) => {
            const item = document.createElement('div');
            item.className = 'file-preview-item';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-file-btn';
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', function () {
                removeFileFromInput(inputEl, idx);
                updatePreview(inputEl, previewContainer, type);
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
     * Filters files for validity (type/size) and merges them into the input element.
     * @param {HTMLElement} inputEl - The file input element.
     * @param {FileList|File[]} filesList - The list of files to add/merge.
     * @param {string} type - 'photo' or 'attachment'.
     */
    function validateAndAddFiles(inputEl, filesList, type) {
        // Start with current accepted files in the input (if this is a drop event)
        const merged = Array.from(filesList || []);

        // Filter for validity
        const accepted = [];
        const rejected = [];
        merged.forEach(f => {
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

        // Put ACCEPTED files back into input (replaces previous files with accepted set)
        const dt = filesToDataTransfer(accepted);
        inputEl.files = dt.files;
        
        // Update preview
        if (type === 'photo') updatePreview(inputEl, photosPreview, 'photo');
        else updatePreview(inputEl, attachmentsPreview, 'attachment');
    }

    // FIX (Issue #2): The 'change' event listener is simplified to prevent double prompting
    if(photosInput) {
        photosInput.addEventListener('change', function () {
            validateAndAddFiles(photosInput, photosInput.files, 'photo');
        });
    }
    if(attachmentsInput) {
        attachmentsInput.addEventListener('change', function () {
            validateAndAddFiles(attachmentsInput, attachmentsInput.files, 'attachment');
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
                // For drop, we merge existing and dropped files manually.
                const existing = Array.from(inputEl.files || []);
                const dropFiles = Array.from(dtFiles);
                const merged = existing.concat(dropFiles);
                validateAndAddFiles(inputEl, merged, type);
            }
        });
    }
    setupDropZone(photosDrop, photosInput, 'photo');
    setupDropZone(attachmentsDrop, attachmentsInput, 'attachment');

    // Initialize (if input already has files, show previews)
    updatePreview(photosInput, photosPreview, 'photo');
    updatePreview(attachmentsInput, attachmentsPreview, 'attachment');

    // Save Changes: show central save dialog and submit on confirm
    saveBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        const titleEl = document.getElementById('edit_project_title');
        if (!titleEl || !titleEl.value.trim()) {
            // IMPORTANT: Using console.error instead of alert/confirm
            console.error('Project title is required.');
            return;
        }
        if (window.showSaveDialog) {
            window.showSaveDialog(function(){
                if(form) form.submit();
            });
        } else {
            // IMPORTANT: Using console.error instead of alert/confirm
            console.error('Save Confirmation required.');
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