<?php
session_start();
session_destroy();
header('Location: /INVENKAS/login.php');
?>
