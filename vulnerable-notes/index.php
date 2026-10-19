<?php
require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: notes/dashboard.php');
    exit;
}

header('Location: login/login.php');
exit;