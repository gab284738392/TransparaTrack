<?php
// This is m_profileedit.php
// It contains *only* the HTML for the modal.
// It relies on variables from adminprofile.php ($profileImagePath, $loggedInFullName, etc.)
?>

<div id="profileEditModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 800px;"> 
    
        <div class="modal-header">
            <h2 class="modal-title">Update Profile</h2>
            <button type="button" id="profile-show-cancel-dialog-x" class="modal-close-btn">&times;</button>
        </div>

        <div class="modal-body">
            
            <form id="profileEditForm" action="update_profile_script.php" method="POST" class="modal-form profile-edit-layout" enctype="multipart/form-data">
                
                <input type="hidden" id="current_profile_image_path" name="current_profile_image_path" value="<?= $userData['ProfileImagePath'] ?? '' ?>">

                <div class="profile-pic-controls">
                    <div class="profile-pic-preview">
                        <img src="<?= $profileImagePath ?>" alt="Profile Picture" id="profilePreviewImage">
                    </div>
                    
                    <input type="file" id="profilePicInput" name="profile_pic" accept="image/jpeg, image/png" style="display: none;">
                    <label for="profilePicInput" class="modal-btn btn-upload">Upload</label>
                    <button type="button" class="modal-btn btn-remove" id="profileRemoveBtn">Remove</button>
                </div>

                <div class="profile-form-fields">
                    <div class="form-group">
                        <label for="profile_name">Name</label>
                        <input type="text" id="profile_name" name="profile_name" value="<?= $loggedInFullName ?>">
                    </div>
                    <div class="form-group">
                        <label for="profile_username">Username</label>
                        <input type="text" id="profile_username" name="profile_username" value="<?= $loggedInUsername ?>">
                    </div>
                    <div class="form-group">
                        <label for="profile_email">Email</label>
                        <input type="email" id="profile_email" name="profile_email" value="<?= $loggedInEmail ?>">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" id="profile-show-cancel-dialog" class="modal-btn modal-btn-secondary">Cancel</button>
            <button type="button" id="profile-show-save-dialog" class="modal-btn modal-btn-primary">Save Changes</button>
        </div>
        
    </div>
</div>

<style>
.profile-edit-layout {
    display: flex;
    flex-direction: row;
    gap: 30px;
    /* Ensure the layout itself is aligned to the start */
    align-items: flex-start; 
}
.profile-pic-controls {
    display: flex;
    flex-direction: column; /* Stack items vertically */
    align-items: center;    /* Center items horizontally */
    width: 150px;           
    flex-shrink: 0;         
}
.profile-pic-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #f0f0f0;
    margin-bottom: 15px; 
    display: flex;
    justify-content: center;
    align-items: center;
}
.profile-pic-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover; 
}
.profile-form-fields {
    flex-grow: 1;
}

/* --- START OF BUTTON FIX --- */
/* Target the buttons *inside* the controls */
.profile-pic-controls .modal-btn {
    width: 100%;            /* Make buttons fill the 150px column */
    text-align: center;
    box-sizing: border-box; 
    margin-bottom: 10px;
    
    /* Override any weird alignment properties */
    margin-left: 0;
    margin-right: 0;
    padding-left: 10px; /* Add some padding */
    padding-right: 10px;
}

/* Specific fix for the Upload <label> to look like a button */
.profile-pic-controls .btn-upload {
    display: inline-block; /* Treat label like a block */
    line-height: 1.5;     /* Adjust line height for vertical centering */
    padding-top: 6px;     /* Adjust padding to match button height */
    padding-bottom: 6px;
}
/* --- END OF BUTTON FIX --- */
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure we are not running this script multiple times
    if (window.profileEditScriptLoaded) return;
    window.profileEditScriptLoaded = true;

    const fileInput = document.getElementById('profilePicInput');
    const previewImage = document.getElementById('profilePreviewImage');
    const removeBtn = document.getElementById('profileRemoveBtn');
    const currentImagePathInput = document.getElementById('current_profile_image_path');
    
    const defaultProfilePicPath = '<?= $base_web_path ?>assets/adminpic.svg';

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    currentImagePathInput.value = '__NEW_FILE_UPLOADED__';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            previewImage.src = defaultProfilePicPath; 
            fileInput.value = ''; 
            currentImagePathInput.value = '__REMOVE_IMAGE__';
        });
    }
});
</script>