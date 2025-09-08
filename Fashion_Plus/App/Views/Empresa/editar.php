<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}
?>

<?php if (!isset($datos['empresa']) || !$datos['empresa']) {
    echo "<div class='alert alert-danger'>⚠️ Empresa no encontrada.</div>";
    return;
}
$empresa = $datos['empresa'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empresa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header"><h3>Fashion Plus</h3></div>
    <ul class="sidebar-menu">
        <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
        <?php if ($_SESSION['usuario']['Rol_us'] === 'admin'): ?>
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
        <?php endif; ?>
        <li><a href="<?= URL ?>empresa/ver" class="active"><i class="fas fa-building"></i> Empresas</a></li>
        <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>producto/ver"><i class="fas fa-box"></i> Productos</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="content-container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-edit fa-rosado"></i> Editar Empresa</h1>
                <p class="text-muted">Modifica los datos de la empresa</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= URL ?>empresa/editar/<?= $empresa['ID_emp'] ?>">
                    <input type="hidden" name="id" value="<?= $empresa['ID_emp'] ?>">

                    <!-- Sección de Datos Generales -->
                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-info-circle fa-rosado"></i> Datos Generales</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="nombre">Nombre de la Empresa <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?= $empresa['Nombre_emp'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="nit">NIT <span class="text-danger">*</span></label>
                                    <input type="text" name="nit" id="nit" class="form-control" value="<?= $empresa['NIT_emp'] ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

            <!-- Sección de Contacto -->
            <div class="form-section">
                <h5 class="section-title"><i class="fas fa-address-book fa-rosado"></i> Información de Contacto</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="contacto">Persona de Contacto <span class="text-danger">*</span></label>
                            <input type="text" name="contacto" id="contacto" class="form-control" value="<?= $empresa['Contacto_emp'] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" name="telefono" id="telefono" class="form-control" value="<?= $empresa['Telefono_emp'] ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="direccion">Dirección <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" id="direccion" class="form-control" value="<?= $empresa['Direccion_emp'] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="correo">Correo <span class="text-danger">*</span></label>
                            <input type="email" name="correo" id="correo" class="form-control" value="<?= $empresa['Correo_emp'] ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="form-group text-end">
                <button type="submit" class="btn btn-rosado">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="<?= URL ?>empresa/ver" class="btn btn-secondary">
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
