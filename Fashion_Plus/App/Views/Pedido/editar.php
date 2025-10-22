<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ' . URL . 'usuario/login'); exit; }

$pedido    = $datos['pedido']    ?? [];
$empresas  = $datos['empresas']  ?? [];
$clientes  = $datos['clientes']  ?? [];
$productos = $datos['productos'] ?? [];
$estados   = $datos['estados']   ?? ['pendiente', 'en proceso', 'completado', 'cancelado'];
$errores   = $datos['errores']   ?? [];

$idPed     = (int)($pedido['ID_ped'] ?? 0);
$idEmp     = (int)($pedido['ID_emp'] ?? 0);
$idCli     = (int)($pedido['ID_cli'] ?? 0);
$fecha     = htmlspecialchars(substr($pedido['Fecha_ped'] ?? date('Y-m-d'), 0, 10));
$estadoVal = htmlspecialchars($pedido['Estado'] ?? 'pendiente');
$descuento = (float)($pedido['Descuento'] ?? 0);

/** Detalle inicial para pintar filas */
$rowsData = [];
if (!empty($pedido['detalles']) && is_array($pedido['detalles'])) {
    foreach ($pedido['detalles'] as $d) {
        $rowsData[] = [
            'producto_id' => $d['ID_pro'] ?? '',
            'cantidad'    => $d['Cantidad_det'] ?? $d['Cantidad'] ?? 1,
            'precio'      => $d['PrecioUnitario_det'] ?? $d['Precio'] ?? ''
        ];
    }
}

/** Historial de abonos y total abonado */
$abonosData = [];
if (!empty($pedido['abonos']) && is_array($pedido['abonos'])) {
    foreach ($pedido['abonos'] as $a) {
        $abonosData[] = [
            'ID_abono' => (int)($a['ID_abono'] ?? 0),
            'fecha'    => $a['Fecha_abono'],
            'monto'    => (float)$a['Monto_abono']
        ];
    }
}
$abonado = 0.0;
if (!empty($pedido['abonos']) && is_array($pedido['abonos'])) {
    foreach ($pedido['abonos'] as $ab) {
        $abonado += (float)($ab['Monto_abono'] ?? 0);
    }
}

