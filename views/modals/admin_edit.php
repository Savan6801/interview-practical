<?php
// views/modals/admin_edit.php
require_once __DIR__ . '/../../db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$admin = null;
if ($id) {
    $stmt = $conn->prepare("SELECT id,name,email,image FROM admins WHERE id=?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $res = $stmt->get_result();
    $admin = $res->fetch_assoc();
    $stmt->close();
}
?>
<div class="modal" id="adminModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="adminForm" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Admin</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Image (required - upload to replace)</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                    <div class="mt-2">
                        Current:
                        <?php if (!empty($admin['image'])): ?>
                            <img src="http://localhost/interview-practical/uploads/admin/<?php echo $admin['id']; ?>/<?php echo $admin['image']; ?>" class="thumb">
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div>
