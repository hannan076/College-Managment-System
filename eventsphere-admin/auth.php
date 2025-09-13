<?php
session_start();
require_once __DIR__ . '/db.php';

function login($email, $password){
    global $pdo;
    $stmt = $pdo->prepare("SELECT user_id,password,role,is_active FROM users WHERE email=?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && $u['is_active'] && password_verify($password, $u['password'])){
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['role']    = $u['role'];
        return true;
    }
    return false;
}

function is_logged_in(){ return !empty($_SESSION['user_id']); }
function is_admin(){ return isset($_SESSION['role']) && $_SESSION['role']==='admin'; }

function require_admin(){
    if (!is_logged_in() || !is_admin()){
        header("Location: login.php"); exit;
    }
}
