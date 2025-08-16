<?php
include "db/conn.php";

$action = $_GET['action'] ?? '';

if ($action == "list") {
    $query = "SELECT * FROM users";
    $result = $conn->query($query);

    $data = [];
    while($row = $result->fetch_assoc()) {
        $img = $row['image'] ? "<img src='uploads/images/{$row['image']}' width='40'>" : '';
        $docIcon = '';
        if ($row['document']) {
            $ext = pathinfo($row['document'], PATHINFO_EXTENSION);
            if (in_array($ext, ['jpg','jpeg','png'])) $docIcon = "🖼️";
            elseif ($ext == 'pdf') $docIcon = "📄";
        }

        $actionBtn = "<button class='delete' data-id='{$row['id']}'>Delete</button>";
        if ($row['role'] == 'user') {
            $actionBtn .= " <button>Edit</button> <button>Upload Doc</button>";
        }

        $data[] = [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['role'],
            $img,
            $docIcon,
            $actionBtn
        ];
    }
    echo json_encode([
        "draw" => intval($_GET['draw']),
        "recordsTotal" => count($data),
        "recordsFiltered" => count($data),
        "data" => $data
    ]);
}

if ($action == "save") {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $imageName = time()."_".$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/images/".$imageName);
    }

    $docName = '';
    if (!empty($_FILES['document']['name'])) {
        $docName = time()."_".$_FILES['document']['name'];
        move_uploaded_file($_FILES['document']['tmp_name'], "uploads/docs/".$docName);
    }

    if ($id) {
        $sql = "UPDATE users SET name='$name', email='$email'";
        if ($imageName) $sql .= ", image='$imageName'";
        if ($docName) $sql .= ", document='$docName'";
        $sql .= " WHERE id=$id";
        $conn->query($sql);
        echo "Updated Successfully";
    } else {
        $sql = "INSERT INTO users (name,email,role,image,document) 
                VALUES('$name','$email','$role','$imageName','$docName')";
        $conn->query($sql);
        echo "Added Successfully";
    }
}

if ($action == "delete") {
    $id = $_GET['id'];
    $conn->query("DELETE FROM users WHERE id=$id");
    echo "Deleted Successfully";
}
?>
