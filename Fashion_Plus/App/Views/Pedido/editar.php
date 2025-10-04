<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ' . URL . 'usuario/login'); exit; }

$pedido    = $datos['pedido']    ?? [];
$empresas  = $datos['empresas']  ?? [];
$clientes  = $datos['clientes']  ?? []; // clientes ya filtrados por la empresa del pedido
$productos = $datos['productos'] ?? [];
$estados   = $datos['estados']   ?? ['pendiente', 'en proceso', 'completado', 'cancelado'];
$errores   = $datos['errores']   ?? [];

$idPed     = (int)($pedido['ID_ped'] ?? 0);
$idEmp     = (int)($pedido['ID_emp'] ?? 0);
$idCli     = (int)($pedido['ID_cli'] ?? 0);
$fecha     = htmlspecialchars(substr($pedido['Fecha_ped'] ?? date('Y-m-d'), 0, 10));
$estadoVal = htmlspecialchars($pedido['Estado'] ?? 'pendiente');
$descuento = (float)($pedido['Descuento'] ?? 0);

// Construir filas iniciales del detalle con el mismo formato que registrar ($rowsData)
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
        .tabla-detalle thead th{ background-color:#f0f0f0; }
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
                <form method="POST" action="<?= URL ?>pedido/actualizar">
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?= $fecha ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select" required>
                                    <?php foreach ($estados as $estado): ?>
                                        <option value="<?= h($estado) ?>" <?= (strcasecmp($estado, $estadoVal)===0) ? 'selected' : '' ?>>
                                            <?= h(ucwords($estado)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Descuento (Q)</label>
                                <input type="number" name="descuento" id="descuentoInput" class="form-control" min="0" step="0.01" value="<?= number_format($descuento,2,'.',''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
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
                                <tbody id="detallesBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <div class="resumen-pedido border rounded p-3">
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">Q0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span>Descuento:</span>
                                    <span id="descuentoTotal">Q<?= number_format($descuento,2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <strong>Total:</strong>
                                    <strong id="total">Q0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-end mt-4">
                        <button type="submit" class="btn btn-rosado"><i class="fas fa-save"></i> Guardar Cambios</button>
                        <a href="<?= URL ?>pedido/ver" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Template de fila (como en registrar) -->
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
        <td><input type="number" name="precio[]"   class="form-control text-end precio-input"   min="0" step="0.01" value="0.00" required></td>
        <td class="text-end subtotal-linea">Q0.00</td>
        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const empresaSelect = document.getElementById('empresaSelect');
    const clienteSelect = document.getElementById('clienteSelect');
    const noClientesAlert = document.getElementById('noClientesAlert');

    const detallesBody   = document.getElementById('detallesBody');
    const template       = document.getElementById('producto-row-template');
    const agregarBtn     = document.getElementById('agregarProducto');
    const descuentoInput = document.getElementById('descuentoInput');
    const subtotalSpan   = document.getElementById('subtotal');
    const descuentoSpan  = document.getElementById('descuentoTotal');
    const totalSpan      = document.getElementById('total');

    // Datos iniciales (del servidor)
    const filasIniciales = <?= json_encode($rowsData, JSON_UNESCAPED_UNICODE); ?>;
    const clientesIniciales = <?= json_encode($clientes, JSON_UNESCAPED_UNICODE); ?>;
    let clientePreseleccionado = "<?= $idCli ?>";

    function mostrarMensajeSinClientes(mostrar) {
        if (!noClientesAlert) return;
        noClientesAlert.classList.toggle('d-none', !mostrar);
    }

    function limpiarClientes() {
        clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';
        clienteSelect.disabled = true;
    }

    function renderClientes(clientes) {
        limpiarClientes();
        if (!Array.isArray(clientes) || clientes.length === 0) {
            mostrarMensajeSinClientes(Boolean(empresaSelect.value));
            return;
        }
        clienteSelect.disabled = false;
        mostrarMensajeSinClientes(false);

        clientes.forEach(function (c) {
            const opt = document.createElement('option');
            opt.value = c.ID_cli;
            const nombre = [c.Nombre_cli || '', c.Apellido_cli || ''].join(' ').trim();
            opt.textContent = nombre || ('Cliente ' + c.ID_cli);
            if (clientePreseleccionado && String(c.ID_cli) === String(clientePreseleccionado)) {
                opt.selected = true;
            }
            clienteSelect.appendChild(opt);
        });
        clientePreseleccionado = '';
    }

    function cargarClientesPorEmpresa(empresaId) {
        if (!empresaId) { renderClientes([]); return; }
        clienteSelect.innerHTML = '<option value="">Cargando...</option>';
        clienteSelect.disabled = true;
        fetch('<?= URL ?>pedido/clientesPorEmpresa/' + empresaId)
            .then(resp => resp.ok ? resp.json() : [])
            .then(data => renderClientes(Array.isArray(data) ? data : []))
            .catch(() => renderClientes([]));
    }

    // Inicializar clientes (si ya vinieron filtrados)
    if (clientesIniciales && clientesIniciales.length) {
        renderClientes(clientesIniciales);
    } else if (empresaSelect && empresaSelect.value) {
        cargarClientesPorEmpresa(empresaSelect.value);
    } else {
        renderClientes([]);
    }

    // Cambio de empresa
    if (empresaSelect) {
        empresaSelect.addEventListener('change', function () {
            clientePreseleccionado = '';
            cargarClientesPorEmpresa(this.value);
        });
    }

    // --- Detalle (igual que registrar)
    function actualizarTotales() {
        let subtotal = 0;
        detallesBody.querySelectorAll('tr').forEach(function (row) {
            const cantidad = parseFloat(row.querySelector('.cantidad-input').value) || 0;
            const precio   = parseFloat(row.querySelector('.precio-input').value) || 0;
            const subLinea = cantidad * precio;
            row.querySelector('.subtotal-linea').textContent = 'Q' + subLinea.toFixed(2);
            subtotal += subLinea;
        });
        const desc = parseFloat(descuentoInput.value) || 0;
        const total = Math.max(subtotal - desc, 0);
        subtotalSpan.textContent = 'Q' + subtotal.toFixed(2);
        descuentoSpan.textContent = 'Q' + desc.toFixed(2);
        totalSpan.textContent = 'Q' + total.toFixed(2);
    }

    function enlazarEventos(row) {
        const sel = row.querySelector('.producto-select');
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
            if (!detallesBody.querySelector('tr')) agregarFila();
            actualizarTotales();
        });
    }

    function agregarFila(productoId = '', cantidad = 1, precio = '') {
        const frag = template.content.cloneNode(true);
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

        detallesBody.appendChild(row);
        enlazarEventos(row);
        actualizarTotales();
    }

    document.getElementById('agregarProducto').addEventListener('click', function(){
        agregarFila();
    });
    descuentoInput.addEventListener('input', actualizarTotales);

    // Pintar filas iniciales o una vacía
    if (Array.isArray(filasIniciales) && filasIniciales.length) {
        filasIniciales.forEach(f => agregarFila(f.producto_id, f.cantidad, f.precio));
    } else {
        agregarFila();
    }

    // Recalcular al inicio
    actualizarTotales();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
