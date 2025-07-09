<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan $conn sudah terhubung ke DB-mu, misal:
// $conn = new mysqli($host, $user, $pass, $dbname);

// Dashboard & User info
$dashboardLink = '#';
$showDashboard = false;
$displayName = '';

if (isset($_SESSION['author_type'])) {
    $showDashboard = true;
    if ($_SESSION['author_type'] === 'admin') {
        $dashboardLink = './bloging/dashboard/admin/index.php';
    } elseif ($_SESSION['author_type'] === 'user') {
        $dashboardLink = './bloging/dashboard/users/index.php';
    }
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
}

// Ambil kategori dari database
$categories = [];
$query = $conn->query("SELECT id, category, parent_id FROM category ORDER BY category ASC");
while ($row = $query->fetch_assoc()) {
    $categories[] = $row;
}

// Build kategori tree (rekursif)
if (!function_exists('buildCategoryTree')) {
    function buildCategoryTree(array $elements, $parentId = null)
    {
        $branch = [];
        foreach ($elements as $element) {
            $pid = ($element['parent_id'] === null || strtolower($element['parent_id']) === 'null') ? null : $element['parent_id'];
            if ($pid === $parentId) {
                $children = buildCategoryTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}

$categoryTree = buildCategoryTree($categories);

// Tangkap kategori aktif dari URL (category.php?id=...)
$currentCategoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Fungsi cari semua parent kategori aktif supaya submenu yang terkait bisa otomatis dibuka
if (!function_exists('findActiveParents')) {
    function findActiveParents($elements, $currentId, &$parents = []) {
        foreach ($elements as $el) {
            if ($el['id'] == $currentId) {
                return true;
            }
            if (isset($el['children'])) {
                if (findActiveParents($el['children'], $currentId, $parents)) {
                    $parents[] = $el['id'];
                    return true;
                }
            }
        }
        return false;
    }
}

$activeParents = [];
if ($currentCategoryId !== null) {
    findActiveParents($categoryTree, $currentCategoryId, $activeParents);
}

// Render dropdown kategori (rekursif), menampilkan submenu langsung jika termasuk parent chain aktif
if (!function_exists('renderCategoryDropdown')) {
    function renderCategoryDropdown($categories, $activeParents = [], $currentCategoryId = null)
    {
        foreach ($categories as $cat) {
            $isActiveParent = in_array($cat['id'], $activeParents);
            $isCurrent = $cat['id'] === $currentCategoryId;

            if (isset($cat['children'])) {
                echo '<li class="dropdown-submenu ' . ($isActiveParent ? 'show' : '') . '">';
                echo '<a class="dropdown-item dropdown-toggle" href="#">' . htmlspecialchars($cat['category']) . '</a>';
                echo '<ul class="dropdown-menu" style="' . ($isActiveParent ? 'display:block;' : 'display:none;') . '">';
                renderCategoryDropdown($cat['children'], $activeParents, $currentCategoryId);
                echo '</ul></li>';
            } else {
                echo '<li><a class="dropdown-item ' . ($isCurrent ? 'active' : '') . '" href="category.php?id=' . $cat['id'] . '">' . htmlspecialchars($cat['category']) . '</a></li>';
            }
        }
    }
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<header>
    <nav class="navbar navbar-expand-lg navbar-light custom-navbar shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <img src="img/sms.png" alt="Logo" style="height:48px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-3">
                    <li class="nav-item"><a class="nav-link fw-semibold d-flex align-items-center gap-1" href="index.php"><i class="bi bi-house-door"></i> Beranda</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold d-flex align-items-center gap-1" href="daftar-artikel.php"><i class="bi bi-journal-text"></i> Daftar Artikel</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-semibold" href="#" id="kategoriDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid me-1"></i>Kategori
                        </a>
                        <ul class="dropdown-menu p-2" aria-labelledby="kategoriDropdown" style="min-width:240px;">
                            <?php renderCategoryDropdown($categoryTree, [], null); ?>
                        </ul>
                    </li>

                    <li class="nav-item"><a class="nav-link fw-semibold d-flex align-items-center gap-1" href="tentangkami.php"><i class="bi bi-info-circle"></i> Profil Kami</a></li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <?php if ($showDashboard): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5 text-success"></i>
                                <span class="fw-semibold text-dark">Hi, <?= htmlspecialchars($displayName) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="<?= $dashboardLink ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-success px-4 py-2 rounded-pill fw-semibold" href="login.php" style="min-width: 120px; text-align: center;">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
    .custom-navbar {
        background: #FAFAF0 !important; /* Ivory */
        border-bottom: 3px solid #2E7D32; /* Forest green */
        box-shadow: 0 2px 12px rgba(46, 125, 50, 0.08);
        z-index: 100;
    }
    .navbar-brand span {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .navbar-nav .nav-link {
        color: #2C2C2C !important; /* Charcoal */
        font-size: 1.08rem;
        position: relative;
        transition: color 0.2s;
        display: flex;
        align-items: center;
    }
    .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
        color: #2E7D32 !important; /* Forest green */
    }
    .navbar-nav .nav-link::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 2px;
        height: 2px;
        background: #C5E1A5; /* Lime */
        transition: width 0.3s;
        width: 0;
        z-index: 1;
    }
    .navbar-nav .nav-link:hover::after, .navbar-nav .nav-link.active::after {
        width: 100%;
    }
    .dropdown-menu {
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(46, 125, 50, 0.10);
        border: 1.5px solid #C5E1A5; /* Lime */
        font-size: 1rem;
        background: #EEEEEE; /* Soft gray */
    }
    .dropdown-item.active, .dropdown-item:active {
        background: #2E7D32; /* Forest green */
        color: #fff !important;
    }
    .dropdown-item:hover {
        background: #C5E1A5; /* Lime */
        color: #2E7D32; /* Forest green */
    }
    .btn-success {
        background: #2E7D32; /* Forest green */
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(46, 125, 50, 0.10);
    }
    .btn-success:hover {
        background: #1B5E20; /* Darker forest green */
        color: #fff;
    }
    .navbar-toggler {
        border-radius: 8px;
        border: 2px solid #2E7D32; /* Forest green */
        padding: 6px 10px;
    }
    .navbar-toggler:focus {
        box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
    }
    @media (max-width: 991px) {
        .navbar-nav {
            gap: 0.5rem !important;
        }
        .navbar-brand span {
            font-size: 1.1rem;
        }
    }
    @media (max-width: 576px) {
        .navbar-brand img {
            height: 36px !important;
        }
        .navbar-brand span {
            font-size: 1rem;
        }
        .custom-navbar {
            padding: 0.5rem 0.2rem !important;
        }
    }
    /* Submenu posisi dan display */
    .dropdown-submenu {
        position: relative;
    }
    .dropdown-submenu > .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -1px;
        position: absolute;
        min-width: 200px;
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(46, 125, 50, 0.10);
        border: 1.5px solid #C5E1A5; /* Lime */
        background: #EEEEEE; /* Soft gray */
    }
    /* Tampilkan submenu hanya saat hover tepat di atas menu utama */
    @media (min-width: 992px) {
        .dropdown-submenu > .dropdown-menu {
            pointer-events: none;
        }
        .dropdown-submenu:hover > .dropdown-menu,
        .dropdown-submenu.show > .dropdown-menu {
            display: block !important;
            pointer-events: auto;
        }
    }
    /* Hover warna lime pada dropdown */
    .dropdown-menu .dropdown-item:hover,
    .dropdown-menu .dropdown-item:focus {
        background: #C5E1A5; /* Lime */
        color: #2E7D32 !important; /* Forest green */
    }
    .dropdown-menu .dropdown-item.active, .dropdown-menu .dropdown-item:active {
        background: #2E7D32; /* Forest green */
        color: #fff !important;
    }
</style>

<!-- Bootstrap JS Bundle (Popper + Bootstrap) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tidak ada JS submenu bertahan, submenu hanya muncul saat hover (desktop) -->
