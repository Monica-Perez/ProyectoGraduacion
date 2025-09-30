<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}
?>

<?php
if (!isset($datos['usuario']) || !$datos['usuario']) {
    echo "<div class='alert alert-danger'>⚠️ Usuario no encontrado.</div>";
    return;
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

</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Fashion Plus</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= URL ?>dashboard"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="<?= URL ?>empresa/ver"><i class="fas fa-building"></i> Empresas</a></li>
            <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
            <li><a href="<?= URL ?>producto/ver"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="<?= URL ?>pedido/ver"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
            <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="content-container">
            <!-- <div class="header">
                <div class="header-title">
                    <h1><i class="fas fa-user-edit text-primary"></i> Editar Usuario</h1>
                    <p class="text-muted">Modifica los datos del usuario</p>
                </div>
            </div> -->

            <div class="card mb-4">
                <!--div class="card-body "-->
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-edit"></i> Editar Usuario</h4>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?= $usuario['ID_us'] ?>">

                            <div class="mb-3">
                                <label>Usuario:</label>
                                <input type="text" name="usuario" class="form-control" value="<?= $usuario['usuario'] ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Rol:</label>
                                <select name="rol" class="form-select" required>
                                    <option value="admin" <?= $usuario['Rol_us'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="vendedor" <?= $usuario['Rol_us'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Estado:</label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo" <?= $usuario['Estado_us'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= $usuario['Estado_us'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>


                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
            </div>
        </div>
    </div>
</body>
</html>
