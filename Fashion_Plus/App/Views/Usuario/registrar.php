<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        <li><a href="<?= URL ?>inicio" class="active"><i class="fas fa-home"></i> Inicio</a></li>
        <?php if ($_SESSION['usuario']['Rol_us'] === 'admin'): ?>
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
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
                <h1><i class="fas fa-user-plus fa-rosado"></i> Registrar Usuario</h1>
                <p class="text-muted">Ingresa la información del nuevo usuario</p>
            </div>
        </div>

        <div class="card">
            <?php if (!empty($datos['error'])): ?>
                <div class="alert alert-danger"><?= $datos['error'] ?></div>
            <?php endif; ?>

            <div class="card-body">
                <form method="POST" action="<?= URL ?>usuario/registrar">
                    <div class="mb-3">
                        <label class="form-label">Usuario:</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña:</label>
                        <input type="password" name="pass" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol:</label>
                        <select name="rol" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="vendedor">Vendedor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado:</label>
                        <select name="estado" class="form-select" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="form-group text-right">
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
