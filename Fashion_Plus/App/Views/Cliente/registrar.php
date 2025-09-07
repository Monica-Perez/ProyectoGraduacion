<?php if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Cliente</title>
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
        <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
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
                <h1><i class="fas fa-user-plus fa-rosado"></i> Registrar Cliente</h1>
                <p class="text-muted">Complete los datos del nuevo cliente</p>
            </div>
        </div>

        <?php if (!empty($datos['error'])): ?>
            <div class="alert alert-danger"><?= $datos['error'] ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= URL ?>cliente/registrar">

                    <!-- Información de Empresa -->
                    <div class="form-section mb-4">
                        <h5 class="section-title"><i class="fas fa-building fa-rosado"></i> Empresa Relacionada</h5>
                        <div class="mb-3">
                            <label class="form-label">Empresa:</label>
                            <select name="ID_emp" class="form-select" required>
                                <option value="">Seleccione una empresa</option>
                                <?php foreach ($datos['empresas'] as $e): ?>
                                    <option value="<?= $e['ID_emp'] ?>"><?= htmlspecialchars($e['Nombre_emp']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Datos Personales -->
                    <div class="form-section mb-4">
                        <h5 class="section-title"><i class="fas fa-user fa-rosado"></i> Datos Personales</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre:</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido:</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="form-section mb-4">
                        <h5 class="section-title"><i class="fas fa-envelope fa-rosado"></i> Información de Contacto</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono:</label>
                                <input type="text" name="telefono" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Correo:</label>
                                <input type="email" name="correo" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Dirección:</label>
                                <input type="text" name="direccion" class="form-control" required>
                            </div>
                        </div>
                    </div>


                    <!-- Botones -->
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-rosado"><i class="fas fa-save"></i> Guardar</button>
                        <a href="<?= URL ?>cliente/ver" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
