<?php
// This is m_dialogs.php
// NOW CONTAINS ALL THREE DIALOGS
?>

<div id="saveDialog" class="dialog-overlay">
    <div class="dialog-content">
        <h2 class="dialog-title">
            Do you want to <strong>save</strong> the changes you made?
        </h2>
        <div class="dialog-buttons">
            <button id="dialog-save-confirm" class="dialog-btn dialog-btn-confirm">Yes</button>
            <button id="dialog-save-cancel" class="dialog-btn dialog-btn-cancel">Cancel</button>
        </div>
    </div>
</div>


<div id="publishDialog" class="dialog-overlay">
    <div class="dialog-content">
        <h2 class="dialog-title">
            Are you sure you want to <strong>publish</strong> this project?
        </h2>
        <p class="dialog-subtitle">This will make it visible to others.</p>
        
        <div class="dialog-buttons">
            <button id="dialog-publish-confirm" class="dialog-btn dialog-btn-confirm">Yes</button>
            <button id="dialog-publish-cancel" class="dialog-btn dialog-btn-cancel">Cancel</button>
        </div>
    </div>
</div>


<div id="cancelDialog" class="dialog-overlay">
    <div class="dialog-content">
        <h2 class="dialog-title">
            Are you sure you want to <strong>cancel</strong>?
        </h2>
        <p class="dialog-subtitle">Unsaved changes will be lost.</p>
        <div class="dialog-buttons">
            <button id="dialog-cancel-confirm" class="dialog-btn dialog-btn-confirm">Yes</button>
            <button id="dialog-cancel-cancel" class="dialog-btn dialog-btn-cancel">Cancel</button>
        </div>
    </div>
</div>

<script>
// Centralized dialog helpers. Other modals call window.showSaveDialog/onConfirm etc.
(function(){
    function setupDialog(id){
        const el = document.getElementById(id);
        if(!el) return null;
        const confirmBtn = el.querySelector('.dialog-btn-confirm');
        const cancelBtn = el.querySelector('.dialog-btn-cancel');
        return {el, confirmBtn, cancelBtn};
    }

    const save = setupDialog('saveDialog');
    const publish = setupDialog('publishDialog');
    const cancel = setupDialog('cancelDialog');

    function showDialog(dialogObj, onConfirm){
        if(!dialogObj) return;
        const {el, confirmBtn, cancelBtn} = dialogObj;
        el.style.display = 'flex';

        function cleanup(){
            el.style.display = 'none';
            confirmBtn.removeEventListener('click', onClick);
            cancelBtn.removeEventListener('click', onCancel);
        }

        function onClick(e){
            e.preventDefault();
            cleanup();
            if(typeof onConfirm === 'function') onConfirm();
        }
        function onCancel(e){
            e.preventDefault();
            cleanup();
        }

        confirmBtn.addEventListener('click', onClick);
        cancelBtn.addEventListener('click', onCancel);
    }

    window.showSaveDialog = function(onConfirm){ showDialog(save, onConfirm); };
    window.showPublishDialog = function(onConfirm){ showDialog(publish, onConfirm); };
    window.showCancelDialog = function(onConfirm){ showDialog(cancel, onConfirm); };
})();
</script>