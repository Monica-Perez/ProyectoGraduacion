<?php if (!isset($_SESSION)) session_start(); ?>

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
                <h1><i class="fas fa-user-plus fa-rosado"></i> Registrar Usuario</h1>
                <p class="text-muted">Ingresa la información del nuevo usuario</p>
            </div>
        </div>

        <!-- Mensaje de error -->
        <?php if (!empty($datos['error'])): ?>
            <div class="alert alert-danger"><?= $datos['error'] ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm rounded" style="max-width: 600px; margin: 0 auto;">
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

                <button type="submit" class="btn btn-rosado w-100"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
