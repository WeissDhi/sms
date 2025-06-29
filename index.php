<?php
session_start();
include './bloging/config.php';

// Latest articles
$query_latest = "SELECT * FROM blogs 
                WHERE status = 'published' 
                 ORDER BY created_at DESC 
                 LIMIT 6";
$result_latest = mysqli_query($conn, $query_latest);

// Trending this week (7 hari terakhir berdasarkan views)
$seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
$query_trending = "SELECT * FROM blogs 
                   WHERE created_at >= '$seven_days_ago' 
                   AND status = 'published'
                   ORDER BY views DESC 
                   LIMIT 5";
$result_trending = mysqli_query($conn, $query_trending);

// Get featured categories with article counts
$query_categories = "SELECT c.*, COUNT(b.id) as article_count 
                     FROM category c 
                     LEFT JOIN blogs b ON c.id = b.category_id AND b.status = 'published'
                     GROUP BY c.id 
                     ORDER BY article_count DESC 
                     LIMIT 6";
$result_categories = mysqli_query($conn, $query_categories);

// Get statistics
$total_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM blogs WHERE status = 'published'"))['total'];
$total_views = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM blogs WHERE status = 'published'"))['total'];
$total_authors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT author_id) as total FROM blogs WHERE status = 'published'"))['total'];

// Get most viewed article of all time
$query_most_viewed = "SELECT b.*, 
                             c.category as category_name,
                             CASE 
                                 WHEN b.author_type = 'admin' THEN a.first_name
                                 ELSE u.fname 
                             END as author_name
                      FROM blogs b
                      LEFT JOIN category c ON b.category_id = c.id
                      LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
                      LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
                      WHERE b.status = 'published'
                      ORDER BY b.views DESC 
                      LIMIT 1";
$result_most_viewed = mysqli_query($conn, $query_most_viewed);
$most_viewed_article = mysqli_fetch_assoc($result_most_viewed);

// Ambil semua kategori untuk mapping id -> data
$allCategories = [];
$resCat = $conn->query("SELECT id, category, parent_id FROM category");
while ($cat = $resCat->fetch_assoc()) {
  $allCategories[$cat['id']] = $cat;
}

if (!$result_trending) {
  echo "Trending Query Error: " . mysqli_error($conn);
}
?>


<!DOCTYPE html>
<html lang="zxx" class="no-js">

<head>
  <!-- Mobile Specific Meta -->
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Favicon-->
  <link rel="shortcut icon" href="img/fav.png" />
  <!-- Author Meta -->
  <meta name="author" content="colorlib" />
  <!-- Meta Description -->
  <meta name="description" content="" />
  <!-- Meta Keyword -->
  <meta name="keywords" content="" />
  <!-- meta character set -->
  <meta charset="UTF-8" />
  <!-- Site Title -->
  <title>Blogger</title>

  <link
    href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700"
    rel="stylesheet" />
  <!--CSS============================================= -->
  <link rel="stylesheet" href="css/linearicons.css" />
  <link rel="stylesheet" href="css/font-awesome.min.css" />
  <link rel="stylesheet" href="css/bootstrap.css" />
  <link rel="stylesheet" href="css/owl.carousel.css" />
  <link rel="stylesheet" href="css/main.css" />
</head>

