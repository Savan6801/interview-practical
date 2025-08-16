<?php
function load_listing_view($role) {
    if ($role == 'admin') {
        include "admin_form.php";
    } else {
        include "user_form.php";
    }
}
?>
