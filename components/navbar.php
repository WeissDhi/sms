<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Dashboard logic
$dashboardLink = '#';
$showDashboard = false;

if (isset($_SESSION['author_type'])) {
    $showDashboard = true;

    if ($_SESSION['author_type'] === 'admin') {
        $dashboardLink = './bloging/dashboard/admin/index.php';
    } elseif ($_SESSION['author_type'] === 'user') {
        $dashboardLink = './bloging/dashboard/users/index.php';
    }
}

// Ambil nama user/admin dari database
$displayName = '';
if ($showDashboard && isset($_SESSION['username'])) {
    if ($_SESSION['author_type'] === 'admin') {
        $stmt = $conn->prepare("SELECT first_name FROM admin WHERE username = ?");
        $stmt->bind_param("s", $_SESSION['username']);
    } elseif ($_SESSION['author_type'] === 'user') {
        $stmt = $conn->prepare("SELECT fname FROM users WHERE username = ?");
        $stmt->bind_param("s", $_SESSION['username']);
    }

    if ($stmt->execute()) {
        $stmt->bind_result($name);
        if ($stmt->fetch()) {
            $displayName = $name;
        }
    }
    $stmt->close();
}

// Ambil kategori dari database
$categories = [];
$subcategories = [];

$category_query = $conn->query("SELECT id, category, parent_id FROM category ORDER BY category ASC");

while ($row = $category_query->fetch_assoc()) {
    if (is_null($row['parent_id'])) {
        $categories[$row['id']] = [
            'name' => $row['category'],
            'children' => []
        ];
    } else {
        $subcategories[] = $row;
    }
}

foreach ($subcategories as $sub) {
    if (isset($categories[$sub['parent_id']])) {
        $categories[$sub['parent_id']]['children'][] = [
            'id' => $sub['id'],
            'name' => $sub['category']
        ];
    }
}
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<header class="default-header">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="img/sms.png" alt="Logo" style="height: 40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="daftar-artikel.php">Daftar Artikel</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Kategori
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <?php foreach ($categories as $cat_id => $cat): ?>
                                <?php if (!empty($cat['children'])): ?>
                                    <li class="dropdown-submenu position-relative">
                                        <a class="dropdown-item dropdown-toggle" href="#"><?= htmlspecialchars($cat['name']) ?></a>
                                        <ul class="dropdown-menu sub-menu rounded-0 shadow">
                                            <?php foreach ($cat['children'] as $sub): ?>
                                                <li><a class="dropdown-item" href="kategori.php?id=<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="kategori.php?id=<?= $cat_id ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tentangkami.php">Tentang Kami</a>
                    </li>

                    <?php if ($showDashboard): ?>
                        <!-- Hi, User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Hi, <?= htmlspecialchars($displayName) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="<?= $dashboardLink ?>">Dashboard</a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <?php if (!$showDashboard): ?>
                            <a class="btn btn-primary px-4 py-2" href="login.php">Login</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- CSS untuk submenu -->
<style>
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu > .sub-menu {
    display: none;
    top: 0;
    left: 100%;
    margin-top: 0;
    position: absolute;
    z-index: 999;
}

.dropdown-submenu:hover > .sub-menu {
    display: block;
}

.dropdown-menu {
    overflow: visible;
}
</style>
