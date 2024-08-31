<?php
session_start();
require '../includes/config.php';

if (!isset($_SESSION['voter_id'])) {
    header('Location: login.php');
    exit();
}

$voter_id = $_SESSION['voter_id'];
$stmt = $pdo->prepare('SELECT * FROM kandidat');
$stmt->execute();
$kandidat = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kandidat_id = $_POST['kandidat_id'];

    $stmt = $pdo->prepare('UPDATE voters SET voted_for = ?, has_voted = 1 WHERE id = ?');
    $stmt->execute([$kandidat_id, $voter_id]);

    $_SESSION['has_voted'] = true;
    header('Location: voting.php');
    exit();
}

$has_voted = isset($_SESSION['has_voted']) ? $_SESSION['has_voted'] : false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pemilihan Ketua OSIS</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <script>
        let selectedCandidateId = null;

        function selectCandidate(id) {
            selectedCandidateId = id;
            document.getElementById('kandidat_id').value = selectedCandidateId;
            document.getElementById('coblosButton').disabled = false;

            // Reset all buttons to default
            document.querySelectorAll('.select-button').forEach(button => {
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-primary');
                button.innerText = 'Pilih';
            });

            // Highlight the selected button
            const selectedButton = document.getElementById('select_' + id);
            selectedButton.classList.remove('btn-outline-primary');
            selectedButton.classList.add('btn-success');
            selectedButton.innerText = 'Dipilih';
        }
    </script>
</head>

<body>
<svg id="wave" style="transform:rotate(180deg); transition: 0.3s" viewBox="0 0 1440 490" version="1.1" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="sw-gradient-0" x1="0" x2="0" y1="1" y2="0"><stop stop-color="rgba(0, 141, 218, 1)" offset="0%"></stop><stop stop-color="rgba(255, 255, 255, 1)" offset="100%"></stop></linearGradient></defs><path style="transform:translate(0, 0px); opacity:1" fill="url(#sw-gradient-0)" d="M0,245L120,220.5C240,196,480,147,720,171.5C960,196,1200,294,1440,351.2C1680,408,1920,425,2160,416.5C2400,408,2640,376,2880,302.2C3120,229,3360,114,3600,89.8C3840,65,4080,131,4320,171.5C4560,212,4800,229,5040,204.2C5280,180,5520,114,5760,98C6000,82,6240,114,6480,138.8C6720,163,6960,180,7200,171.5C7440,163,7680,131,7920,114.3C8160,98,8400,98,8640,122.5C8880,147,9120,196,9360,228.7C9600,261,9840,278,10080,245C10320,212,10560,131,10800,106.2C11040,82,11280,114,11520,163.3C11760,212,12000,278,12240,318.5C12480,359,12720,376,12960,383.8C13200,392,13440,392,13680,343C13920,294,14160,196,14400,204.2C14640,212,14880,327,15120,343C15360,359,15600,278,15840,277.7C16080,278,16320,359,16560,351.2C16800,343,17040,245,17160,196L17280,147L17280,490L17160,490C17040,490,16800,490,16560,490C16320,490,16080,490,15840,490C15600,490,15360,490,15120,490C14880,490,14640,490,14400,490C14160,490,13920,490,13680,490C13440,490,13200,490,12960,490C12720,490,12480,490,12240,490C12000,490,11760,490,11520,490C11280,490,11040,490,10800,490C10560,490,10320,490,10080,490C9840,490,9600,490,9360,490C9120,490,8880,490,8640,490C8400,490,8160,490,7920,490C7680,490,7440,490,7200,490C6960,490,6720,490,6480,490C6240,490,6000,490,5760,490C5520,490,5280,490,5040,490C4800,490,4560,490,4320,490C4080,490,3840,490,3600,490C3360,490,3120,490,2880,490C2640,490,2400,490,2160,490C1920,490,1680,490,1440,490C1200,490,960,490,720,490C480,490,240,490,120,490L0,490Z"></path></svg>
    <div class="header">
        <div class="logo-container">
            <img src="../assets/images/icons/ifsu.png" alt="Logo MPK" class="logo mpk-logo">
            <img src="../assets/images/icons/ifsu.png" alt="Logo Sekolah" class="logo school-logo">
            <img src="../assets/images/icons/ifsu.png" alt="Logo OSIS" class="logo osis-logo">
        </div>
        <h1>Pemilihan Ketua OSIS</h1>
        <h2>SMK Informatika Sumedang</h2>
        <h3>Tahun 2025-2026</h3>
    </div>
    <div class="container">
        <div class="row">
            <?php foreach ($kandidat as $k): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="../assets/images/<?= htmlspecialchars($k['img']) ?>" class="card-img-top"
                        alt="<?= htmlspecialchars($k['ketua']) ?>">
                    <div class="card-body">
                        <h5 class="card-title font-weight-bold"><?= htmlspecialchars($k['ketua']) ?></h5>
                        <h5 class="card-title font-weight-bold"><?= htmlspecialchars($k['wakil1']) ?></h5>
                        <h5 class="card-title font-weight-bold"><?= htmlspecialchars($k['wakil2']) ?></h5>
                        <br>
                        <p class="card-text text-muted mb-2">Visi: <?= htmlspecialchars($k['visi']) ?></p>
                        <br>
                        <p class="card-text text-muted mb-2">Misi: <?= htmlspecialchars($k['misi']) ?></p>
                        <?php if (!$has_voted): ?>
                        <!-- Tombol untuk memilih kandidat -->
                        <button id="select_<?= $k['id'] ?>" class="btn btn-outline-primary select-button"
                            onclick="selectCandidate(<?= $k['id'] ?>)">Pilih</button>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-block" disabled>Anda sudah memilih</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (!$has_voted): ?>
        <form method="POST">
            <input type="hidden" name="kandidat_id" id="kandidat_id">
            <button type="submit" class="btn btn-primary btn-block mt-3" id="coblosButton" disabled>Coblos</button>
        </form>
        <?php endif; ?>

        <?php if ($has_voted): ?>
        <script>
            Swal.fire({
                title: 'Terima kasih telah memilih!',
                text: "Pilih 'Keluar' untuk keluar atau 'Kembali' untuk melihat hasil.",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Keluar',
                cancelButtonText: 'Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            });
        </script>
        <?php endif; ?>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
