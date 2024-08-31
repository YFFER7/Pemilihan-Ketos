<?php
session_start();
require '../includes/config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Fungsi untuk menambahkan pemilih
if (isset($_POST['add_voter'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $kelas = $_POST['kelas'];
    $token = $_POST['token'];
    $status = $_POST['status'];

    $sql = 'INSERT INTO voters (nama_lengkap, kelas, token, status) VALUES (?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nama_lengkap, $kelas, $token, $status]);

    header('Location: pemilih.php');
    exit();
}

// Fungsi untuk mengupdate pemilih
if (isset($_POST['update_voter'])) {
    $id = $_POST['id'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $kelas = $_POST['kelas'];
    $token = $_POST['token'];
    $status = $_POST['status'];
    $hasil = $_POST['hasil']; // Menambahkan hasil ke update

    $sql = 'UPDATE voters SET nama_lengkap = ?, kelas = ?, token = ?, status = ?, hasil = ? WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nama_lengkap, $kelas, $token, $status, $hasil, $id]);

    header('Location: pemilih.php');
    exit();
}

// Fungsi untuk menghapus suara
if (isset($_GET['delete_vote_id'])) {
    $id = $_GET['delete_vote_id'];

    // Logika untuk menghapus suara terkait dengan pemilih
    $sql = 'UPDATE voters SET status = "belum_memilih", hasil = NULL WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header('Location: pemilih.php');
    exit();
}

// Fungsi untuk menghapus akun
if (isset($_GET['delete_account_id'])) {
    $id = $_GET['delete_account_id'];

    // Hapus akun pemilih
    $sql = 'DELETE FROM voters WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header('Location: pemilih.php');
    exit();
}

// Ambil semua data pemilih
$sql = 'SELECT * FROM voters';
$stmt = $pdo->query($sql);
$voters = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Dashboard Pemilihan</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon-32x32.png" type="image/png" />

    <!-- Vector CSS -->
    <link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet" />

    <!-- Plugins -->
    <link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
    <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
    <link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />

    <!-- Loader -->
    <link href="assets/css/pace.min.css" rel="stylesheet" />
    <script src="assets/js/pace.min.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&family=Roboto&display=swap" />

    <!-- Icons CSS -->
    <link rel="stylesheet" href="../assets/css/icons.css" />

    <!-- App CSS -->
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="../assets/css/dark-sidebar.css" />
    <link rel="stylesheet" href="../assets/css/dark-theme.css" />
    <!-- Link ke Chart.js dan chartjs-plugin-datalabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

</head>

<body>
    <!-- Wrapper -->
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar-wrapper" data-simplebar="true">
            <div class="sidebar-header">
                <div>
                    <img src="assets/images/logo-icon.png" class="logo-icon-2" alt="Logo" />
                </div>
                <div>
                    <h4 class="logo-text">Syndash</h4>
                </div>
                <a href="javascript:;" class="toggle-btn ms-auto"><i class="bx bx-menu"></i></a>
            </div>

            <!-- Navigation -->
            <ul class="metismenu" id="menu">
                <li>
                    <a href="admin_dashboard.php">
                        <div class="parent-icon icon-color-1"><i class="bx bx-home-alt"></i></div>
                        <div class="menu-title">Dashboard</div>
                    </a>
                </li>
                <li>
                    <a href="pemilih.php">
                        <div class="parent-icon icon-color-1"><i class="bx bx-home-alt"></i></div>
                        <div class="menu-title">Pemilih</div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Header -->
        <header class="top-header">
            <nav class="navbar navbar-expand">
                <div class="left-topbar d-flex align-items-center">
                    <a href="javascript:;" class="toggle-btn"><i class="bx bx-menu"></i></a>
                </div>
            </nav>
        </header>

        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <!-- Page Content -->
            <div class="page-content-wrapper">
                <div class="page-content">
                    <div class="row">
                        <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addVoterModal">
                            Tambah Pemilih
                        </button>
                        <!-- Tabel Data Pemilih -->
                        <div class="table-responsive">
                            <table id="votersTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Lengkap</th>
                                        <th>Kelas</th>
                                        <th>Token</th>
                                        <th>Status</th>
                                        <th>Hasil</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($voters as $index => $voter): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($voter['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($voter['kelas']); ?></td>
                                        <td><?php echo htmlspecialchars($voter['token']); ?></td>
                                        <td><?php echo htmlspecialchars($voter['status']); ?></td>
                                        <td><?php echo htmlspecialchars($voter['hasil']) ?: 'Belum Memilih'; ?></td> <!-- Tampilkan hasil jika ada -->
                                        <td>
                                            <!-- Tombol Hapus Suara dan Hapus Akun -->
                                            <a href="pemilih.php?delete_vote_id=<?php echo $voter['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus suara pemilih ini?');">Hapus Suara</a>
                                            <a href="pemilih.php?delete_account_id=<?php echo $voter['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus akun pemilih ini?');">Hapus Akun</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Pemilih -->
        <div class="modal fade" id="addVoterModal" tabindex="-1" aria-labelledby="addVoterModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addVoterModalLabel">Tambah Pemilih</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="pemilih.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label for="kelas" class="form-label">Kelas</label>
                                <input type="text" class="form-control" id="kelas" name="kelas" required>
                            </div>
                            <div class="mb-3">
                                <label for="token" class="form-label">Token</label>
                                <input type="text" class="form-control" id="token" name="token" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="belum_memilih">Belum Memilih</option>
                                    <option value="sudah_memilih">Sudah Memilih</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" name="add_voter">Tambah Pemilih</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Pemilih -->
        <div class="modal fade" id="editVoterModal" tabindex="-1" aria-labelledby="editVoterModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editVoterModalLabel">Edit Pemilih</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="pemilih.php" method="POST">
                        <input type="hidden" name="id" id="editVoterId">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="edit_nama_lengkap" name="nama_lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_kelas" class="form-label">Kelas</label>
                                <input type="text" class="form-control" id="edit_kelas" name="kelas" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_token" class="form-label">Token</label>
                                <input type="text" class="form-control" id="edit_token" name="token" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="belum_memilih">Belum Memilih</option>
                                    <option value="sudah_memilih">Sudah Memilih</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_hasil" class="form-label">Hasil</label>
                                <input type="text" class="form-control" id="edit_hasil" name="hasil">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" name="update_voter">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- JS Files -->
        <script src="../assets/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/js/jquery.min.js"></script>
        <script src="../assets/js/app.js"></script>
        <script src="../assets/js/dark-mode.js"></script>

        <!-- Inisialisasi DataTables -->
        <script>
            $(document).ready(function() {
                $('#votersTable').DataTable();
            });

            // Fungsi untuk mengisi modal edit dengan data pemilih
            function editVoter(id, nama, kelas, token, status, hasil) {
                $('#editVoterId').val(id);
                $('#edit_nama_lengkap').val(nama);
                $('#edit_kelas').val(kelas);
                $('#edit_token').val(token);
                $('#edit_status').val(status);
                $('#edit_hasil').val(hasil);
                $('#editVoterModal').modal('show');
            }
        </script>
    </div>
</body>

</html>
