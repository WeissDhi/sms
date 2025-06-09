<?php

session_start();
include './bloging/config.php';
include './components/navbar.php';

?>
<!DOCTYPE html>
<html lang="id">

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container py-5">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold">Tentang Syukron Makmun Mesir</h1>
            <p class="lead text-muted">Blog Islami dari Negeri Para Ulama</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p>
                            <strong>Syukron Makmun Mesir</strong> adalah sebuah blog Islami yang bertujuan menyebarkan hikmah, ilmu, dan inspirasi dari negeri Mesir. Blog ini menjadi wadah untuk berbagi catatan spiritual, kisah para ulama, serta pengalaman belajar Islam di salah satu pusat peradaban Islam terbesar.
                        </p>

                        <p>
                            Kami berharap setiap konten yang kami hadirkan dapat menambah wawasan, menenangkan hati, dan memperkuat keimanan para pembaca. Semua tulisan disusun dengan semangat dakwah dan berbasis pada sumber-sumber Islam yang terpercaya seperti Al-Qur’an, Hadis, dan penjelasan para ulama.
                        </p>

                        <p>
                            Terima kasih atas kunjungan dan dukungan Anda. Semoga kehadiran blog ini menjadi amal jariyah yang terus mengalir manfaatnya.
                        </p>

                        <blockquote class="blockquote mt-4">
                            <p class="mb-0 fst-italic">"Sampaikan dariku walau hanya satu ayat." (HR. Bukhari)</p>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php include './components/footer.php'; ?>
</body>

</html>