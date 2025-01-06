<?php
session_start();

// Redirige vers la page de connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user'])) {
    header('Location: ../admin.php');
    exit;
}

// Vérifie si le rôle de l'utilisateur est admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../admin.php');
    exit;
}

global $cnx;
require_once '../configs/config.php';

// Si un utilisateur doit être supprimé via la confirmation du modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    try {
        $stmt = $cnx->prepare("DELETE FROM user WHERE id = ?");
        $stmt->execute([$delete_id]);
        header('Location: admin.php'); // Redirige vers la même page après suppression
        exit;
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

// Récupérer tous les utilisateurs non-admin
$stmt = $cnx->prepare("SELECT * FROM user WHERE role != 'admin'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inclure les liens et la barre de navigation
include('../include/links.inc.php');
include('../include/navbar.inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'accueil Admin - StarTrip</title>
    <!-- Ajouter les styles Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark">

<div class="container mt-5">
    <h1 class="text-light">Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</h1>
    <p class="text-light">You are logged in as administrator.</p>

    <div class="mt-5">
        <h3 class="text-light">Manage Users</h3>
        <table class="table table-dark table-bordered">
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <!-- Formulaire pour la modification -->
                        <form action="edit_user.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-warning btn-sm">Modify</button>
                        </form>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-user-id="<?php echo $user['id']; ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
    var deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var deleteForm = document.getElementById('deleteForm');
        var deleteIdInput = document.getElementById('deleteId');
        deleteIdInput.value = userId;
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
