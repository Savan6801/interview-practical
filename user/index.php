<?php include "db/conn.php";
include "helper.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>CRUD App</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
<h2>User/Admin Management</h2>

<button onclick="$('#addModal').show()">Add User/Admin</button>

<table id="userTable" class="display">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Image</th>
        <th>Action</th>
    </tr>
    </thead>
</table>

<div id="addModal" style="display:none;">
    <?php load_listing_view('user'); ?>
</div>

<script>
    $(document).ready(function () {
        var table = $('#userTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "ajax.php?action=list"
        });

        $(document).on("submit", "#userForm", function (e) {
            e.preventDefault();
            $.ajax({
                url: "ajax.php?action=save",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function (resp) {
                    alert(resp);
                    $('#addModal').hide();
                    table.ajax.reload();
                }
            });
        });

        $(document).on("click", ".delete", function () {
            if (confirm("Delete?")) {
                var id = $(this).data("id");
                $.get("ajax.php?action=delete&id=" + id, function (resp) {
                    alert(resp);
                    table.ajax.reload();
                });
            }
        });
    });
</script>
</body>
</html>
