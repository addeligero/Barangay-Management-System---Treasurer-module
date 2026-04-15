<?php
session_start();

if (!isset($_SESSION['resident_id'])) {
    header("Location: /resident/login.php");
    exit;
}
