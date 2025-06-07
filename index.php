<?php
session_start();
include './bloging/config.php';

// Latest articles
$query_latest = "SELECT * FROM blogs 
                --  WHERE status = 'published' 
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
  <section
    class="banner-area relative"
    id="home"
    data-parallax="scroll"
    data-image-src="img/header-bg.jpg">
    <div class="overlay-bg overlay"></div>
    <div class="container">
      <div class="row fullscreen">
        <div
          class="banner-content d-flex align-items-center col-lg-12 col-md-12">
          <h1>
            A Discount Toner Cartridge <br />
            Is Better Than Ever.
          </h1>
        </div>
        <div
          class="head-bottom-meta d-flex justify-content-between align-items-end col-lg-12">
          <div class="col-lg-6 flex-row d-flex meta-left no-padding">
            <p><span class="lnr lnr-heart"></span> 15 Likes</p>
            <p><span class="lnr lnr-bubble"></span> 02 Comments</p>
          </div>
          <div
            class="col-lg-6 flex-row d-flex meta-right no-padding justify-content-end">
            <div class="user-meta">
              <h4 class="text-white">Mark wiens</h4>
              <p>12 Dec, 2017 11:21 am</p>
            </div>
            <img class="img-fluid user-img" src="img/user.jpg" alt="" />
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- End banner Area -->

  <!-- Start category Area -->
  <section class="category-area section-gap" id="news">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="menu-content pb-70 col-lg-8">
          <div class="title text-center">
            <h1 class="mb-10">Latest blogs from all categories</h1>
            <p>
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
          </div>
        </div>
      </div>
      <div class="active-cat-carusel">
        <?php while ($row = mysqli_fetch_assoc($result_latest)): ?>
          <div class="item single-cat">
            <img src="bloging/uploads/<?= htmlspecialchars(basename($row['image'])) ?>" alt="gambar" />

            <p class="date"><?= date('d M Y', strtotime($row['created_at'])); ?></p>
            <h4><a href="view_detail.php?id=<?= $row['id']; ?>"><?= $row['title']; ?></a></h4>
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
                </div>
            </div>
        </div>
        <div class="row">
            <?php if ($result_trending && mysqli_num_rows($result_trending) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result_trending)): ?>
                    <div class="col-lg-4 col-md-6 single-fashion">
                    <img src="bloging/uploads/<?= htmlspecialchars(basename($row['image'])) ?>" alt="gambar" />
                        <h4><?= $row['title'] ?></h4>
                        <p class="card-text">
                            <?= mb_strimwidth(strip_tags($row['content']), 0, 120, '...') ?>
                        </p>
                        <a href="view_detail.php?id=<?= $row['id'] ?>">Baca selengkapnya</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">Belum ada artikel trending minggu ini.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

  <!-- End fashion Area -->

  <!-- Start team Area -->
  <section class="team-area section-gap" id="team">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="menu-content pb-70 col-lg-8">
          <div class="title text-center">
            <h1 class="mb-10">About Blogger Team</h1>
            <p>Who are in extremely love with eco friendly system.</p>
          </div>
        </div>
      </div>
      <div class="row justify-content-center d-flex align-items-center">
        <div class="col-lg-6 team-left">
          <p>
            inappropriate behavior is often laughed off as “boys will be
            boys,” women face higher conduct standards especially in the
            workplace. That’s why it’s crucial that, as women, our behavior on
            the job is beyond reproach. inappropriate behavior is often
            laughed off as “boys will be boys,” women face higher conduct
            standards especially in the workplace. That’s why it’s crucial
            that.
          </p>
          <p>
            inappropriate behavior is often laughed off as “boys will be
            boys,” women face higher conduct standards especially in the
            workplace. That’s why it’s crucial that, as women.
          </p>
        </div>
        <div class="col-lg-6 team-right d-flex justify-content-center">
          <div class="row active-team-carusel">
            <div class="single-team">
              <div class="thumb">
                <img class="img-fluid" src="img/team1.jpg" alt="" />
                <div class="align-items-center justify-content-center d-flex">
                  <a href="#"><i class="fa fa-facebook"></i></a>
                  <a href="#"><i class="fa fa-twitter"></i></a>
                  <a href="#"><i class="fa fa-linkedin"></i></a>
                </div>
              </div>
              <div class="meta-text mt-30 text-center">
                <h4>Dora Walker</h4>
                <p>Senior Core Developer</p>
              </div>
            </div>
            <div class="single-team">
              <div class="thumb">
                <img class="img-fluid" src="img/team2.jpg" alt="" />
                <div class="align-items-center justify-content-center d-flex">
                  <a href="#"><i class="fa fa-facebook"></i></a>
                  <a href="#"><i class="fa fa-twitter"></i></a>
                  <a href="#"><i class="fa fa-linkedin"></i></a>
                </div>
              </div>
              <div class="meta-text mt-30 text-center">
                <h4>Lena Keller</h4>
                <p>Creative Content Developer</p>
              </div>
            </div>
          </div>
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
</body>

</html>