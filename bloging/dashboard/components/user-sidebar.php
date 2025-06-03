<!-- components/sidebar.php -->

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="../../dashboard/users/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../dashboard/users/profile.php">
                    <i class="bi bi-journal-text"></i> Edit Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../dashboard/users/blog_management.php">
                    <i class="bi bi-people"></i> Manajemen Blog
                </a>
            </li>
        </ul>
    </div>
</div>