<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$productos = $datos['productos'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
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
        <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>producto/ver" class="active"><i class="fas fa-box"></i> Productos</a></li>
        <li><a href="<?= URL ?>pedido/ver"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="content-container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-box fa-rosado"></i> Lista de Productos</h1>
                <p class="text-muted">Productos registrados en el sistema.</p>
            </div>
            <div class="header-actions">
                <a href="<?= URL ?>producto/registrar" class="btn btn-rosado">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Buscar producto...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (empty($productos)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> No hay productos registrados.
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table table-bordered table-hover" id="productoTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th hidden>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $p): ?>
                                    <tr>
                                        <td hidden><?= $p['ID_pro']; ?></td>
                                        <td><?= htmlspecialchars($p['Nombre_pro']) ?></td>
                                        <td><?= htmlspecialchars($p['Descripcion_pro']) ?></td>
                                        <td>Q<?= number_format($p['Precio_pro'], 2) ?></td>
                                        <td>
                                            <a href="<?= URL ?>producto/editar/<?= $p['ID_pro'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= URL ?>producto/eliminar/<?= $p['ID_pro'] ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este producto?');">
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
                <div class="d-flex justify-content-end">
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

<script>
    $(document).ready(function(){
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#productoTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
</script>
</body>
</html>
