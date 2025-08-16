<?php
// views/modals/user_edit.php
require_once __DIR__ . '/../../db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;
if ($id) {
    $stmt = $conn->prepare("SELECT u.*, a.name as admin_name FROM users u LEFT JOIN admins a ON u.admin_id=a.id WHERE u.id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
}
$admins = $conn->query("SELECT id,name FROM admins ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<div class="modal" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="userForm" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <div class="mb-3">
                    <label>Name</label><input type="text" name="name" class="form-control"
                                              value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Email</label><input type="email" name="email" class="form-control"
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Admin (read-only)</label>
                    <select name="admin_id" class="form-control" required disabled>
                        <?php foreach ($admins as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $a['id'] == $user['admin_id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="admin_id" value="<?= $user['admin_id'] ?>">
                </div>
                <div class="mb-3">
                    <label>Image (required - upload to replace)</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                    <!--                    <div class="mt-2">-->
                    <!--                        Current: -->
                    <?php //if($user['image']): ?><!--<img src="http://localhost/interview-practical/uploads/user/' . $row['id'] . '/' . $row['image']" class="thumb">--><?php //endif; ?>
                    <!--                    </div>-->
                    <div class="mt-2">
                        Current:
                        <?php if (!empty($user['image'])): ?>
                            <img src="http://localhost/interview-practical/uploads/user/<?php echo $user['id']; ?>/<?php echo $user['image']; ?>"
                                 class="thumb">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update User</button>
            </div>
        </form>
    </div>
</div>
