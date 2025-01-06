<?php
session_start();
require_once '../configs/config.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../src/index.php');
    exit;
}

$id_edit = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);

if (!$id_edit) {
    header('Location: admin.php');
    exit;
}

$stmt = $cnx->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$id_edit]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validation du format de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'The email address is invalid.';
    } else {
        try {
            $stmt = $cnx->prepare("UPDATE user SET username = ?, email = ? WHERE id = ?;");
            $stmt->execute([$username, $email, $id_edit]);
            header("Location: admin.php");
        } catch (Exception $e) {
            $error = 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify my profile - StarTrip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-dark">

<?php include('../include/navbar.inc.php'); ?>

<div class="container mt-5">
    <h2 class="text-light text-center mb-4">Modify the profile</h2>

    <form method="POST" action="edit_user.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_edit); ?>">

        <div class="mb-3">
            <label for="username" class="form-label text-light">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label text-light">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save modification
            </button>
        </div>
    </form>

    <div class="text-center mt-3">
        <a href="../src/admin.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left-circle"></i> Comeback to homepage
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
