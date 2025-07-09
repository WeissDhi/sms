<!-- components/sidebar.php -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .offcanvas.custom-sidebar {
        width: 280px;
        background: linear-gradient(135deg, rgba(44, 62, 80, 0.95), rgba(52, 152, 219, 0.92));
        box-shadow: 4px 0 24px rgba(44, 62, 80, 0.12);
        border-top-right-radius: 24px;
        border-bottom-right-radius: 24px;
        backdrop-filter: blur(8px);
        color: #fff;
    }

    .offcanvas-header {
        border-bottom: 1.5px solid rgba(255, 255, 255, 0.12);
        padding: 2rem 1.5rem 1.2rem 1.5rem;
        background: rgba(255, 255, 255, 0.04);
        border-top-right-radius: 24px;
    }

    .offcanvas-title {
        color: #fff;
        font-weight: 700;
        font-size: 1.6rem;
        letter-spacing: 1px;
        text-shadow: 0 2px 8px rgba(44, 62, 80, 0.12);
    }

    .nav-link {
        color: #e0e0e0;
        padding: 1rem 1.5rem;
        margin: 0.3rem 0;
        border-radius: 12px;
        font-size: 1.08rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.25s cubic-bezier(.4, 2, .3, 1);
        box-shadow: none;
    }

    .nav-link i {
        font-size: 1.35rem;
        transition: color 0.2s;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.10);
        color: #fff;
        transform: translateX(8px) scale(1.03);
        box-shadow: 0 2px 12px rgba(52, 152, 219, 0.08);
    }

    .nav-link:hover i {
        color: #6dd5fa;
    }

    .nav-link.active {
        background: linear-gradient(90deg, #2980b9 60%, #6dd5fa 100%);
        color: #fff;
        box-shadow: 0 4px 18px rgba(52, 152, 219, 0.18);
        font-weight: 700;
        transform: scale(1.04);
    }

    .nav-link.active i {
        color: #fff;
        text-shadow: 0 2px 8px #6dd5fa;
    }

    .btn-close {
        padding: 1.1rem;
        margin: -1.1rem -1.1rem -1.1rem auto;
        filter: invert(1) grayscale(1);
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .btn-close:hover {
        opacity: 1;
    }

    @media (max-width: 600px) {
        .offcanvas.custom-sidebar {
            width: 90vw;
            border-radius: 0 18px 18px 0;
        }

        .offcanvas-header {
            padding: 1.2rem 1rem 1rem 1rem;
        }
    }
</style>
<div class="offcanvas offcanvas-start custom-sidebar" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" data-bs-backdrop="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="../../dashboard/penulis/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'blog_management.php') ? 'active' : ''; ?>" href="../../dashboard/penulis/blog_management.php">
                    <i class="bi bi-journal-text"></i> Manajemen Blog
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="../../dashboard/penulis/profile.php">
                    <i class="bi bi-person"></i> Profil
                </a>
            </li>
        </ul>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarElement = document.getElementById('sidebar');
        // const bsSidebar = bootstrap.Offcanvas.getOrCreateInstance(sidebarElement); // Dihapus agar tidak error di Bootstrap 5

        // Tutup offcanvas ketika klik di luar sidebar
        document.addEventListener('click', function(event) {
            if (sidebarElement.classList.contains('show')) { // Check if the sidebar is shown
                const isClickInside = 
                    sidebarElement.contains(event.target) ||
                    event.target.closest('.btn[data-bs-toggle="offcanvas"]') ||
                    event.target.closest('.dropdown') ||
                    event.target.closest('.dropdown-menu');
                if (!isClickInside) {
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(sidebarElement);
                    if (bsOffcanvas) bsOffcanvas.hide();
                }
            }
        });

        // Ambil semua offcanvas
        document.querySelectorAll('.offcanvas').forEach(function(offcanvas) {
            offcanvas.addEventListener('shown.bs.offcanvas', function () {
                // Tambahkan event listener pada backdrop
                const backdrop = document.querySelector('.offcanvas-backdrop');
                if (backdrop) {
                    // Hapus event listener lama agar tidak dobel
                    backdrop.onclick = null;
                    backdrop.addEventListener('click', function() {
                        // Paksa tutup offcanvas
                        var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
                        if (bsOffcanvas) bsOffcanvas.hide();
                    }, { once: true });
                }
            });
        });
    });

    document.addEventListener('hidden.bs.offcanvas', function () {
        // Paksa hapus backdrop jika masih ada
        document.querySelectorAll('.offcanvas-backdrop').forEach(function(backdrop) {
            backdrop.parentNode.removeChild(backdrop);
        });
        // Pastikan body tidak punya class 'offcanvas-backdrop'
        document.body.classList.remove('offcanvas-backdrop');
        document.body.style.overflow = '';
    });
</script>