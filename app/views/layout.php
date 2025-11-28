<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Hotel Yokoso') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <?php if (!empty($extra_css ?? '')): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($extra_css) ?>">
    <?php endif; ?>

    <link rel="icon" href="/assets/img/favicon.ico">

    <!-- Estilos para layout fijo (navbar + footer) -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa; 
        }

        .main-wrapper {
            flex: 1;
            margin-top: 16px;
        }

        .navbar-dashboard {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .footer-dashboard {
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1030;
            box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body<?= !empty($body_class ?? '') ? ' class="' . htmlspecialchars($body_class) . '"' : '' ?>>

    <!-- Navbar (solo en páginas de dashboard) -->
    <?php if (!empty($show_dashboard_nav ?? false)): ?>
        <nav class="navbar-dashboard navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-rojo-quemado);">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="/guest/dashboard.php">
                    <img src="/assets/img/empresaLogoYokoso.png" 
                         alt="Logo Hotel Yokoso" 
                         class="logo-navbar" style="height: 32px; margin-right: 8px;">
                    <span class="fw-bold">Hotel Yokoso</span>
                </a>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">
                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['nombreUsuario'] ?? 'Huésped') ?>
                    </span>
                    <a href="/logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-door-open me-1"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    
<!-- Contenido principal  CAMBIADO-->
<div class="main-wrapper">
    <div class="<?= $use_container_fluid ?? false ? 'container-fluid ' : 'container' ?>">
        <?= $content ?? '' ?>
    </div>
</div>

    <!-- Footer (solo en dashboard) -->
    <?php if (!empty($show_dashboard_footer ?? false)): ?>
        <footer class="footer-dashboard bg-black text-white text-center py-3 small">
            <div class="container">
                <p class="mb-1">© <?= date('Y') ?> Hotel Yokoso. Todos los derechos reservados.</p>
                <p class="mb-0"><i class="fas fa-phone me-1"></i> +591 7000 0000</p>
            </div>
        </footer>
    <?php endif; ?>

    <!-- Scripts base -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarAccion(mensaje = '¿Estás seguro?') {
            return confirm(mensaje);
        }
    </script>

    <?php if (!empty($scripts ?? '')): ?>
        <?= $scripts ?>
    <?php endif; ?>

</body>
</html>