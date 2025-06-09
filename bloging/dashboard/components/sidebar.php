<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .offcanvas {
            width: 280px;
            background: linear-gradient(to bottom, #ffffff, #f8f9fa);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .offcanvas-header {
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 1rem;
        }
        .offcanvas-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.4rem;
        }
        .nav-link {
            color: #495057;
            padding: 0.8rem 1.2rem;
            margin: 0.2rem 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: #e9ecef;
            color: #0d6efd;
            transform: translateX(5px);
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .btn-close {
            padding: 0.8rem;
            margin: -0.8rem -0.8rem -0.8rem auto;
        }
    </style>
</head>
<!-- components/sidebar.php -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="../../dashboard/admin/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'blogs_management.php') ? 'active' : ''; ?>" href="../../dashboard/admin/blogs_management.php">
                    <i class="bi bi-journal-text"></i> Manajemen Blog
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'users_management.php') ? 'active' : ''; ?>" href="../../dashboard/admin/users_management.php">
                    <i class="bi bi-people"></i> Manajemen User
                </a>
            </li>
        </ul>
    </div>
</div>