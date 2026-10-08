<?php
// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'support_system';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to check if file is allowed
function isAllowedFile($filename) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xml'];
    return in_array(getFileExtension($filename), $allowed);
}

// Function to get file icon
function getFileIcon($filename) {
    $ext = getFileExtension($filename);
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'fa-image';
        case 'pdf':
            return 'fa-file-pdf';
        case 'doc':
        case 'docx':
            return 'fa-file-word';
        case 'xml':
            return 'fa-file-code';
        default:
            return 'fa-file';
    }
}
?> 