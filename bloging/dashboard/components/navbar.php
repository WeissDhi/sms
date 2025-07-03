<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- components/navbar.php -->
<?php
// Get user's name based on session
$displayName = '';
if (isset($_SESSION['username'])) {
    if ($_SESSION['author_type'] === 'admin') {
        $stmt = $conn->prepare("SELECT first_name FROM admin WHERE username = ?");
    } else {
        $stmt = $conn->prepare("SELECT fname FROM users WHERE username = ?");
    }
    $stmt->bind_param("s", $_SESSION['username']);
    if ($stmt->execute()) {
        $stmt->bind_result($name);
        if ($stmt->fetch()) {
            $displayName = $name;
        }
    }
    $stmt->close();
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .navbar {
        background: linear-gradient(to right, #2c3e50, #34495e) !important;
        padding: 0.8rem 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar-brand {
        font-size: 1.4rem;
        font-weight: 600;
        color: #ffffff !important;
        letter-spacing: 0.5px;
    }
    .navbar-brand:hover {
        color: #e9ecef !important;
    }
    .btn-toggle-sidebar {
        background: transparent;
        border: 2px solid rgba(255,255,255,0.2);
        padding: 0.5rem 0.8rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .btn-toggle-sidebar:hover {
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.3);
        transform: translateY(-1px);
    }
    .btn-toggle-sidebar i {
        font-size: 1.2rem;
        color: #ffffff;
    }
    .navbar .container-fluid {
        padding: 0 1.5rem;
    }
    .profile-dropdown .dropdown-toggle {
        color: #ffffff !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .profile-dropdown .dropdown-toggle:hover {
        color: #e9ecef !important;
    }
    .profile-dropdown .dropdown-menu {
        background: #ffffff;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 8px;
        margin-top: 0.5rem;
    }
    .profile-dropdown .dropdown-item {
        padding: 0.7rem 1.2rem;
        color: #2c3e50;
        transition: all 0.2s ease;
    }
    .profile-dropdown .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
    .profile-dropdown .dropdown-item.text-danger:hover {
        background-color: #fff5f5;
        color: #dc3545;
    }
    .profile-dropdown .dropdown-item i {
        margin-right: 0.5rem;
        width: 1.2rem;
    }
</style>

<nav class="navbar navbar-dark">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <button class="btn btn-toggle-sidebar me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="#">
                <i class="bi bi-layout-text-window me-2"></i>
                Dashboard Panel
            </a>
        </div>
        <div class="d-flex align-items-center">
            <div class="dropdown profile-dropdown">
                <a class="dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <?php echo htmlspecialchars($displayName); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li>
                        <a class="dropdown-item" href="../../../index.php">
                            <i class="bi bi-house"></i> Kembali ke Beranda
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="../../../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap Bundle with Popper -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

<script>
// Initialize all dropdowns
document.addEventListener('DOMContentLoaded', function() {
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});
</script>