<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}
?>

<?php $empresas = $datos['empresas']; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Asegura jQuery -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header"><h3>Fashion Plus</h3></div>
    <ul class="sidebar-menu">
        <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
        <?php if ($_SESSION['usuario']['Rol_us'] === 'admin'): ?>
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
        <?php endif; ?>
        <li><a href="<?= URL ?>empresa/ver" class="active"><i class="fas fa-building"></i> Empresas</a></li>
        <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="content-container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-building fa-rosado"></i> Lista de Empresas</h1>
                <p class="text-muted">Empresas registradas en el sistema</p>
            </div>
            <div class="header-actions">
                <a href="<?= URL ?>empresa/registrar" class="btn btn-rosado">
                    <i class="fas fa-plus"></i> Nueva Empresa
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <!-- <h5 class="mb-0"><i class="fas fa-users fa-rosado"></i> Empresas Registradas</h5> -->
                    <div class="search-box">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Buscar empresa...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="card-body p-0">
                <?php if (empty($empresas)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> No hay empresas registradas.
                    </div>
                <?php else: ?>

                    <div class="table-container">
                        <table class="table table-bordered table-hover" id="empresaTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>NIT</th>
                                    <th>Contacto</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Correo</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empresas as $e): ?>
                                    <tr>
                                        <td><?= $e['ID_emp']; ?></td>
                                        <td><?= htmlspecialchars($e['Nombre_emp']) ?></td>
                                        <td><?= htmlspecialchars($e['NIT_emp']) ?></td>
                                        <td><?= htmlspecialchars($e['Contacto_emp']) ?></td>
                                        <td><?= sprintf("%s-%s", substr($e['Telefono_emp'], 0, 4), substr($e['Telefono_emp'], 4, 4)) ?></td>
                                        <td><?= htmlspecialchars($e['Direccion_emp']) ?></td>
                                        <td><?= htmlspecialchars($e['Correo_emp']) ?></td>
                                        <td>
                                            <a href="<?= URL ?>cliente/editar/<?= $c['ID_cli'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= URL ?>cliente/eliminar/<?= $c['ID_cli'] ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este cliente?');">
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
                    <button type="button" class="btn btn-rosado me-2" onclick="window.print()" title="Imprimir lista">
                        <i class="fas fa-print"></i> Imprimir
                    </button>

                    <a href="<?= URL ?>inicio" class="btn btn-secondary" title="Volver al inicio">
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
            $("#empresaTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>

</body>
</html>
