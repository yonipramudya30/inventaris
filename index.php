<?php
session_start();
if(isset($_SESSION['user_id'])){ 
    // Cek role user
    if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: /INVENKAS/admin/');
    } else {
        header('Location: /INVENKAS/dashboard/');
    }
    exit; 
}
header('Location: /INVENKAS/login.php');
?>
