<!-- Sidebar -->
<div class="sidebar bg-primary-dark text-white" id="sidebar-wrapper">
    <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
        <i class="fas fa-trophy me-2"></i>SportManager
    </div>
    <div class="list-group list-group-flush my-3">
        <a href="<?= BASE_URL ?>dashboard.php" class="list-group-item list-group-item-action bg-transparent second-text active">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="<?= BASE_URL ?>modules/equipes/index.php" class="list-group-item list-group-item-action bg-transparent second-text">
            <i class="fas fa-users me-2"></i>Équipes
        </a>
        <a href="<?= BASE_URL ?>modules/tournois/index.php" class="list-group-item list-group-item-action bg-transparent second-text">
            <i class="fas fa-flag me-2"></i>Tournois
        </a>
        <a href="<?= BASE_URL ?>modules/matches/index.php" class="list-group-item list-group-item-action bg-transparent second-text">
            <i class="fas fa-futbol me-2"></i>Matches
        </a>
        <a href="<?= BASE_URL ?>modules/auth/login.php" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold mt-5">
            <i class="fas fa-power-off me-2"></i>Déconnexion
        </a>
    </div>
</div>

<!-- Page Content -->
<div class="page-content-wrapper w-100">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent border-bottom px-4 py-3">
        <div class="container-fluid">
            <button class="btn" id="sidebarToggle">
                <i class="fas fa-bars text-white"></i>
            </button>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle second-text fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i>Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profil</a></li>
                            <li><a class="dropdown-item" href="#">Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>modules/auth/login.php">Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid px-4 py-4">