<?php if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$cliente = $datos['cliente'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
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
        <?php if ($_SESSION['usuario']['Rol_us'] === 'admin'): ?>
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
        <?php endif; ?>
        <li><a href="<?= URL ?>empresa/ver"><i class="fas fa-building"></i> Empresas</a></li>
        <li><a href="<?= URL ?>cliente/ver" class="active"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<!-- Contenido -->
<div class="main-content">
    <div class="content-container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-user-edit fa-rosado"></i> Editar Cliente</h1>
                <p class="text-muted">Modifica la información del cliente</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= URL ?>cliente/editar/<?= $cliente['ID_cli'] ?>">
                    <input type="hidden" name="id" value="<?= $cliente['ID_cli'] ?>">

                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-user"></i> Datos Personales</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Nombre:</label>
                                    <input type="text" name="nombre" class="form-control" value="<?= $cliente['Nombre_cli'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Apellido:</label>
                                    <input type="text" name="apellido" class="form-control" value="<?= $cliente['Apellido_cli'] ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-address-book"></i> Contacto</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Teléfono:</label>
                                    <input type="text" name="telefono" class="form-control" value="<?= $cliente['Telefono_cli'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Correo:</label>
                                    <input type="email" name="correo" class="form-control" value="<?= $cliente['Correo_cli'] ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-end mt-3">
                        <button type="submit" class="btn btn-rosado">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="<?= URL ?>cliente/ver" class="btn btn-secondary">
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
