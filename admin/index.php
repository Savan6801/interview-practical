<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Table</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>

<body>

<div class="container mt-5">
    <h2>Simple Bordered Table</h2>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th scope="col">Sr.No</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS (optional, for components requiring JS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#adminTable').DataTable({
            ajax: {
                url: 'api/admin.php?function=getAdminDetails', // Calls your class API
                dataSrc: ''
            },
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'email'},
            ]
        });
    });
</script>
</body>

</html>