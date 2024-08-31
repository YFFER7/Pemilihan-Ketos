<?php
session_start();
require '../includes/config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Ambil data untuk dashboard
$sql = 'SELECT COUNT(*) as total_kandidat FROM kandidat';
$stmt = $pdo->query($sql);
$total_kandidat = $stmt->fetchColumn();

$sql = 'SELECT COUNT(*) as total_voters FROM voters';
$stmt = $pdo->query($sql);
$total_voters = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as belum_memilih FROM voters WHERE status = 'belum memilih'";
$stmt = $pdo->query($sql);
$belum_memilih = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as sudah_memilih FROM voters WHERE status = 'memilih'";
$stmt = $pdo->query($sql);
$sudah_memilih = $stmt->fetchColumn();

$sql = 'SELECT ketua, SUM(jumlah_suara) as jumlah_suara FROM kandidat GROUP BY ketua';
$stmt = $pdo->query($sql);
$votes = $stmt->fetchAll();

$kandidat_names = [];
$kandidat_votes = [];
foreach ($votes as $vote) {
    $kandidat_names[] = $vote['ketua'];
    $kandidat_votes[] = $vote['jumlah_suara'];
}
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
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&family=Roboto&display=swap" />

    <!-- Icons CSS -->
    <link rel="stylesheet" href="../assets/css/icons.css" />

    <!-- App CSS -->
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="../assets/css/dark-sidebar.css" />
    <link rel="stylesheet" href="../assets/css/dark-theme.css" />
    <!-- Link ke Chart.js dan chartjs-plugin-datalabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
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
                    <a href="javascript:;">
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
                        <div class="col-12 col-lg-3">
                            <div class="card text-white bg-warning mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Jumlah Kandidat</h5>
                                    <p class="card-text"><?php echo $total_kandidat; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Jumlah Pemilih</h5>
                                    <p class="card-text"><?php echo $total_voters; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <div class="card text-white bg-danger mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Belum Memilih</h5>
                                    <p class="card-text"><?php echo $belum_memilih; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <div class="card text-white bg-primary mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Sudah Memilih</h5>
                                    <p class="card-text"><?php echo $sudah_memilih; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h3 class="mt-5">Kemajuan Pemilihan</h3>
                    <div class="container mt-4">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <canvas id="barChart" height="250"></canvas>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <canvas id="pieChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p class="mb-0">Made By Reffy and Tataq</p>
    </div>
    </div>

    <!-- JavaScript -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/plugins/simplebar/js/simplebar.min.js"></script>
    <script src="../assets/plugins/metismenu/js/metisMenu.min.js"></script>
    <script src="../assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
    <script src="../assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="../assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="../assets/plugins/vectormap/jquery-jvectormap-in-mill.js"></script>
    <script src="../assets/plugins/vectormap/jquery-jvectormap-us-aea-en.js"></script>
    <script src="../assets/plugins/vectormap/jquery-jvectormap-uk-mill-en.js"></script>
    <script src="../assets/plugins/vectormap/jquery-jvectormap-au-mill.js"></script>
    <script src="../assets/plugins/apexcharts-bundle/js/apexcharts.min.js"></script>
    <script src="../assets/js/index.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Bar Chart (Horizontal)
    var ctx = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($kandidat_names); ?>,
            datasets: [{
                label: 'Jumlah Suara',
                data: <?php echo json_encode($kandidat_votes); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Membuat grafik batang horizontal
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    // Pie Chart
    var kandidat_names = <?php echo json_encode($kandidat_names); ?>;
    var kandidat_votes = <?php echo json_encode($kandidat_votes); ?>;

    var ctx2 = document.getElementById('pieChart').getContext('2d');
    var pieChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: kandidat_names,
            datasets: [{
                label: 'Jumlah Suara',
                data: kandidat_votes,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 206, 86, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    formatter: (value, ctx) => {
                        let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let percentage = (value * 100 / sum).toFixed(2) + "%";
                        return percentage;
                    },
                    color: '#fff',
                }
            }
        }
    });

    // Fungsi untuk memperbarui data grafik pie
    function updateChart(chart, index) {
        kandidat_votes[index]++;
        chart.data.datasets[0].data = kandidat_votes;
        chart.update();
    }

    // Event listener untuk tombol simulasi perubahan data
    document.getElementById('voteAgung').addEventListener('click', function() {
        updateChart(pieChart, 0);
    });

    document.getElementById('voteAsep').addEventListener('click', function() {
        updateChart(pieChart, 1);
    });

    document.getElementById('voteCahya').addEventListener('click', function() {
        updateChart(pieChart, 2);
    });

    document.getElementById('voteSkip').addEventListener('click', function() {
        updateChart(pieChart, 3);
    });
</script>

</body>

</html>