<body>

  <?php include './components/navbar.php'; ?>

  <!-- start banner Area -->
  <!-- Banner section dihapus sesuai permintaan -->

  <style>
    body {
      background: #FAFAF0;
      /* Ivory */
      font-family: 'Poppins', Arial, sans-serif;
      color: #2C2C2C;
      /* Charcoal */
      max-width: 100vw;
      overflow-x: hidden;
    }

    /* Container Section Card */
    .category-area,
    .fashion-area,
    .statistics-area,
    .categories-area,
    .featured-article-area,
    .team-area {
      background: #EEEEEE;
      /* Soft gray */
      border-radius: 24px;
      box-shadow: 0 4px 16px rgba(46, 125, 50, 0.08);
      /* Forest green shadow */
      padding: 36px 24px 32px 24px;
      margin-bottom: 48px;
      position: relative;
    }

    @media (max-width: 768px) {

      .category-area,
      .fashion-area,
      .statistics-area,
      .categories-area,
      .featured-article-area,
      .team-area {
        padding: 18px 6px 18px 6px;
        margin-bottom: 24px;
      }
    }

    .banner-area .banner-content h1 {
      font-size: 32px;
      line-height: 1.5;
      font-weight: 600;
      color: #2C2C2C;
      /* Charcoal */
    }

    /* Section Divider */
    .section-divider {
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, #2E7D32 60%, #C5E1A5 100%);
      /* Forest green to lime */
      border-radius: 2px;
      margin: 24px auto 0 auto;
    }

    .row {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    [class*="col-"] {
      padding-left: 8px !important;
      padding-right: 8px !important;
    }

    img,
    .card,
    .featured-img {
      max-width: 100%;
      height: auto;
      box-sizing: border-box;
    }
  </style>
  <!-- End banner Area -->

  <!-- Start category Area -->
  <section class="category-area section-gap" id="news">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="menu-content pb-70 col-lg-8">
          <div class="title text-center">
            <h1 class="mb-10">Blog Terbaru dari Semua Kategori</h1>
            <p>
              Jelajahi tulisan-tulisan terbaru dari semua kategori blog, mulai dari informasi ringan hingga topik mendalam.
            </p>
            <div class="section-divider"></div>
          </div>
        </div>
      </div>
      <div class="row">
        <?php while ($row = mysqli_fetch_assoc($result_latest)): ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <?php if ($row['image']): ?>
                <img src="bloging/uploads/<?= htmlspecialchars(basename($row['image'])) ?>" class="card-img-top" alt="Blog Image">
              <?php else: ?>
                <img src="https://via.placeholder.com/400x200?text=No+Image" class="card-img-top" alt="No Image">
              <?php endif; ?>

              <div class="card-body d-flex flex-column">
                <!-- Judul -->
                <h5 class="text-truncate" title="<?= strip_tags($row['title']) ?>">
                  <?= mb_strimwidth(strip_tags($row['title']), 0, 60, '...') ?>
                </h5>

                <!-- Meta Info -->
                <div class="meta-info mb-2">
                  <span class="icon">📅</span><?= date('d M Y', strtotime($row['created_at'])) ?>
                </div>

                <!-- Badge Kategori -->
                <div class="mb-2">
                  <?php
                  if (!empty($row['category_id']) && isset($allCategories[$row['category_id']])) {
                    $cat = $allCategories[$row['category_id']];
                    if ($cat['parent_id'] && isset($allCategories[$cat['parent_id']])) {
                      $parent = $allCategories[$cat['parent_id']];
                  ?>
                      <a href="category.php?id=<?= $parent['id'] ?>" class="badge bg-success text-white text-decoration-none me-1">
                        #<?= htmlspecialchars($parent['category']) ?>
                      </a>
                      <a href="category.php?id=<?= $cat['id'] ?>" class="badge bg-light text-success text-decoration-none">
                        #<?= htmlspecialchars($cat['category']) ?>
                      </a>
                    <?php
                    } else {
                    ?>
                      <a href="category.php?id=<?= $cat['id'] ?>" class="badge bg-success text-white text-decoration-none">
                        #<?= htmlspecialchars($cat['category']) ?>
                      </a>
                  <?php
                    }
                  }
                  ?>
                </div>

                <!-- Konten Ringkas -->
                <p class="card-text mb-3"><?= mb_strimwidth(strip_tags($row['content']), 0, 100, '...') ?></p>

                <!-- Tombol -->
                <a href="<?= htmlspecialchars($row['slug']) ?>" class="btn btn-read-more mt-auto">
                  Baca Selengkapnya <span>&rarr;</span>
                </a>

              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
  <!-- End category Area -->


  <!-- Start fashion Area -->
  <section class="fashion-area section-gap" id="fashion">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="menu-content pb-60 col-lg-10">
          <div class="title text-center">
            <h1 class="mb-10">Bacaan Trending di Minggu Ini</h1>
            <p>Temukan artikel-artikel yang paling banyak dibaca dalam seminggu terakhir.</p>
            <div class="section-divider"></div>
          </div>
        </div>
      </div>
      <div class="row">
        <?php if ($result_trending && mysqli_num_rows($result_trending) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result_trending)): ?>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100">
                <?php if ($row['image']): ?>
                  <img src="bloging/uploads/<?= htmlspecialchars(basename($row['image'])) ?>" class="card-img-top" alt="Blog Image">
                <?php else: ?>
                  <img src="https://via.placeholder.com/400x200?text=No+Image" class="card-img-top" alt="No Image">
                <?php endif; ?>

                <div class="card-body d-flex flex-column">
                  <!-- Judul -->
                  <h5 class="text-truncate" title="<?= strip_tags($row['title']) ?>">
                    <?= mb_strimwidth(strip_tags($row['title']), 0, 60, '...') ?>
                  </h5>

                  <!-- Meta Info -->
                  <div class="meta-info mb-2">
                    <span class="icon">📅</span><?= date('d M Y', strtotime($row['created_at'])) ?>
                    <span class="icon ms-3">👁️</span><?= number_format($row['views']) ?> views
                  </div>

                  <!-- Konten Ringkas -->
                  <p class="card-text mb-3"><?= mb_strimwidth(strip_tags($row['content']), 0, 100, '...') ?></p>

                  <!-- Tombol -->
                  <a href="<?= htmlspecialchars($row['slug']) ?>" class="btn btn-read-more mt-auto">
                    Baca Selengkapnya <span>&rarr;</span>
                  </a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="no-results">Belum ada artikel trending minggu ini.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- End fashion Area -->

  <!-- Start Statistics Section -->
  <section class="statistics-area section-gap" id="statistics">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="menu-content pb-70 col-lg-8">
          <div class="title text-center">
            <h1 class="mb-10">Statistik Blog Kami</h1>
            <p>Lihat seberapa berkembang komunitas blog kami dalam berbagi pengetahuan dan informasi.</p>
            <div class="section-divider"></div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card text-center">
            <div class="stat-icon">
              <i class="fa fa-file-text-o"></i>
            </div>
            <div class="stat-number"><?= number_format($total_articles) ?></div>
            <div class="stat-label">Total Artikel</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card text-center">
            <div class="stat-icon">
              <i class="fa fa-eye"></i>
            </div>
            <div class="stat-number"><?= number_format($total_views) ?></div>
            <div class="stat-label">Total Views</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card text-center">
            <div class="stat-icon">
              <i class="fa fa-users"></i>
            </div>
            <div class="stat-number"><?= number_format($total_authors) ?></div>
            <div class="stat-label">Penulis Aktif</div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- End Statistics Section -->

  <!-- Start Featured Categories Section -->
  <section class="categories-area section-gap" id="categories">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="menu-content pb-70 col-lg-8">
          <div class="title text-center">
            <h1 class="mb-10">Kategori Unggulan</h1>
            <p>Jelajahi berbagai kategori artikel yang telah kami sediakan untuk memenuhi kebutuhan informasi Anda.</p>
            <div class="section-divider"></div>
          </div>
        </div>
      </div>
      <div class="row">
        <?php if ($result_categories && mysqli_num_rows($result_categories) > 0): ?>
          <?php while ($category = mysqli_fetch_assoc($result_categories)): ?>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="category-card">
                <div class="category-icon">
                  <i class="fa fa-folder-open"></i>
                </div>
                <div class="category-content">
                  <h4><?= htmlspecialchars($category['category']) ?></h4>
                  <p><?= number_format($category['article_count']) ?> Artikel</p>
                  <a href="category.php?id=<?= $category['id'] ?>" class="btn btn-category">
                    Jelajahi Kategori <i class="fa fa-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="no-results">Belum ada kategori tersedia.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <!-- End Featured Categories Section -->

  <!-- Start Most Viewed Article Section -->
  <?php if ($most_viewed_article): ?>
    <section class="featured-article-area section-gap" id="featured">
      <div class="container">
        <div class="row d-flex justify-content-center">
          <div class="menu-content pb-70 col-lg-8">
            <div class="title text-center">
              <h1 class="mb-10">Artikel Terpopuler Sepanjang Masa</h1>
              <p>Artikel yang paling banyak dibaca oleh pembaca setia kami.</p>
              <div class="section-divider"></div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-8 mx-auto">
            <div class="featured-article-card">
              <div class="row">
                <div class="col-lg-6">
                  <?php if ($most_viewed_article['image']): ?>
                    <img src="bloging/uploads/<?= htmlspecialchars(basename($most_viewed_article['image'])) ?>"
                      class="featured-img" alt="Featured Article">
                  <?php else: ?>
                    <img src="https://via.placeholder.com/600x400?text=No+Image"
                      class="featured-img" alt="No Image">
                  <?php endif; ?>
                </div>
                <div class="col-lg-6">
                  <div class="featured-content">
                    <div class="featured-badge">
                      <i class="fa fa-fire"></i> Terpopuler
                    </div>
                    <h2><?= strip_tags($most_viewed_article['title']) ?></h2>
                    <p><?= mb_strimwidth(strip_tags($most_viewed_article['content']), 0, 200, '...') ?></p>
                    <div class="featured-meta">
                      <span><i class="fa fa-user"></i> <?= htmlspecialchars($most_viewed_article['author_name']) ?></span>
                      <span><i class="fa fa-eye"></i> <?= number_format($most_viewed_article['views']) ?> views</span>
                      <span><i class="fa fa-folder"></i> <?= htmlspecialchars($most_viewed_article['category_name']) ?></span>
                    </div>
                    <a href="<?= htmlspecialchars($most_viewed_article['slug']) ?>" class="btn btn-featured">
                      Baca Artikel Lengkap <i class="fa fa-arrow-right"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>
  <!-- End Most Viewed Article Section -->

  <!-- End Newsletter Section -->

  <!-- Start team Area -->
  <section class="team-area section-gap" id="team">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="menu-content pb-70 col-lg-8">
          <div class="title text-center">
            <h1 class="mb-10">Tentang Syukron Ma'mun Society</h1>
            <p>Komunitas alumni Daarul Rahman Cabang Kairo yang berkomitmen untuk berbagi pengetahuan dan pengalaman.</p>
            <div class="section-divider"></div>
          </div>
        </div>
      </div>
      <div class="row justify-content-center d-flex align-items-center">
        <div class="col-lg-8 team-left">
          <h3>Visi Kami</h3>
          <p>
            Menjadi media digital terdepan yang mendokumentasikan pemikiran, karya tulis, dan kegiatan para alumni
            Daarul Rahman Cabang Kairo dalam bentuk blog yang informatif dan inspiratif.
          </p>

          <h3>Misi Kami</h3>
          <p>
            Menyediakan platform yang memungkinkan para alumni untuk berbagi pengetahuan, pengalaman, dan pemikiran
            mereka dengan masyarakat luas, serta mendokumentasikan kegiatan-kegiatan penting komunitas kami.
          </p>

          <h3>Nilai-Nilai Kami</h3>
          <ul>
            <li><strong>Integritas:</strong> Menjaga kejujuran dan kredibilitas dalam setiap konten</li>
            <li><strong>Kolaborasi:</strong> Mendorong kerjasama antar anggota komunitas</li>
            <li><strong>Inovasi:</strong> Terus berinovasi dalam cara berbagi informasi</li>
            <li><strong>Kontribusi:</strong> Memberikan manfaat positif bagi masyarakat</li>
          </ul>

          <a href="tentangkami.php" class="btn btn-featured mt-4">Tentang Kami</a>
        </div>
      </div>
    </div>
  </section>
  <!-- End team Area -->

  <?php include './components/footer.php'; ?>


  <script src="js/vendor/jquery-2.2.4.min.js"></script>
  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
    integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"
    crossorigin="anonymous"></script>
  <script src="js/vendor/bootstrap.min.js"></script>
  <script src="js/jquery.ajaxchimp.min.js"></script>
  <script src="js/parallax.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/jquery.sticky.js"></script>
  <script src="js/main.js"></script>

  <script>
    // Newsletter form handling
    document.addEventListener('DOMContentLoaded', function() {
      const newsletterForm = document.querySelector('.newsletter-form');
      if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const email = this.querySelector('input[type="email"]').value;

          // Simple validation
          if (email && email.includes('@')) {
            alert('Terima kasih! Anda telah berlangganan newsletter kami.');
            this.reset();
          } else {
            alert('Mohon masukkan email yang valid.');
          }
        });
      }

      // Animate statistics on scroll
      const statNumbers = document.querySelectorAll('.stat-number');
      const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const target = entry.target;
            const finalNumber = parseInt(target.textContent.replace(/,/g, ''));
            animateNumber(target, 0, finalNumber, 2000);
            observer.unobserve(target);
          }
        });
      }, observerOptions);

      statNumbers.forEach(stat => observer.observe(stat));

      function animateNumber(element, start, end, duration) {
        const startTime = performance.now();
        const startNumber = start;
        const endNumber = end;

        function updateNumber(currentTime) {
          const elapsed = currentTime - startTime;
          const progress = Math.min(elapsed / duration, 1);

          const currentNumber = Math.floor(startNumber + (endNumber - startNumber) * progress);
          element.textContent = currentNumber.toLocaleString();

          if (progress < 1) {
            requestAnimationFrame(updateNumber);
          }
        }

        requestAnimationFrame(updateNumber);
      }

      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });
    });
  </script>
