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

$categoryTree = buildCategoryTree($categories);

// Tangkap kategori aktif dari URL (category.php?id=...)
$currentCategoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Fungsi cari semua parent kategori aktif supaya submenu yang terkait bisa otomatis dibuka
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

$activeParents = [];
if ($currentCategoryId !== null) {
    findActiveParents($categoryTree, $currentCategoryId, $activeParents);
}

// Render dropdown kategori (rekursif), menampilkan submenu langsung jika termasuk parent chain aktif
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
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="img/sms.png" alt="Logo" style="height:40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-3">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="daftar-artikel.php">Daftar Artikel</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="kategoriDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Kategori
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="kategoriDropdown">
                            <?php renderCategoryDropdown($categoryTree, $activeParents, $currentCategoryId); ?>
                        </ul>
                    </li>

                    <li class="nav-item"><a class="nav-link" href="tentangkami.php">Tentang Kami</a></li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <?php if ($showDashboard): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Hi, <?= htmlspecialchars($displayName) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="<?= $dashboardLink ?>">Dashboard</a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-primary px-4 py-2" href="login.php" style="min-width: 120px; text-align: center;">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
    /* Submenu posisi dan display */
    .dropdown-submenu {
        position: relative;
    }

    .dropdown-submenu > .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -1px;
        position: absolute;
        display: none;
        min-width: 200px;
    }

    /* Tampilkan submenu yang punya class .show */
    .dropdown-submenu.show > .dropdown-menu {
        display: block !important;
    }

    /* Tampilkan submenu saat hover di desktop */
    @media (min-width: 992px) {
        .dropdown-submenu:hover > .dropdown-menu {
            display: block;
        }
    }

    /* Hover dan fokus untuk tombol utama */
    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active {
        background-color: #8fc333 !important;
        border-color: #fff !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    /* Hover link navbar */
    .navbar-nav .nav-link:hover {
        color: #8fc333 !important;
        text-decoration: underline;
    }

    /* Hover submenu item */
    .dropdown-menu .dropdown-item:hover {
        color: #8fc333 !important;
        background-color: transparent !important;
        text-decoration: underline;
    }

    /* Highlight kategori aktif */
    .dropdown-item.active {
        font-weight: 600;
        color: #8fc333 !important;
        text-decoration: underline;
    }
</style>

<!-- Bootstrap JS Bundle (Popper + Bootstrap) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle submenu on click untuk mobile (dan touch devices)
    document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const submenu = this.nextElementSibling;
            if (!submenu) return;

            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
            } else {
                // Tutup submenu lain yang terbuka
                document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
                    menu.style.display = 'none';
                });
                submenu.style.display = 'block';
            }
        });
    });

    // Tutup submenu saat klik di luar dropdown
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
            menu.style.display = 'none';
        });
    });
</script>
