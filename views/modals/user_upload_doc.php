<?php
// views/modals/user_upload_doc.php
require_once __DIR__ . '/../../db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<div class="modal" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="uploadForm" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="action" value="upload_doc">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="mb-3">
                    <label>Document (image/pdf)</label>
                    <input type="file" name="doc" accept="image/*,application/pdf" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
        </form>
    </div>
</div>
