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
    <link rel="shortcut icon" href="img/sms.png" />
    <!-- Author Meta -->
    <meta name="author" content="colorlib" />
    <!-- Meta Description -->
    <meta name="description" content="" />
    <!-- Meta Keyword -->
    <meta name="keywords" content="" />
    <!-- meta character set -->
    <meta charset="UTF-8" />
    <!-- Site Title -->
    <title>Syukron Makmun Society-Mesir - Tentang Kami</title>

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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(46, 125, 50, 0.7), rgba(46, 125, 50, 0.7)), url('img/mesir-bg.jpg');
            background-size: cover;
            background-position: center;
            color: #FAFAF0; /* Ivory */
            padding: 100px 0;
            margin-bottom: 50px;
            position: relative;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to top, #FAFAF0, transparent); /* Ivory */
        }

        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            background: #EEEEEE; /* Soft gray */
            box-shadow: 0 5px 15px rgba(46,125,50,0.08);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(46,125,50,0.18);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #2E7D32; /* Forest green */
            margin-bottom: 20px;
        }

        .quote-section {
            background-color: #EEEEEE; /* Soft gray */
            padding: 60px 0;
            margin: 50px 0;
        }

        .arabic-text {
            font-family: 'Traditional Arabic', serif;
            font-size: 2rem;
            color: #2E7D32; /* Forest green */
            margin-bottom: 20px;
        }

        .mission-card {
            border-left: 4px solid #2E7D32; /* Forest green */
            padding: 20px;
            margin: 20px 0;
            background: #C5E1A5; /* Lime */
            border-radius: 0 15px 15px 0;
            color: #2C2C2C; /* Charcoal */
        }

        .team-section {
            padding: 50px 0;
        }

        .team-member {
            text-align: center;
            margin-bottom: 30px;
        }

        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
            border: 5px solid #FAFAF0; /* Ivory */
            box-shadow: 0 5px 15px rgba(46,125,50,0.08);
        }
        .card.border-0.shadow-sm {
            background: #EEEEEE; /* Soft gray */
        }
        .card-body p, .card-body h2, .card-body .lead {
            color: #2C2C2C; /* Charcoal */
        }
        .badge.bg-success {
            background: #2E7D32 !important; /* Forest green */
            color: #FAFAF0 !important; /* Ivory */
        }
        body {
            background: #FAFAF0 !important; /* Ivory */
            color: #2C2C2C;
        }

        /* Social Media Cards Styles */
        .social-media-card {
            text-decoration: none;
            display: block;
        }

        .social-media-card:hover {
            text-decoration: none;
        }

        .social-card {
            background: #EEEEEE; /* Soft gray */
            border-radius: 15px;
            padding: 25px 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .social-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2E7D32, #4CAF50);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .social-card:hover::before {
            transform: scaleX(1);
        }

        .social-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(46,125,50,0.2);
            border-color: #2E7D32;
        }

        .instagram-card:hover {
            background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: white;
        }

        .facebook-card:hover {
            background: linear-gradient(135deg, #3b5998 0%, #4c70ba 100%);
            color: white;
        }

        .social-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin: 0 auto 15px auto;
            box-shadow: 0 2px 8px rgba(46,125,50,0.08);
        }
        .instagram-card .social-icon {
            background: radial-gradient(circle at 30% 110%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
            color: #fff;
        }
        .facebook-card .social-icon {
            background: #3b5998;
            color: #fff;
        }
        .social-card:hover .social-icon {
            box-shadow: 0 4px 16px rgba(46,125,50,0.18);
            transform: scale(1.12) rotate(-6deg);
        }
        .social-content h5 {
            font-weight: 600;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .social-content p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin: 0;
            transition: all 0.3s ease;
        }

        .social-card:hover .social-content h5,
        .social-card:hover .social-content p {
            color: white;
        }
    </style>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Syukron Makmun Society-Mesir</h1>
            <p class="lead fs-4">Blog Islami dari Negeri Para Ulama</p>
            <div class="mt-4">
                <span class="badge bg-success p-2 px-4 fs-6">Sejak 2025</span>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Vision & Mission -->
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="mission-card">
                    <h3 class="h4 mb-3">Visi Kami</h3>
                    <p class="mb-0">Menjadi wadah inspirasi dan pembelajaran Islam yang terpercaya, menghubungkan umat dengan khazanah keilmuan dari negeri Mesir.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mission-card">
                    <h3 class="h4 mb-3">Misi Kami</h3>
                    <p class="mb-0">Menyebarkan ilmu dan hikmah Islam melalui konten berkualitas, menginspirasi generasi muda, dan memperkuat ukhuwah islamiyah.</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h4>Konten Berkualitas</h4>
                    <p>Artikel dan tulisan yang disusun berdasarkan sumber-sumber terpercaya dalam Islam.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <h4>Inspirasi Islami</h4>
                    <p>Kisah-kisah inspiratif dari para ulama dan pengalaman spiritual di negeri Mesir.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h4>Ukhuwah Islamiyah</h4>
                    <p>Mempererat tali silaturahmi antar umat Islam melalui konten yang membangun.</p>
                </div>
            </div>
        </div>

        <!-- Quote Section -->
        <section class="quote-section text-center">
            <div class="container">
                <div class="arabic-text">بَلِّغُوا عَنِّي وَلَوْ آيَةً</div>
                <blockquote class="blockquote">
                    <p class="mb-0 fs-4 fst-italic">"Sampaikan dariku walau hanya satu ayat."</p>
                    <footer class="blockquote-footer mt-2">HR. Bukhari</footer>
                </blockquote>
            </div>
        </section>

        <!-- About Content -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="h3 mb-4 text-center">Tentang Kami</h2>
                        <p class="lead mb-4">
                            <strong>Syukron Makmun Society-Mesir</strong> adalah sebuah blog Islami yang bertujuan menyebarkan hikmah, ilmu, dan inspirasi dari negeri Mesir. Blog ini menjadi wadah untuk berbagi catatan spiritual, kisah para ulama, serta pengalaman belajar Islam di salah satu pusat peradaban Islam terbesar.
                        </p>
                        <p class="mb-4">
                            Kami berharap setiap konten yang kami hadirkan dapat menambah wawasan, menenangkan hati, dan memperkuat keimanan para pembaca. Semua tulisan disusun dengan semangat dakwah dan berbasis pada sumber-sumber Islam yang terpercaya seperti Al-Qur'an, Hadis, dan penjelasan para ulama.
                        </p>
                        <p class="mb-0">
                            Terima kasih atas kunjungan dan dukungan Anda. Semoga kehadiran blog ini menjadi amal jariyah yang terus mengalir manfaatnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5 text-center">
                        <h3 class="h4 mb-4">Ikuti Kami di Media Sosial</h3>
                        <p class="lead mb-4">Dapatkan update terbaru dan konten inspiratif melalui media sosial kami</p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-3">
                                <a href="https://www.instagram.com/ikdar_cairo" target="_blank" class="social-media-card">
                                    <div class="social-card instagram-card">
                                        <div class="social-icon">
                                            <i class="fab fa-instagram"></i>
                                        </div>
                                        <div class="social-content">
                                            <h5 class="mb-1">Instagram</h5>
                                            <p class="mb-0">@ikdar_cairo</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="https://web.facebook.com/ikdar.cairo?_rdc=1&_rdr#" target="_blank" class="social-media-card">
                                    <div class="social-card facebook-card">
                                        <div class="social-icon">
                                            <i class="fab fa-facebook-f"></i>
                                        </div>
                                        <div class="social-content">
                                            <h5 class="mb-1">Facebook</h5>
                                            <p class="mb-0">Ikdar Cairo</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome (kit removed, now using CDN above) -->

    <?php include './components/footer.php'; ?>
</body>

</html>