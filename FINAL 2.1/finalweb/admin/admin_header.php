<?php
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }

require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin · <?php echo SITE_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    :root{--navy:#0a2540}
    .navbar-navy{background:var(--navy)!important}
    .btn-navy{background:var(--navy);color:#fff}
    .btn-navy:hover{filter:brightness(.95);color:#fff}
    
    /* Full height layout for admin */
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }
    
    .admin-layout {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .admin-navbar {
      flex-shrink: 0;
      z-index: 1001;
    }
    
    .admin-content {
      flex: 1;
      overflow: visible;
      position: relative;
    }
    
    .modal { z-index: 1065; }
    .modal-backdrop { z-index: 1060; }
  </style>
</head>
<body>
<div class="admin-layout">
  <nav class="navbar navbar-dark navbar-navy admin-navbar">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">Admin · <?php echo SITE_NAME; ?></a>
      <div class="d-flex align-items-center gap-2 text-white">
        <small>Signed in as <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'admin'); ?></strong></small>
        <a class="btn btn-outline-light btn-sm" href="<?php echo BASE_URL; ?>/index.php" target="_blank">View Site</a>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <div class="admin-content">
