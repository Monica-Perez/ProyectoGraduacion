<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$clientes = $datos['clientes'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes</title>
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
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
        <?php endif; ?>
        <li><a href="<?= URL ?>empresa/ver"><i class="fas fa-building"></i> Empresas</a></li>
        <li><a href="<?= URL ?>cliente/ver" class="active"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>producto/ver"><i class="fas fa-box"></i> Productos</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <div class="content-container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-user-tie fa-rosado"></i> Lista de Clientes</h1>
                <p class="text-muted">Clientes registrados en el sistema.</p>
            </div>
            <div class="header-actions">
                <a href="<?= URL ?>cliente/registrar" class="btn btn-rosado">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Buscar cliente...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (empty($clientes)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> No hay clientes registrados.
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table table-bordered table-hover" id="clienteTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Empresa</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Correo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $c): ?>
                                    <tr>
                                        <td><?= $c['ID_cli']; ?></td>
                                        <td><?= htmlspecialchars($c['Nombre_emp']) ?></td>
                                        <td><?= htmlspecialchars($c['Nombre_cli']) ?></td>
                                        <td><?= htmlspecialchars($c['Apellido_cli']) ?></td>
                                        <td><?= sprintf("%s-%s", substr($c['Telefono_cli'], 0, 4), substr($c['Telefono_cli'], 4, 4)) ?></td>
                                        <td><?= htmlspecialchars($c['Direccion_cli']) ?></td>
                                        <td><?= htmlspecialchars($c['Correo_cli']) ?></td>
                                        <td>
                                            <a href="<?= URL ?>cliente/editar/<?= $c['ID_cli'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= URL ?>cliente/eliminar/<?= $c['ID_cli'] ?>" class="btn btn-sm btn-danger" title="Eliminar" 
                                                onclick="return confirm('¿Estás seguro de eliminar este cliente?');">
                                                <i class="fas fa-trash-alt"></i>
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
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-rosado me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="<?= URL ?>inicio" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Buscador en tiempo real -->
<script>
    $(document).ready(function(){
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#clienteTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
</body>
</html>
