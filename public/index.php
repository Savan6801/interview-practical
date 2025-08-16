<?php
// public/index.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/helper.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Core PHP CRUD - Admin & User</title>
    <!-- Include CSS: bootstrap, datatables -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .thumb { width:48px; height:48px; object-fit:cover; border-radius:4px; }
        .action-btn { margin-right:4px; }
    </style>
</head>
<body class="p-4">
<div class="container">
    <h2>Admin & User Management (Core PHP)</h2>
    <ul class="nav nav-tabs" id="mainTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#adminsTab">Admins</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#usersTab">Users</a></li>
    </ul>
    <div class="tab-content mt-3">
        <div id="adminsTab" class="tab-pane fade show active">
            <?php load_view(__DIR__ . '/../views/admin_list.php'); ?>
        </div>
        <div id="usersTab" class="tab-pane fade">
            <?php load_view(__DIR__ . '/../views/user_list.php'); ?>
        </div>
    </div>
</div>

<!-- Modals (empty container for dynamic content) -->
<div id="modalContainer"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    // AJAX + DataTables setup and handlers
    $(document).ready(function() {
        // Admin DataTable server-side
        var adminTable = $('#adminTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '../api/admin.php',
                type: 'POST',
                data: { action: 'list' }
            },
            columns: [
                { data: 'id' },
                { data: 'image', orderable: false, searchable: false },
                { data: 'name' },
                { data: 'email' },
                { data: 'created_at' },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });

        // User DataTable server-side
        var userTable = $('#userTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '../api/user.php',
                type: 'POST',
                data: { action: 'list' }
            },
            columns: [
                { data: 'id' },
                { data: 'image', orderable: false, searchable: false },
                { data: 'name' },
                { data: 'email' },
                { data: 'admin_name' },
                { data: 'doc_type', orderable: false, searchable: false },
                { data: 'created_at' },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });

        // Show Add Admin modal
        $('#addAdminBtn').on('click', function(){
            $.get('../views/modals/admin_add.php', function(html){
                $('#modalContainer').html(html);
                var modal = new bootstrap.Modal(document.getElementById('adminModal'));
                modal.show();
                bindAdminForm(modal, adminTable);
            });
        });

        // Show Add User modal
        $('#addUserBtn').on('click', function(){
            $.get('../views/modals/user_add.php', function(html){
                $('#modalContainer').html(html);
                var modal = new bootstrap.Modal(document.getElementById('userModal'));
                modal.show();
                bindUserForm(modal, userTable);
            });
        });

        // Use delegated events for Edit/Delete/Upload
        $('#adminsTab').on('click', '.editAdmin', function(){
            var id = $(this).data('id');
            $.get('../views/modals/admin_edit.php', {id:id}, function(html){
                $('#modalContainer').html(html);
                var modal = new bootstrap.Modal(document.getElementById('adminModal'));
                modal.show();
                bindAdminForm(modal, adminTable);
            });
        });

        $('#adminsTab').on('click', '.deleteAdmin', function(){
            if (!confirm('Delete admin and all users under it?')) return;
            var id = $(this).data('id');
            $.post('../api/admin.php', { action: 'delete', id: id }, function(resp){
                if (resp.success) {
                    adminTable.ajax.reload(null,false);
                    userTable.ajax.reload(null,false);
                    alert('Admin and related users deleted');
                } else alert(resp.error || 'Error');
            }, 'json');
        });

        $('#usersTab').on('click', '.editUser', function(){
            var id = $(this).data('id');
            $.get('../views/modals/user_edit.php', {id:id}, function(html){
                $('#modalContainer').html(html);
                var modal = new bootstrap.Modal(document.getElementById('userModal'));
                modal.show();
                bindUserForm(modal, userTable);
            });
        });

        $('#usersTab').on('click', '.deleteUser', function(){
            if (!confirm('Delete user?')) return;
            var id = $(this).data('id');
            $.post('../api/user.php', { action: 'delete', id: id }, function(resp){
                if (resp.success) {
                    userTable.ajax.reload(null,false);
                    alert('User deleted');
                } else alert(resp.error || 'Error');
            }, 'json');
        });

        // Upload doc
        $('#usersTab').on('click', '.uploadDoc', function(){
            var id = $(this).data('id');
            $.get('../views/modals/user_upload_doc.php', {id:id}, function(html){
                $('#modalContainer').html(html);
                var modal = new bootstrap.Modal(document.getElementById('uploadModal'));
                modal.show();
                bindUploadForm(modal, userTable);
            });
        });

        // Form bindings
        function bindAdminForm(modal, table) {
            $('#adminForm').on('submit', function(e){
                e.preventDefault();
                var fd = new FormData(this);
                $.ajax({
                    url: '../api/admin.php',
                    method: 'POST',
                    dataType: 'json',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(resp){
                        if (resp.success) {
                            table.ajax.reload(null,false);
                            modal.hide();
                        } else alert(resp.error || 'Error');
                    }
                });
            });
        }

        function bindUserForm(modal, table) {
            $('#userForm').on('submit', function(e){
                e.preventDefault();
                var fd = new FormData(this);
                $.ajax({
                    url: '../api/user.php',
                    method: 'POST',
                    dataType: 'json',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(resp){
                        if (resp.success) {
                            table.ajax.reload(null,false);
                            modal.hide();
                        } else alert(resp.error || 'Error: ' + (resp.error || ''));
                    }
                });
            });
        }

        function bindUploadForm(modal, table) {
            $('#uploadForm').on('submit', function(e){
                e.preventDefault();
                var fd = new FormData(this);
                $.ajax({
                    url: '../api/user.php',
                    method: 'POST',
                    dataType: 'json',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(resp){
                        if (resp.success) {
                            table.ajax.reload(null,false);
                            modal.hide();
                        } else alert(resp.error || 'Error: ' + (resp.error || ''));
                    }
                });
            });
        }

    });
</script>
</body>
</html>
