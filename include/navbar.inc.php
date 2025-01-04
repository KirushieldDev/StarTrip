<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="../src/index.php">
            <i class="bi bi-rocket"></i> Travia
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="../src/index.php">
                        <i class="bi bi-search"></i> Search a trip
                    </a>
                </li>
                <?php if(isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../src/bookings.php">
                            <i class="bi bi-journal-text"></i> My bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../src/cart.php">
                            <i class="bi bi-cart"></i> My cart
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center">
                <!-- Font Toggle Button -->
                <button id="fontToggleBtn" class="btn btn-outline-dark me-2">
                    <i class="bi bi-file-earmark-font"></i>
                </button>

                <!-- Login/User Menu -->
                <?php if(isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="../scripts/logout.php">
                                    <i class="bi bi-box-arrow-right text-danger"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="../src/login.php" class="btn btn-outline-dark">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>