<?php if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$usuario = $datos['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
    <link rel="icon" type="image/png" href="<?= URL ?>public/img/Icono.png">
</head>
<body>

<!-- Sidebar (igual que Empresa) -->
<div class="sidebar">
    <div class="sidebar-header"><h3>Fashion Plus</h3></div>
    <ul class="sidebar-menu">
        <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
        <?php if ($_SESSION['usuario']['Rol_us'] === 'admin'): ?>
            <li><a href="<?= URL ?>usuario/ver" class="active"><i class="fas fa-users"></i> Usuarios</a></li>
        <?php endif; ?>
        <li><a href="<?= URL ?>empresa/ver"><i class="fas fa-building"></i> Empresas</a></li>
        <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>producto/ver"><i class="fas fa-box"></i> Productos</a></li>
        <li><a href="<?= URL ?>pedido/ver"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
        <li><a href="<?= URL ?>dashboard/ver"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <div class="content-container">

        <!-- Header (misma lógica de Empresa) -->
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-user-edit fa-rosado"></i> Editar Usuario</h1>
                <p class="text-muted">Modifique los datos del usuario</p>
            </div>
        </div>

        <?php if (!empty($datos['error'])): ?>
            <div class="alert alert-danger"><?= $datos['error'] ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= URL ?>usuario/editar">
                    <input type="hidden" name="id" value="<?= (int)$usuario['ID_us'] ?>">
                    
                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-info-circle fa-rosado"></i> Datos Generales</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                                <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rol <span class="text-danger">*</span></label>
                                <select name="rol" class="form-select" required>
                                    <option value="admin"    <?= (($usuario['Rol_us'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option>
                                    <option value="vendedor" <?= (($usuario['Rol_us'] ?? '') === 'vendedor') ? 'selected' : '' ?>>Vendedor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-user-shield fa-rosado"></i> Estado y Acceso</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo"   <?= (($usuario['Estado_us'] ?? '') === 'activo')   ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= (($usuario['Estado_us'] ?? '') === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones (mismo alineado que Empresa) -->
                    <div class="form-group text-end mt-4">
                        <button type="submit" class="btn btn-rosado">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                        <a href="<?= URL ?>usuario/ver" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
</body>
</html>
