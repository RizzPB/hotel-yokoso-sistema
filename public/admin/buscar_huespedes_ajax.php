<?php
// public/vistas/admin/buscar_huespedes_ajax.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado');
}

require_once __DIR__ . '/../../config/database.php';

$buscar = trim($_GET['buscar'] ?? '');

// 💫 Consulta segura con LIKE en múltiples campos
$sql = "SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, email, telefono, activo FROM Huesped WHERE 1=1";
$params = [];

if (!empty($buscar)) {
    $sql .= " AND (
        nombre LIKE ? OR 
        apellido LIKE ? OR 
        nroDocumento LIKE ? OR 
        email LIKE ?
    )";
    $like = "%{$buscar}%";
    $params = [$like, $like, $like, $like];
}

$sql .= " ORDER BY apellido";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ❤️ Generar HTML de las tarjetas
if (empty($huespedes)) {
    echo '
    <div class="col-12 text-center py-5 text-muted">
        <i class="fas fa-search fa-4x mb-3"></i>
        <h5>No se encontraron huéspedes</h5>
        <p class="text-muted">Prueba con otro término de búsqueda.</p>
    </div>';
} else {
    foreach ($huespedes as $h) {
        $badge = $h['activo'] 
            ? '<span class="badge bg-success fs-6">Activo</span>' 
            : '<span class="badge bg-danger fs-6">Inactivo</span>';

        echo '
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">' . htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) . '</h5>
                            <small class="text-muted">' . htmlspecialchars($h['tipoDocumento']) . ': ' . htmlspecialchars($h['nroDocumento']) . '</small>
                        </div>
                        ' . $badge . '
                    </div>
                    <hr class="my-2">
                    <p class="small mb-1"><i class="fas fa-envelope text-primary"></i> ' . htmlspecialchars($h['email'] ?: 'Sin email') . '</p>
                    <p class="small mb-0"><i class="fas fa-phone text-success"></i> ' . htmlspecialchars($h['telefono'] ?: 'Sin teléfono') . '</p>
                    <div class="mt-3 text-end">
                        <a href="editar_huesped.php?id=' . $h['idHuesped'] . '" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>';
    }
}
?>