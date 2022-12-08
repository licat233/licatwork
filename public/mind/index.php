<?php
session_start();
$filename = "/loginpage.html";
if (isset($_SESSION['mindadmin']) && $_SESSION["mindadmin"] === true) {
    $filename = "/homepage.html";
    require(__DIR__ . $filename);
    die();
}
if (isset($_POST['submit'])) { 
    $password = $_POST['password']; 
    if ($password === "123123") {
        $_SESSION["mindadmin"] = true;
        $filename = "/homepage.html";
    }
}
require(__DIR__ . $filename);