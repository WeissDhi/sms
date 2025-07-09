<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil semua penulis
$result = $conn->query("SELECT * FROM penulis ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="shortcut icon" href="../../../img/sms.png" />
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .page-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 2px solid #e9ecef;
            padding: 1rem;
        }
        
        .table td {
            vertical-align: middle;
            padding: 1rem;
            color: #495057;
        }
        
        .action-buttons .btn {
            padding: 0.5rem;
            border-radius: 0.5rem;
            margin: 0 0.15rem;
            transition: all 0.2s;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-buttons .btn i {
            font-size: 0.9rem;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            color: white;
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #2980b9, #2471a3);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border: none;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(46,204,113,0.2);
        }
        
        /* DataTables Styling */
        .dataTables_wrapper {
            padding: 1rem 0;
        }
        
        .dataTables_filter input {
            width: 300px !important;
            padding: 0.5rem 1rem !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            margin-left: 0.5rem !important;
            font-size: 0.9rem !important;
        }
        
        .dataTables_filter input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25) !important;
            outline: none !important;
        }
        
        .dataTables_length select {
            padding: 0.4rem 2rem 0.4rem 1rem !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            font-size: 0.9rem !important;
        }
        
        .dataTables_length select:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25) !important;
            outline: none !important;
        }
        
        .dataTables_info {
            padding: 1rem 0 !important;
            color: #6c757d !important;
            font-size: 0.9rem !important;
        }
        
        .dataTables_paginate {
            margin-top: 1rem !important;
        }
        
        .paginate_button {
            padding: 0.5rem 1rem !important;
            margin: 0 0.2rem !important;
            border-radius: 0.5rem !important;
            border: 1px solid #dee2e6 !important;
            background: white !important;
            color: var(--primary-color) !important;
        }
        
        .paginate_button.current {
            background: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: white !important;
        }
        
        .paginate_button:hover {
            background: #e9ecef !important;
            border-color: #dee2e6 !important;
            color: var(--primary-color) !important;
        }
    </style>
</head>

<body>
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?php echo $_SESSION['success']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?php echo $_SESSION['error']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['error']); endif; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Dashboard Admin</h2>
                    <p>Kelola seluruh penulis dan artikel</p>
                </div>
                <div>
                    <a href="add_penulis.php" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Tambah Penulis Baru
                    </a>
                    <a href="../../../index.php" class="btn btn-outline-light ms-2">
                        <i class="bi bi-house-door-fill"></i> HOME
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="userTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['fname']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="penulis_detail.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit_penulis.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete_penulis.php" class="btn btn-danger btn-sm delete-penulis" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['username']); ?>" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#userTable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                    infoEmpty: "Tidak ada data yang tersedia",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                order: [[0, 'desc']], // Sort by ID column by default
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-select');
                }
            });

            // Sweet Alert Delete Confirmation
            $('.delete-penulis').on('click', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Anda akan menghapus penulis "${userName}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `delete_penulis.php?id=${userId}`;
                    }
                });
            });
        });
    </script>
</body>

</html>