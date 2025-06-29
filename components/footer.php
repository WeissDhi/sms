<!-- footer.php -->
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">

<!-- Bootstrap 5 (jika belum dipakai) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<footer class="footer-area bg-dark text-white py-5 mt-5">
  <div class="container">
    <div class="row g-4">
      
      <!-- Tentang Kami -->
      <div class="col-md-4">
        <h5 class="fw-bold mb-3">Tentang Kami</h5>
        <p class="small text-light">
          IKDAR CAIRO adalah platform yang dikelola oleh mahasiswa Indonesia di Kairo, bertujuan untuk menyebarkan informasi dan pemikiran dalam berbagai bidang seperti humaniora, Islamologi, dan sastra.
        </p>
      </div>

      <!-- Kategori -->
      <div class="col-md-4">
        <h5 class="fw-bold mb-3">Kategori</h5>
        <ul class="list-unstyled">
          <?php
          // Ambil kategori dari database
          $footer_categories = [];
          $resCat = $conn->query("SELECT id, category FROM category ORDER BY category ASC LIMIT 7");
          while ($cat = $resCat->fetch_assoc()) {
              echo '<li><a href="category.php?id=' . $cat['id'] . '" class="footer-link">' . htmlspecialchars($cat['category']) . '</a></li>';
          }
          ?>
        </ul>
      </div>

      <!-- Sosial Media + Scroll Top -->
      <div class="col-md-4">
        <h5 class="fw-bold mb-3">Ikuti Kami</h5>
        <div class="d-flex gap-3">
          <a href="https://facebook.com/ikdar.cairo" class="social-icon bg-light text-dark rounded-circle d-flex align-items-center justify-content-center facebook-icon">
            <i class="fa fa-facebook"></i>
          </a>
          <a href="https://instagram.com/ikdar_cairo" class="social-icon bg-light text-dark rounded-circle d-flex align-items-center justify-content-center instagram-icon">
            <i class="fa fa-instagram"></i>
          </a>
        </div>

        <button onclick="scrollToTop()" class="btn btn-outline-light btn-sm mt-4">
          ↑ Kembali ke atas
        </button>
      </div>
    </div>

    <!-- Copyright -->
    <div class="row mt-4 pt-3 border-top border-secondary">
      <div class="col text-center">
        <p class="mb-0 small text-secondary">
          &copy; <?= date('Y') ?> IKDAR CAIRO. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</footer>

<style>
  .footer-link {
    color: #bbb;
    display: block;
    margin-bottom: 6px;
    transition: 0.3s ease;
    text-decoration: none;
  }

  .footer-link:hover {
    color: #fff;
    transform: translateX(5px);
  }

  .social-icon {
    width: 40px;
    height: 40px;
    font-size: 18px;
    transition: all 0.3s ease;
  }

  .social-icon:hover {
    background-color: #8fc333; /* hijau */
    color: #fff !important;
  }

  .facebook-icon:hover {
    color: #1877f2 !important;
  }

  .instagram-icon:hover {
    color: #e1306c !important;
  }

  .footer-area {
    background: linear-gradient(to right, #1d1d1d, #111);
  }
</style>

<script>
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
</script>
