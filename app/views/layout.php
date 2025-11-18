<?php
// Solo iniciar sesión si no está activa (útil para algunas vistas)
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
    
</head>
<body<?= !empty($body_class ?? '') ? ' class="' . htmlspecialchars($body_class) . '"' : '' ?>>

<?= $content ?? '' ?>

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