$totalPedido = (float)($pedido['Total_ped'] ?? 0);
$saldoDb = max($totalPedido - $abonado, 0);
function h($s){ return htmlspecialchars((string)$s); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Pedido</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
    <style>
        .section-title{ color: var(--secondary); margin-bottom: 1rem; }
        .resumen-pedido{ background-color:#f8f9fa; }
        .resumen-pedido span{ font-size:.95rem; }
        .table-container{ overflow-x:auto; }
        .sidebar .active{ font-weight:600; }
    </style>
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
                <h1><i class="fas fa-shopping-cart fa-rosado"></i> Editar Pedido #<?= h($idPed) ?></h1>
                <p class="text-muted">Actualice los datos del pedido.</p>
            </div>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Revise la información:</h6>
                <ul class="mb-0">
                    <?php foreach ($errores as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">

                <!-- ============ FORM EDITAR PEDIDO (CON ID) ============ -->
                <form id="formEditarPedido" method="POST" action="<?= URL ?>pedido/editar">
                    <input type="hidden" name="ID_ped" value="<?= h($idPed) ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Empresa</label>
                                <select name="ID_emp" id="empresaSelect" class="form-select" required>
                                    <option value="">Seleccione una empresa</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?= $empresa['ID_emp']; ?>"
                                            <?= ($idEmp && (int)$empresa['ID_emp'] === $idEmp) ? 'selected' : ''; ?>>
                                            <?= h($empresa['Nombre_emp'] ?? ('Empresa '.$empresa['ID_emp'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cliente</label>
                                <select name="ID_cli" id="clienteSelect" class="form-select" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <?php $nom = trim(($cliente['Nombre_cli'] ?? '').' '.($cliente['Apellido_cli'] ?? '')); ?>
                                        <option value="<?= $cliente['ID_cli']; ?>"
                                            <?= ($idCli && (int)$cliente['ID_cli'] === $idCli) ? 'selected' : ''; ?>>
                                            <?= h($nom ?: 'Cliente '.$cliente['ID_cli']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="noClientesAlert" class="alert alert-warning mt-2 d-none">
                                    <i class="fas fa-info-circle"></i> No hay clientes asociados a la empresa seleccionada.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= $fecha ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select" required>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= h($estado) ?>" <?= (strcasecmp($estado, $estadoVal)===0) ? 'selected' : '' ?>>
                                        <?= h(ucwords($estado)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Descuento (Q)</label>
                            <input type="number" name="descuento" id="descuentoInput" class="form-control" min="0" step="0.01" value="<?= number_format($descuento,2,'.',''); ?>">
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="form-section my-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="section-title mb-0">
                                <i class="fas fa-box-open fa-rosado"></i> Productos del pedido
                            </h5>
                            <button type="button" class="btn btn-rosado" id="agregarProducto">
                                <i class="fas fa-plus"></i> Agregar producto
                            </button>
                        </div>

                        <div class="table-container">
                            <table class="table table-bordered table-hover" id="productoTable">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center" style="width:120px;">Cantidad</th>
                                        <th class="text-end" style="width:160px;">Precio Unitario (Q)</th>
                                        <th class="text-end" style="width:150px;">Subtotal</th>
                                        <th class="text-center" style="width:70px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="productosBody"></tbody>
                            </table>
                        </div>
                    </div>
                </form><!-- CIERRE DE PEDIDOS -->

                <!-- ============ ABONOS + RESUMEN (fuera del form grande) ============ -->
                <div class="row g-4 align-items-start mt-2">

                    <!-- IZQUIERDA: ABONOS -->
                    <div class="col-lg-8">
                        <div class="form-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="section-title mb-0">
                                    <i class="fas fa-dollar-sign fa-rosado"></i> Abonos del pedido
                                </h5>
                            </div>

                            <!-- Form independiente para nuevo abono -->
                            <form action="<?= URL ?>pedido/abonar/<?= $idPed ?>" method="POST" class="mb-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-sm-5 col-md-4">
                                        <label class="form-label">Fecha del abono</label>
                                        <input type="date" name="fecha_abono" value="<?= date('Y-m-d') ?>" class="form-control" required>
                                    </div>

                                    <div class="col-12 col-sm-5 col-md-4">
                                        <label class="form-label">Monto</label>
                                        <input type="number" name="monto" step="0.01" min="0.01" max="<?= number_format($saldoDb, 2, '.', '') ?>"
                                            class="form-control" placeholder="0.00" required>
                                    </div>

                                    <div class="col-12 col-sm col-md text-sm-end ms-sm-auto">
                                        <button type="submit" class="btn btn-rosado px-4 w-100 w-sm-auto mt-2 mt-sm-0">
                                            <i class="fas fa-plus"></i> Agregar abono
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-container mt-2">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th hidden>ID</th>
                                            <th style="width:160px;">Fecha</th>
                                            <th class="text-end" style="width:160px;">Monto (Q)</th>
                                            <th class="text-center" style="width:90px;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="abonosBody">
                                        <?php if (!empty($abonosData)): ?>
                                            <?php foreach ($abonosData as $ab): ?>
                                                <tr>
                                                    <td hidden><?= h($ab['ID_abono'] ?? '') ?></td>
                                                    <td><?= h(substr($ab['fecha'],0,10)) ?></td>
                                                    <td class="text-end">Q<?= number_format((float)$ab['monto'],2) ?></td>
                                                    <td class="text-center">
                                                        <a href="<?= URL ?>pedido/eliminarAbono/<?= (int)$ab['ID_abono'] ?>/<?= $idPed ?>"
                                                           class="btn btn-outline-danger btn-sm"
                                                           onclick="return confirm('¿Eliminar abono?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No hay abonos registrados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- DERECHA: RESUMEN + BOTONES -->
                    <div class="col-lg-4">
                        <div class="resumen-pedido border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="subtotal">Q0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>Descuento:</span>
                                <span id="descuentoTotal">Q<?= number_format($descuento,2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>Abono:</span>
                                <span id="abonoTotal">Q<?= number_format($abonado, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <strong>Saldo:</strong>
                                <strong id="total">Q0.00</strong>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" form="formEditarPedido" class="btn btn-rosado">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="<?= URL ?>pedido/ver" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
                <!-- ============ FIN ABONOS/RESUMEN ============ -->

            </div>
        </div>
    </div>
</div>

<!-- Template de fila de producto -->
<template id="producto-row-template">
    <tr>
        <td>
            <select name="producto_id[]" class="form-select producto-select" required>
                <option value="">Seleccione un producto</option>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?= $producto['ID_pro']; ?>" data-precio="<?= h($producto['Precio_pro'] ?? 0); ?>">
                        <?= h($producto['Nombre_pro']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" name="cantidad[]" class="form-control text-center cantidad-input" min="1" value="1" required></td>
        <td><input type="number" name="precio[]" class="form-control text-end precio-input" min="0" step="0.01" value="0.00" required></td>
        <td class="text-end subtotal-linea">Q0.00</td>
        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const empresaSelect = document.getElementById('empresaSelect');
    const clienteSelect = document.getElementById('clienteSelect');
    const noClientesAlert = document.getElementById('noClientesAlert');
    const productosBody = document.getElementById('productosBody');

    const productoTemplate = document.getElementById('producto-row-template');
    const agregarProductoBtn = document.getElementById('agregarProducto');

    const descuentoInput = document.getElementById('descuentoInput');
    const subtotalSpan   = document.getElementById('subtotal');
    const descuentoSpan  = document.getElementById('descuentoTotal');
    const totalSpan      = document.getElementById('total');
    const abonoSpan      = document.getElementById('abonoTotal');

    const filasIniciales     = <?= json_encode($rowsData, JSON_UNESCAPED_UNICODE); ?>;
    const clientesIniciales  = <?= json_encode($clientes, JSON_UNESCAPED_UNICODE); ?>;
    const abonadoServidor    = parseFloat('<?= number_format($abonado, 2, ".", "") ?>') || 0;
    let clientePreseleccionado = "<?= $idCli ?>";

    function mostrarMensajeSinClientes(mostrar) { noClientesAlert?.classList.toggle('d-none', !mostrar); }
    function limpiarClientes() { clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>'; clienteSelect.disabled = true; }
    function renderClientes(list) {
        limpiarClientes();
        if (!Array.isArray(list) || list.length === 0) { mostrarMensajeSinClientes(Boolean(empresaSelect.value)); return; }
        clienteSelect.disabled = false; mostrarMensajeSinClientes(false);
        list.forEach(function (c) {
            const opt = document.createElement('option');
            opt.value = c.ID_cli;
            const nombre = [c.Nombre_cli || '', c.Apellido_cli || ''].join(' ').trim();
            opt.textContent = nombre || ('Cliente ' + c.ID_cli);
            if (clientePreseleccionado && String(c.ID_cli) === String(clientePreseleccionado)) opt.selected = true;
            clienteSelect.appendChild(opt);
        });
        clientePreseleccionado = '';
    }
    function cargarClientesPorEmpresa(empresaId) {
        if (!empresaId) { renderClientes([]); return; }
        clienteSelect.innerHTML = '<option value="">Cargando...</option>'; clienteSelect.disabled = true;
        fetch('<?= URL ?>pedido/clientesPorEmpresa/' + empresaId)
            .then(resp => resp.ok ? resp.json() : [])
            .then(data => renderClientes(Array.isArray(data) ? data : []))
            .catch(() => renderClientes([]));
    }
    if (clientesIniciales && clientesIniciales.length) renderClientes(clientesIniciales);
    else if (empresaSelect && empresaSelect.value) cargarClientesPorEmpresa(empresaSelect.value);
    else renderClientes([]);
    empresaSelect?.addEventListener('change', function () { clientePreseleccionado = ''; cargarClientesPorEmpresa(this.value); });

    function actualizarTotales() {
        let subtotal = 0;
        productosBody.querySelectorAll('tr').forEach(function (row) {
            const cantidad = parseFloat(row.querySelector('.cantidad-input')?.value || 0);
            const precio   = parseFloat(row.querySelector('.precio-input')?.value || 0);
            const subLinea = cantidad * precio;
            const celdaSub = row.querySelector('.subtotal-linea');
            if (celdaSub) celdaSub.textContent = 'Q' + subLinea.toFixed(2);
            subtotal += subLinea;
        });
        const desc  = parseFloat(descuentoInput?.value || 0);
        const total = Math.max(subtotal - desc, 0);
        const totalAbonos = abonadoServidor;
        const saldo = Math.max(total - totalAbonos, 0);

        subtotalSpan.textContent  = 'Q' + subtotal.toFixed(2);
        descuentoSpan.textContent = 'Q' + desc.toFixed(2);
        abonoSpan.textContent     = 'Q' + totalAbonos.toFixed(2);
        totalSpan.textContent     = 'Q' + saldo.toFixed(2);
    }

    function enlazarEventosProducto(row) {
        const sel  = row.querySelector('.producto-select');
        const cant = row.querySelector('.cantidad-input');
        const pre  = row.querySelector('.precio-input');
        const del  = row.querySelector('.remove-row');

        sel.addEventListener('change', function () {
            const opt = sel.selectedOptions[0];
            if (opt && opt.dataset.precio && (!pre.value || parseFloat(pre.value) === 0)) {
                pre.value = parseFloat(opt.dataset.precio).toFixed(2);
            }
            actualizarTotales();
        });
        cant.addEventListener('input', actualizarTotales);
        pre.addEventListener('input', actualizarTotales);
        del.addEventListener('click', function () {
            row.remove();
            if (!productosBody.querySelector('tr')) agregarProductoFila();
            actualizarTotales();
        });
    }

    function agregarProductoFila(productoId = '', cantidad = 1, precio = '') {
        const frag = productoTemplate.content.cloneNode(true);
        const row  = frag.querySelector('tr');
        const sel  = row.querySelector('.producto-select');
        const cant = row.querySelector('.cantidad-input');
        const pre  = row.querySelector('.precio-input');

        if (productoId) sel.value = productoId;
        cant.value = cantidad || 1;
        if (precio === '' || isNaN(parseFloat(precio))) {
            const opt = sel.selectedOptions[0];
            if (opt && opt.dataset.precio) pre.value = parseFloat(opt.dataset.precio).toFixed(2);
        } else {
            pre.value = parseFloat(precio).toFixed(2);
        }
        productosBody.appendChild(row);
        enlazarEventosProducto(row);
        actualizarTotales();
    }

    agregarProductoBtn?.addEventListener('click', function(){ agregarProductoFila(); });
    descuentoInput.addEventListener('input', actualizarTotales);

    if (Array.isArray(filasIniciales) && filasIniciales.length) filasIniciales.forEach(f => agregarProductoFila(f.producto_id, f.cantidad, f.precio));
    else agregarProductoFila();

    actualizarTotales();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
