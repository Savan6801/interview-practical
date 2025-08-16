<?php
// helpers/helper.php

function load_view($view_path, $data = []) {
    // $view_path relative to project root or with full path
    extract($data, EXTR_SKIP);
    include $view_path;
}

/**
 * Simple helper to sanitize filenames
 */
function safe_filename($name) {
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
    return $name;
}

/**
 * Get extension/lowercase
 */
function get_file_ext($file) {
    return strtolower(pathinfo($file, PATHINFO_EXTENSION));
}
