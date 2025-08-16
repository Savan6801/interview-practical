<?php
// views/modals/user_add.php
require_once __DIR__ . '/../../db.php';
$admins = $conn->query("SELECT id,name FROM admins ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<div class="modal" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="userForm" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label>Name</label><input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label><input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Admin (required)</label>
                    <select name="admin_id" class="form-control" required>
                        <option value="">-- Select Admin --</option>
                        <?php foreach($admins as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Image (required)</label>
                    <input type="file" name="image" accept="image/*" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">Save User</button></div>
        </form>
    </div>
</div>
