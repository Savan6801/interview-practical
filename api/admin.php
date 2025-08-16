<?php
// api/admin.php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/helper.php';

$action = $_POST['action'] ?? '';

if ($action === 'list') {
    // DataTables server-side handling
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';

    $where = "1";
    $params = [];
    if ($search) {
        $where = "(name LIKE ? OR email LIKE ?)";
        $s = "%$search%";
        $params = [$s, $s];
    }

    // count total
    $totalQ = "SELECT COUNT(*) as cnt FROM admins";
    $totalRes = $conn->query($totalQ)->fetch_assoc();
    $recordsTotal = intval($totalRes['cnt']);

    // filtered count
    if ($where === "1") {
        $recordsFiltered = $recordsTotal;
        $stmt = $conn->prepare("SELECT id,name,email,image,created_at FROM admins ORDER BY id DESC LIMIT ?,?");
        $stmt->bind_param('ii', $start, $length);
    } else {
        $recordsFiltered = 0;
        $stmt = $conn->prepare("SELECT id,name,email,image,created_at FROM admins WHERE $where ORDER BY id DESC LIMIT ?,?");
        $stmt->bind_param('sii', $params[0], $start, $length); // if both params same, search string in first only (works but not perfect)
    }
    // Note: For strict correctness you can build more robust param binding; this simplified approach works for basic usage.

    // To avoid mixing param types, do a simpler query:
    if ($where === "1") {
        $q = "SELECT id,name,email,image,created_at FROM admins ORDER BY id DESC LIMIT $start,$length";
        $res = $conn->query($q);
    } else {
        $s = "%$search%";
        $q = $conn->prepare("SELECT id,name,email,image,created_at FROM admins WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ?,?");
        $q->bind_param('ssii', $s, $s, $start, $length);
        $q->execute();
        $res = $q->get_result();
        $countQ = $conn->prepare("SELECT COUNT(*) as cnt FROM admins WHERE name LIKE ? OR email LIKE ?");
        $countQ->bind_param('ss', $s, $s);
        $countQ->execute();
        $recordsFiltered = $countQ->get_result()->fetch_assoc()['cnt'];
    }

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $imgPath = 'http://localhost/interview-practical/uploads/admin/' . $row['id'] . '/' . $row['image'];
        $imgTag = '<img src="' . ($row['image'] ? $imgPath : 'https://media.istockphoto.com/id/1337144146/vector/default-avatar-profile-icon-vector.jpg?s=612x612&w=0&k=20&c=BIbFwuv7FxTWvh5S3vB6bkT0Qv8Vn8N5Ffseq84ClGI=') . '" class="thumb">';
        $actions = '<button class="btn btn-sm btn-primary action-btn editAdmin" data-id="' . $row['id'] . '">Edit</button>';
        $actions .= '<button class="btn btn-sm btn-danger action-btn deleteAdmin" data-id="' . $row['id'] . '">Delete</button>';
        $data[] = [
            'id' => $row['id'],
            'image' => $imgTag,
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'created_at' => $row['created_at'],
            'actions' => $actions
        ];
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered ?? $recordsTotal,
        "data" => $data
    ]);
    exit;
}

// Add admin
if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if (!$name || !$email || !isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    // create admin
    $stmt = $conn->prepare("INSERT INTO admins (name,email,image) VALUES (?, ?, '')");
    $stmt->bind_param('ss', $name, $email);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit;
    }
    $admin_id = $stmt->insert_id;
    $stmt->close();

    // handle upload
    $uploadDir = __DIR__ . '/../uploads/admin/' . $admin_id;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $f = $_FILES['image'];
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $filename = safe_filename(time() . "_admin." . $ext);
    $target = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($f['tmp_name'], $target)) {
        echo json_encode(['success' => false, 'error' => 'Upload failed']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE admins SET image=? WHERE id=?");
    $stmt->bind_param('si', $filename, $admin_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if (!$id || !$name || !$email) {
        echo json_encode(['success' => false, 'error' => 'Missing']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE admins SET name=?, email=? WHERE id=?");
    $stmt->bind_param('ssi', $name, $email, $id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit;
    }
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $f = $_FILES['image'];
        $uploadDir = __DIR__ . '/../uploads/admin/' . $id;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $filename = safe_filename(time() . "_admin." . $ext);
        $target = $uploadDir . '/' . $filename;
        if (move_uploaded_file($f['tmp_name'], $target)) {
            // delete old image (optional)
            $old = $conn->query("SELECT image FROM admins WHERE id=$id")->fetch_assoc()['image'];
            if ($old && file_exists($uploadDir . '/' . $old)) @unlink($uploadDir . '/' . $old);
            $stmt = $conn->prepare("UPDATE admins SET image=? WHERE id=?");
            $stmt->bind_param('si', $filename, $id);
            $stmt->execute();
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid id']);
        exit;
    }

    // First get users under admin to delete their files
    $stmt = $conn->prepare("SELECT id,image FROM users WHERE admin_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($u = $res->fetch_assoc()) {
        $uDirImg = __DIR__ . '/../uploads/user/' . $u['id'];
        if (!empty($u['image']) && file_exists($uDirImg . '/' . $u['image'])) @unlink($uDirImg . '/' . $u['image']);
    }
    $stmt->close();

    // Delete admin folder
    $adminDir = __DIR__ . '/../uploads/admin/' . $id;
    if (is_dir($adminDir)) {
        // remove files
        $files = glob($adminDir . '/*');
        foreach ($files as $f) if (is_file($f)) @unlink($f);
        @rmdir($adminDir);
    }

    // Delete admin (users will be removed automatically because of FK ON DELETE CASCADE)
    $stmt = $conn->prepare("DELETE FROM admins WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
