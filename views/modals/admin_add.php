<?php
// views/modals/admin_add.php
?>
<div class="modal" id="adminModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="adminForm" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Admin</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Image (required)</label>
                    <input type="file" name="image" accept="image/*" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>
x`