</body>

<?php if (session_status() === PHP_SESSION_NONE) {
  session_start();
} ?>
<!-- Tombol Tambah Artikel -->
<a href="<?= isset($_SESSION['author_id']) && in_array($_SESSION['author_type'], ['user', 'admin']) ? 'bloging/add_blog.php' : 'login.php' ?>"
  class="btn-tambah-artikel">
  + Tambah Artikel
</a>


<style>
  .btn-tambah-artikel {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background-color: #2E7D32;
    /* Forest green */
    color: white;
    padding: 14px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(46, 125, 50, 0.3);
    z-index: 9999;
    transition: background-color 0.3s ease;
  }

  .btn-tambah-artikel:hover {
    background-color: #1B5E20;
    /* Darker forest green */
  }

  .card {
    transition: all 0.4s ease;
    border: 3px solid #2E7D32;
    /* Forest green */
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(6px);
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.45);
    /* Forest green shadow */
    position: relative;
  }

  .card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 35px rgba(46, 125, 50, 0.6);
    /* Forest green shadow */
  }

  .card-img-top {
    height: 180px;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .card:hover .card-img-top {
    transform: scale(1.05);
  }

  .card-body {
    padding: 1rem 1.25rem;
  }

  .card-body h5 {
    font-weight: 600;
    font-size: 1.1rem;
    color: #2C2C2C;
    /* Charcoal */
  }

  .card-body p {
    font-size: 0.95rem;
    color: #555;
  }

  .meta-info {
    font-size: 0.8rem;
    color: #777;
  }

  .icon {
    margin-right: 5px;
    vertical-align: middle;
    color: #6c757d;
  }

  .btn-read-more {
    background: linear-gradient(135deg, #2E7D32, #C5E1A5);
    /* Forest green to lime */
    color: white;
    padding: 8px 20px;
    border-radius: 30px;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
  }

  .btn-read-more:hover {
    background: linear-gradient(135deg, #2E7D32, #2E7D32);
    /* Forest green */
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    color: #fff;
  }

  .btn-read-more span {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
  }

  .btn-read-more:hover span {
    transform: translateX(5px);
  }

  .no-results {
    background: #EEEEEE;
    /* Soft gray */
    padding: 50px;
    border-radius: 10px;
    text-align: center;
    font-size: 1.2rem;
    color: #777;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  /* Statistics Section Styles */
  .stat-card {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.45);
    /* Forest green shadow */
    border: 3px solid #2E7D32;
    /* Forest green */
    transition: all 0.4s ease;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(46, 125, 50, 0.6);
    /* Forest green shadow */
  }

  .stat-icon {
    font-size: 3rem;
    color: #2E7D32;
    /* Forest green */
    margin-bottom: 1rem;
  }

  .stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2C2C2C;
    /* Charcoal */
    margin-bottom: 0.5rem;
  }

  .stat-label {
    font-size: 1.1rem;
    color: #666;
    font-weight: 500;
  }

  /* Category Cards Styles */
  .category-card {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.45);
    /* Forest green shadow */
    border: 3px solid #2E7D32;
    /* Forest green */
    transition: all 0.4s ease;
    height: 100%;
  }

  .category-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(46, 125, 50, 0.6);
    /* Forest green shadow */
  }

  .category-icon {
    font-size: 3rem;
    color: #2E7D32;
    /* Forest green */
    margin-bottom: 1rem;
  }

  .category-content h4 {
    color: #2C2C2C;
    /* Charcoal */
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  .category-content p {
    color: #666;
    margin-bottom: 1.5rem;
  }

  .btn-category {
    background: linear-gradient(135deg, #2E7D32, #C5E1A5);
    /* Forest green to lime */
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-category:hover {
    background: linear-gradient(135deg, #2E7D32, #2E7D32);
    /* Forest green */
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    color: #fff;
  }

  /* Featured Article Styles */
  .featured-article-card {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.45);
    /* Forest green shadow */
    border: 3px solid #2E7D32;
    /* Forest green */
    transition: all 0.4s ease;
  }

  .featured-article-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(46, 125, 50, 0.6);
    /* Forest green shadow */
  }

  .featured-img {
    width: 100%;
    height: 300px;
    object-fit: cover;
  }

  .featured-content {
    padding: 2rem;
  }

  .featured-badge {
    background: linear-gradient(135deg, #F57C00, #FF9800);
    /* Orange burnt gradient */
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 1rem;
  }

  .featured-content h2 {
    color: #2C2C2C;
    /* Charcoal */
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.5rem;
  }

  .featured-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
  }

  .featured-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .featured-meta span {
    color: #777;
    font-size: 0.9rem;
  }

  .featured-meta i {
    margin-right: 5px;
    color: #2E7D32;
    /* Forest green */
  }

  .btn-featured {
    background: linear-gradient(135deg, #2E7D32, #C5E1A5);
    /* Forest green to lime */
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-featured:hover {
    background: linear-gradient(135deg, #2E7D32, #2E7D32);
    /* Forest green */
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    color: #fff;
  }

  /* Newsletter Styles */
  .newsletter-area {
    background: linear-gradient(135deg, #2E7D32, #C5E1A5);
    /* Forest green to lime */
    color: white;
  }

  .newsletter-content h2 {
    font-weight: 600;
    margin-bottom: 1rem;
  }

  .newsletter-content p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
  }

  .newsletter-form .input-group {
    max-width: 500px;
    margin: 0 auto;
  }

  .newsletter-form .form-control {
    border-radius: 25px 0 0 25px;
    border: none;
    padding: 12px 20px;
    font-size: 1rem;
  }

  .newsletter-form .form-control:focus {
    box-shadow: none;
    border-color: #fff;
  }

  .btn-newsletter {
    background: #2C2C2C;
    /* Charcoal */
    color: white;
    border-radius: 0 25px 25px 0;
    border: none;
    padding: 12px 24px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-newsletter:hover {
    background: #1a1a1a;
    /* Darker charcoal */
    color: white;
    transform: translateY(-2px);
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .featured-img {
      height: 200px;
    }

    .featured-content {
      padding: 1.5rem;
    }

    .featured-content h2 {
      font-size: 1.3rem;
    }

    .featured-meta {
      flex-direction: column;
      gap: 0.5rem;
    }

    .stat-number {
      font-size: 2rem;
    }

    .category-card {
      padding: 1.5rem;
    }
  }

  /* Team Section Styles */
  .team-left h3 {
    color: #2C2C2C;
    /* Charcoal */
    font-weight: 600;
    margin-bottom: 1rem;
    margin-top: 2rem;
  }

  .team-left h3:first-child {
    margin-top: 0;
  }

  .team-left p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
  }

  .team-left ul {
    color: #666;
    line-height: 1.8;
  }

  .team-left ul li {
    margin-bottom: 0.5rem;
  }

  .team-left strong {
    color: #2E7D32;
    /* Forest green */
  }

  .single-team {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(46, 125, 50, 0.45);
    /* Forest green shadow */
    border: 3px solid #2E7D32;
    /* Forest green */
    transition: all 0.4s ease;
    margin: 0 10px;
  }

  .single-team:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(46, 125, 50, 0.6);
    /* Forest green shadow */
  }

  .single-team .thumb {
    position: relative;
    overflow: hidden;
  }

  .single-team .thumb img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .single-team:hover .thumb img {
    transform: scale(1.1);
  }

  .single-team .thumb .align-items-center {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(46, 125, 50, 0.8);
    /* Forest green overlay */
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .single-team:hover .thumb .align-items-center {
    opacity: 1;
  }

  .single-team .thumb a {
    color: white;
    font-size: 1.2rem;
    margin: 0 10px;
    transition: transform 0.3s ease;
  }

  .single-team .thumb a:hover {
    transform: scale(1.2);
  }

  .single-team .meta-text {
    padding: 1.5rem;
  }

  .single-team .meta-text h4 {
    color: #2C2C2C;
    /* Charcoal */
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  .single-team .meta-text p {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
  }
</style>



</html>