<?php
require_once '../config/app.php';

// Destroy session and redirect
session_destroy();
header('Location: ../index.php');
exit();
