<?php
session_start();
// Simulated login
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Admin'; // Change to test: 'User'
}
