<?php
session_start();
require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];

    $stmt = $pdo->prepare('SELECT * FROM voters WHERE token = ?');
    $stmt->execute([$token]);
    $voter = $stmt->fetch();

    if ($voter) {
        $_SESSION['voter_id'] = $voter['id'];
        header('Location: voting.php');
        exit;
    } else {
        $error = "Token tidak valid!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Pemilih</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">Login Pemilih</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="token">Masukkan Token</label>
                    <input type="text" class="form-control" id="token" name="token" required>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Login</button>
                <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
