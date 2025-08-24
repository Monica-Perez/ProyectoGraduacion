<?php $usuarios = $datos['usuarios']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Fashion Plus</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="<?= URL ?>usuario/ver" class="active"><i class="fas fa-users"></i> Usuarios</a></li>
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
                <div class="card-body p-0">
                    <?php if (empty($usuarios)): ?>
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle"></i> No hay usuarios registrados.
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table table-bordered table-hover">
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
                                            <td><?= $u['Fecha_Creacion']; ?></td>
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
            </div>
        </div>
    </div>
</body>
</html>
