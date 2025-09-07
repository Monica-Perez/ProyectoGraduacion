<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$usuarios = $datos['usuarios'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Fashion Plus</h3>
    </div>
    <ul class="sidebar-menu">
        <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
        <?php if ($_SESSION['usuario']['Rol_us'] === 'admin'): ?>
            <li><a href="<?= URL ?>usuario/ver" class="active"><i class="fas fa-users"></i> Usuarios</a></li>
        <?php endif; ?> 
        <li><a href="<?= URL ?>empresa/ver"><i class="fas fa-building"></i> Empresas</a></li>
        <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <div class="content-container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-users fa-rosado"></i> Lista de Usuarios</h1>
                <p class="text-muted">Detalle de usuarios registrados</p>
            </div>
            <div class="header-actions">
                <a href="<?= URL ?>usuario/registrar" class="btn btn-rosado">
                    <i class="fas fa-plus"></i> Nuevo Usuario
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Buscar usuario...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (empty($usuarios)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> No hay usuarios registrados.
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table table-bordered table-hover" id="usuariosTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><?= $u['ID_us']; ?></td>
                                        <td><?= htmlspecialchars($u['usuario']) ?></td>
                                        <td><?= ucfirst($u['Rol_us']) ?></td>
                                        <td>
                                            <span class="badge <?= $u['Estado_us'] == 'activo' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= ucfirst($u['Estado_us']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($u['Fecha_Creacion'])) ?></td>
                                        <td>
                                            <a href="<?= URL ?>usuario/editar/<?= $u['ID_us'] ?>" class="btn btn-sm btn-warning mb-1" title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-white">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-rosado me-2" onclick="window.print()" title="Imprimir lista">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="<?= URL ?>inicio" class="btn btn-secondary" title="Volver al inicio">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script de búsqueda -->
<script>
    $(document).ready(function(){
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#usuariosTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
</script>
</body>
</html>
