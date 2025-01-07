<?php
// Start the session to manage user sessions
session_start();

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Declare the database connection as a global variable
global $cnx;

// Include configuration and common components
require_once '../configs/config.php';
include('../include/links.inc.php'); // Include links (e.g., CSS/JS files)
include('../include/navbar.inc.php'); // Include the navigation bar

// Redirect the user to the homepage if they are already logged in
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Initialize an error message variable
$error = '';

// Handle the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted username and password (default to empty if not set)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if both username and password are provided
    if (!empty($username) && !empty($password)) {
        try {
            // Query the database to fetch the user by username
            $stmt = $cnx->prepare("SELECT * FROM user WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch user data as an associative array

            // Verify the password using the hashed value stored in the database
            if ($user && password_verify($password, $user['password'])) {
                // Store user details in the session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                // Redirect based on the user's role
                if ($user['role'] === 'admin') {
                    header('Location: admin.php'); // Redirect to the admin panel
                } else {
                    header('Location: index.php'); // Redirect to the homepage
                }
                exit;
            } else {
                // Set an error message if authentication fails
                $error = 'Invalid username or password';
            }
        } catch (Exception $e) {
            // Handle any exceptions that occur during the login process
            $error = 'An error occurred. Please try again.';
        }
    } else {
        // Set an error message if any field is left empty
        $error = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StarTrip</title>
</head>
<body class="bg-dark">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-person-circle text-primary"></i> Login
                    </h2>

                    <!-- Display error message if there is one -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login form -->
                    <form method="post" action="">
                        <!-- Username field -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text"
                                       class="form-control"
                                       id="username"
                                       name="username"
                                       required
                                       autocomplete="username">
                            </div>
                        </div>

                        <!-- Password field -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       required
                                       autocomplete="current-password">
                            </div>
                        </div>

                        <!-- Login button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>
                    </form>

                    <!-- Registration link -->
                    <div class="mt-3 text-center">
                        <p class="mb-0">Don't have an account?
                            <a href="register.php" class="text-primary">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
