<?php
// api/user.php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/helper.php';

$action = $_POST['action'] ?? '';

if ($action === 'list') {
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';

    $where = "1";
    if ($search) {
        $s = "%$search%";
        $where = "(u.name LIKE '$s' OR u.email LIKE '$s' OR a.name LIKE '$s')";
    }

    $total = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
    $q = "SELECT u.id,u.name,u.email,u.image,u.doc,u.created_at,a.name as admin_name FROM users u LEFT JOIN admins a ON u.admin_id=a.id WHERE $where ORDER BY u.id DESC LIMIT $start,$length";
    $res = $conn->query($q);
    // filtered count:
    $filteredQ = $conn->query("SELECT COUNT(*) as cnt FROM users u LEFT JOIN admins a ON u.admin_id=a.id WHERE $where")->fetch_assoc()['cnt'];

    $data = [];
    while ($row = $res->fetch_assoc()) {
//        $imgPath = '../../uploads/user/' . $row['id'] . '/' . ($row['image'] ?: '');
        $imgPath = 'http://localhost/interview-practical/uploads/user/' . $row['id'] . '/' . $row['image'];
        $imgTag = '<img src="'.($row['image'] ? $imgPath : 'https://media.istockphoto.com/id/1337144146/vector/default-avatar-profile-icon-vector.jpg?s=612x612&w=0&k=20&c=BIbFwuv7FxTWvh5S3vB6bkT0Qv8Vn8N5Ffseq84ClGI=').'" class="thumb">';
        // doc type detection
        $doc_type = '';
        if ($row['doc']) {
            $ext = get_file_ext($row['doc']);
            if (in_array($ext, ['pdf'])) $doc_type = '<i class="bi bi-file-earmark-pdf-fill" title="PDF">PDF</i>';
            else $doc_type = '<i class="bi bi-image" title="Image">IMG</i>';
        } else {
            $doc_type = '';
        }
        $actions = '<button class="btn btn-sm btn-primary action-btn editUser" data-id="'.$row['id'].'">Edit</button>';
        $actions .= '<button class="btn btn-sm btn-danger action-btn deleteUser" data-id="'.$row['id'].'">Delete</button>';

        $data[] = [
            'id'=>$row['id'],
            'image'=>$imgTag,
            'name'=>htmlspecialchars($row['name']),
            'email'=>htmlspecialchars($row['email']),
            'admin_name'=>htmlspecialchars($row['admin_name']),
            'doc_type'=>$doc_type,
            'created_at'=>$row['created_at'],
            'actions'=>$actions
        ];
    }

    echo json_encode([
        "draw"=>$draw,
        "recordsTotal"=>$total,
        "recordsFiltered"=>$filteredQ,
        "data"=>$data
    ]);
    exit;
}

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $admin_id = intval($_POST['admin_id'] ?? 0);
    if (!$name || !$email || !$admin_id || !isset($_FILES['image'])) {
        echo json_encode(['success'=>false,'error'=>'Missing required fields']); exit;
    }
    // insert user with blank image to reserve id
    $stmt = $conn->prepare("INSERT INTO users (admin_id,name,email,image) VALUES (?,?,?, '')");
    $stmt->bind_param('iss', $admin_id, $name, $email);
    if (!$stmt->execute()) { echo json_encode(['success'=>false,'error'=>$conn->error]); exit; }
    $user_id = $stmt->insert_id;
    $stmt->close();

    // upload image
    $uploadDir = __DIR__ . '/../uploads/user/' . $user_id;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $f = $_FILES['image'];
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $filename = safe_filename(time() . "_user." . $ext);
    $target = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($f['tmp_name'], $target)) {
        echo json_encode(['success'=>false,'error'=>'Upload failed']); exit;
    }
    $stmt = $conn->prepare("UPDATE users SET image=? WHERE id=?");
    $stmt->bind_param('si', $filename, $user_id);
    $stmt->execute();

    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $admin_id = intval($_POST['admin_id'] ?? 0); // but will be present as hidden when editing
    if (!$id || !$name || !$email) { echo json_encode(['success'=>false,'error'=>'Missing']); exit; }
    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->bind_param('ssi', $name, $email, $id);
    if (!$stmt->execute()) { echo json_encode(['success'=>false,'error'=>$conn->error]); exit; }
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $f = $_FILES['image'];
        $uploadDir = __DIR__ . '/../uploads/user/' . $id;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $filename = safe_filename(time() . "_user." . $ext);
        $target = $uploadDir . '/' . $filename;
        if (move_uploaded_file($f['tmp_name'], $target)) {
            // delete old image
            $old = $conn->query("SELECT image FROM users WHERE id=$id")->fetch_assoc()['image'];
            if ($old && file_exists($uploadDir . '/' . $old)) @unlink($uploadDir . '/' . $old);
            $stmt = $conn->prepare("UPDATE users SET image=? WHERE id=?");
            $stmt->bind_param('si', $filename, $id);
            $stmt->execute();
        }
    }
    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid id']); exit; }
    // remove files
    $dir = __DIR__ . '/../uploads/user/' . $id;
    if (is_dir($dir)) {
        // remove images
        $files = glob($dir . '/*');
        foreach($files as $f) if(is_file($f)) @unlink($f);
        // remove docs
        $docDir = $dir . '/docs';
        if (is_dir($docDir)) {
            $docs = glob($docDir . '/*');
            foreach($docs as $d) if(is_file($d)) @unlink($d);
            @rmdir($docDir);
        }
        @rmdir($dir);
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param('i',$id);
    if ($stmt->execute()) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}

if ($action === 'upload_doc') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id || !isset($_FILES['doc'])) { echo json_encode(['success'=>false,'error'=>'Missing']); exit; }
    $f = $_FILES['doc'];
    $uploadDir = __DIR__ . '/../uploads/user/' . $id . '/docs';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $filename = safe_filename(time() . "_doc." . $ext);
    $target = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($f['tmp_name'], $target)) { echo json_encode(['success'=>false,'error'=>'Upload failed']); exit; }
    // update db - store last uploaded doc name (overwrites)
    $stmt = $conn->prepare("UPDATE users SET doc=? WHERE id=?");
    $stmt->bind_param('si', $filename, $id);
    $stmt->execute();
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Invalid action']);
