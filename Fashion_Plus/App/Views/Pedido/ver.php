<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$pedidos = $datos['pedidos'] ?? [];
$estados = $datos['estados'] ?? [];

function estadoBadgeClass($estado) {
    switch (strtolower($estado)) {
        case 'completado': return 'bg-success';
        case 'en proceso': return 'bg-warning text-dark';
        case 'cancelado':  return 'bg-danger';
        default:           return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header"><h3>Fashion Plus</h3></div>
    <ul class="sidebar-menu">
        <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
        <?php if (($_SESSION['usuario']['Rol_us'] ?? '') === 'admin'): ?>
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
        <?php endif; ?>
        <li><a href="<?= URL ?>empresa/ver"><i class="fas fa-building"></i> Empresas</a></li>
        <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
        <li><a href="<?= URL ?>producto/ver"><i class="fas fa-box"></i> Productos</a></li>
        <li><a href="<?= URL ?>pedido/ver" class="active"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
        <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="content-container">
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-shopping-cart fa-rosado"></i> Lista de Pedidos</h1>
                <p class="text-muted">Pedidos registrados y su detalle asociado.</p>
            </div>
            <div class="header-actions">
                <a href="<?= URL ?>pedido/registrar" class="btn btn-rosado">
                    <i class="fas fa-plus"></i> Nuevo Pedido
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Buscar pedido...">
                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (empty($pedidos)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> No hay pedidos registrados.
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table table-bordered table-hover" id="pedidoTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Vendedor</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <?php $collapseId = 'pedido-' . $pedido['ID_ped']; ?>
                                    <tr class="pedido-row">
                                        <td><?= (int)$pedido['ID_ped']; ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars(trim($pedido['Cliente'])) ?: 'Sin cliente asignado'; ?></strong><br>
                                            <?php if (!empty($pedido['Correo_cli'])): ?>
                                                <small><i class="fas fa-envelope"></i> <?= htmlspecialchars($pedido['Correo_cli']); ?></small><br>
                                            <?php endif; ?>
                                            <?php if (!empty($pedido['Telefono_cli'])): ?>
                                                <small><i class="fas fa-phone"></i> <?= htmlspecialchars($pedido['Telefono_cli']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($pedido['Fecha_ped'])): ?>
                                                <?= date('d/m/Y', strtotime($pedido['Fecha_ped'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin fecha</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($pedido['Usuario'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge <?= estadoBadgeClass($pedido['Estado'] ?? ''); ?>">
                                                <?= htmlspecialchars(ucwords($pedido['Estado'] ?? 'Desconocido')); ?>
                                            </span>
                                        </td>
                                        <td class="pedido-total">Q<?= number_format((float)($pedido['Total_ped'] ?? 0), 2); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#<?= $collapseId; ?>"
                                                        aria-expanded="false" aria-controls="<?= $collapseId; ?>"
                                                        title="Ver detalle rápido">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="<?= URL ?>pedido/editar/<?= (int)$pedido['ID_ped']; ?>"
                                                   class="btn btn-sm btn-outline-warning" title="Editar pedido">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="detalle-row">
                                        <td colspan="7" class="p-0">
                                            <div class="collapse" id="<?= $collapseId; ?>">
                                                <div class="p-3 table-details">
                                                    <h6 class="mb-3"><i class="fas fa-list"></i> Detalle del pedido</h6>
                                                    <?php $subtotal = 0; ?>
                                                    <?php if (!empty($pedido['detalles'])): ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Producto</th>
                                                                        <th class="text-center">Cantidad</th>
                                                                        <th class="text-end">Precio Unitario</th>
                                                                        <th class="text-end">Subtotal</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($pedido['detalles'] as $detalle): ?>
                                                                        <?php
                                                                            $cant = (float)($detalle['Cantidad_det'] ?? 0);
                                                                            $pre  = (float)($detalle['PrecioUnitario_det'] ?? 0);
                                                                            $lineaTotal = $cant * $pre;
                                                                            $subtotal += $lineaTotal;
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($detalle['Nombre_pro'] ?? 'Producto'); ?></td>
                                                                            <td class="text-center"><?= (int)$cant; ?></td>
                                                                            <td class="text-end">Q<?= number_format($pre, 2); ?></td>
                                                                            <td class="text-end">Q<?= number_format($lineaTotal, 2); ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-light border mb-0">No se registraron productos en este pedido.</div>
                                                    <?php endif; ?>
                                                    <div class="row justify-content-end mt-3">
                                                        <div class="col-md-4">
                                                            <div class="pedido-desglose">
                                                                <span><strong>Subtotal:</strong> Q<?= number_format($subtotal, 2); ?></span>
                                                                <span><strong>Descuento:</strong> Q<?= number_format((float)($pedido['Descuento'] ?? 0), 2); ?></span>
                                                                <span><strong>Total:</strong> Q<?= number_format((float)($pedido['Total_ped'] ?? 0), 2); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(function () {
        $('#searchInput').on('keyup', function () {
            const value = $(this).val().toLowerCase();
            $('#pedidoTable tbody tr.pedido-row').each(function () {
                const row = $(this);
                const match = row.text().toLowerCase().indexOf(value) > -1;
                row.toggle(match);
                row.next('.detalle-row').toggle(match);
            });
        });
    });
</script>
</body>
</html>
