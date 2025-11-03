<?php
if (!isset($_SESSION))
    session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$empresas = $datos['empresas'] ?? [];
$clientes = $datos['clientes'] ?? []; // por defecto vacío si no han elegido empresa
$productos = $datos['productos'] ?? [];
$estados = $datos['estados'] ?? ['pendiente', 'en proceso', 'completado', 'cancelado'];
$errores = $datos['errores'] ?? [];

$form = $datos['form'] ?? [];
$idEmp = $form['ID_emp'] ?? '';
$idCli = $form['ID_cli'] ?? '';
$fecha = htmlspecialchars($form['fecha'] ?? date('Y-m-d'));
$estadoVal = htmlspecialchars($form['estado'] ?? 'pendiente');
$descuento = (float) ($form['descuento'] ?? 0);

function h($s)
{
    return htmlspecialchars((string) $s);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Pedido</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
    <link rel="icon" type="image/png" href="<?= URL ?>public/img/Icono.png">
    <style>
        .section-title {
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        .resumen-pedido {
            background-color: #f8f9fa;
        }

        .resumen-pedido span {
            font-size: .95rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .sidebar .active {
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Fashion Plus</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
            <?php if (($_SESSION['usuario']['Rol_us'] ?? '') === 'admin'): ?>
                <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
            <?php endif; ?>
            <li><a href="<?= URL ?>empresa/ver"><i class="fas fa-building"></i> Empresas</a></li>
            <li><a href="<?= URL ?>cliente/ver"><i class="fas fa-user-tie"></i> Clientes</a></li>
            <li><a href="<?= URL ?>producto/ver"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="<?= URL ?>pedido/ver" class="active"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
            <li><a href="<?= URL ?>dashboard/ver"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="content-container">
            <div class="header">
                <div class="header-title">
                    <h1><i class="fas fa-shopping-cart fa-rosado"></i> Nuevo Pedido</h1>
                    <p class="text-muted">Complete los datos para crear un pedido.</p>
                </div>
            </div>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Revise la información:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errores as $e): ?>
                            <li><?= h($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">

                    <!-- =============== FORM REGISTRAR PEDIDO (único form) =============== -->
                    <form method="POST" action="<?= URL ?>pedido/registrar" id="registrarForm">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Empresa</label>
                                    <select name="ID_emp" id="empresaSelect" class="form-select" required>
                                        <option value="">Seleccione una empresa</option>
                                        <?php foreach ($empresas as $empresa): ?>
                                            <option value="<?= $empresa['ID_emp']; ?>"
                                                <?= (string) $empresa['ID_emp'] === (string) $idEmp ? 'selected' : ''; ?>>
                                                <?= h($empresa['Nombre_emp'] ?? ('Empresa ' . $empresa['ID_emp'])) ?>
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
                                            <?php $nom = trim(($cliente['Nombre_cli'] ?? '') . ' ' . ($cliente['Apellido_cli'] ?? '')); ?>
                                            <option value="<?= $cliente['ID_cli']; ?>"
                                                <?= (string) $cliente['ID_cli'] === (string) $idCli ? 'selected' : ''; ?>>
                                                <?= h($nom ?: 'Cliente ' . $cliente['ID_cli']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div id="noClientesAlert" class="alert alert-warning mt-2 d-none">
                                        <i class="fas fa-info-circle"></i> No hay clientes asociados a la empresa
                                        seleccionada.
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
                                        <option value="<?= h($estado) ?>" <?= (strcasecmp($estado, $estadoVal) === 0) ? 'selected' : '' ?>>
                                            <?= h(ucwords($estado)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Descuento (Q)</label>
                                <input type="number" name="descuento" id="descuentoInput" class="form-control" min="0"
                                    step="0.01" value="<?= number_format($descuento, 2, '.', ''); ?>">
                            </div>
                        </div>

                        <!-- Productos del pedido -->
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

                        <!-- Abonos del pedido (opcional, se envían junto con el pedido) -->
                        <div class="form-section my-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="section-title mb-0">
                                    <i class="fas fa-dollar-sign fa-rosado"></i> Abonos iniciales (opcional)
                                </h5>
                                <button type="button" class="btn btn-rosado" id="agregarAbono">
                                    <i class="fas fa-plus"></i> Agregar abono
                                </button>
                            </div>

                            <div class="table-container">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width:160px;">Fecha</th>
                                            <th class="text-end" style="width:160px;">Monto (Q)</th>
                                            <th class="text-center" style="width:90px;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="abonosBody">
                                        <!-- filas dinámicas -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Resumen + Botones -->
                        <div class="row g-4 align-items-start mt-2">
                            <div class="col-lg-8"></div>
                            <div class="col-lg-4">
                                <div class="resumen-pedido border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span id="subtotal">Q0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Descuento:</span>
                                        <span id="descuentoTotal">Q<?= number_format($descuento, 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Abono:</span>
                                        <span id="abonoTotal">Q0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <strong>Saldo:</strong>
                                        <strong id="total">Q0.00</strong>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-rosado">
                                        <i class="fas fa-save"></i> Guardar Pedido
                                    </button>
                                    <a href="<?= URL ?>pedido/ver" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>

                    </form>
                    <!-- =============== FIN FORM REGISTRAR =============== -->

                </div>
            </div>
        </div>
    </div>

    <!-- Templates -->
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
            <td>
                <input type="number" name="cantidad[]" class="form-control text-center cantidad-input" min="1" value="1"
                    required>
            </td>
            <td>
                <input type="number" name="precio[]" class="form-control text-end precio-input" min="0" step="0.01"
                    value="0.00" required>
            </td>
            <td class="text-end subtotal-linea">Q0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i
                        class="fas fa-trash"></i></button>
            </td>
        </tr>
    </template>

    <template id="abono-row-template">
        <tr>
            <td>
                <input type="date" name="fecha_abono[]" class="form-control fecha-abono-input"
                    value="<?= date('Y-m-d') ?>" required>
            </td>
            <td>
                <input type="number" name="monto_abono[]" class="form-control text-end monto-abono-input" min="0.01"
                    step="0.01" value="0.00" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i
                        class="fas fa-trash"></i></button>
            </td>
        </tr>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- ELEMENTOS ---
            const empresaSelect = document.getElementById('empresaSelect');
            const clienteSelect = document.getElementById('clienteSelect');
            const noClientesAlert = document.getElementById('noClientesAlert');

            const productosBody = document.getElementById('productosBody');
            const productoTpl = document.getElementById('producto-row-template');
            const addProdBtn = document.getElementById('agregarProducto');

            const abonosBody = document.getElementById('abonosBody');
            const abonoTpl = document.getElementById('abono-row-template');
            const addAbonoBtn = document.getElementById('agregarAbono');

            const descuentoInput = document.getElementById('descuentoInput');
            const subtotalSpan = document.getElementById('subtotal');
            const descuentoSpan = document.getElementById('descuentoTotal');
            const abonoSpan = document.getElementById('abonoTotal');
            const totalSpan = document.getElementById('total');

            // --- CLIENTES POR EMPRESA ---
            function mostrarMensajeSinClientes(mostrar) {
                noClientesAlert?.classList.toggle('d-none', !mostrar);
            }

            function limpiarClientes() {
                clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';
                clienteSelect.disabled = true;
            }

            function renderClientes(list) {
                limpiarClientes();
                if (!Array.isArray(list) || list.length === 0) {
                    mostrarMensajeSinClientes(Boolean(empresaSelect.value));
                    return;
                }
                clienteSelect.disabled = false;
                mostrarMensajeSinClientes(false);

                // Si el servidor dejó un cliente preseleccionado
                const clientePre = '<?= (string) $idCli ?>';

                list.forEach(function (c) {
                    const opt = document.createElement('option');
                    opt.value = c.ID_cli;
                    const nombre = [c.Nombre_cli || '', c.Apellido_cli || ''].join(' ').trim();
                    opt.textContent = nombre || ('Cliente ' + c.ID_cli);
                    if (clientePre && String(c.ID_cli) === clientePre) opt.selected = true;
                    clienteSelect.appendChild(opt);
                });
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

            // Inicializar clientes con los que ya vinieron o por empresa seleccionada
            <?php if (!empty($clientes)): ?>
                renderClientes(<?= json_encode($clientes, JSON_UNESCAPED_UNICODE) ?>);
            <?php elseif (!empty($idEmp)): ?>
                cargarClientesPorEmpresa('<?= (string) $idEmp ?>');
            <?php else: ?>
                renderClientes([]);
            <?php endif; ?>

            empresaSelect?.addEventListener('change', function () {
                cargarClientesPorEmpresa(this.value);
            });

            // --- TOTALES ---
            function actualizarTotales() {
                let subtotal = 0;

                // productos → subtotal
                productosBody.querySelectorAll('tr').forEach(function (row) {
                    const cantidad = parseFloat(row.querySelector('.cantidad-input')?.value || 0);
                    const precio = parseFloat(row.querySelector('.precio-input')?.value || 0);
                    const subLinea = cantidad * precio;
                    const celdaSub = row.querySelector('.subtotal-linea');
                    if (celdaSub) celdaSub.textContent = 'Q' + subLinea.toFixed(2);
                    subtotal += subLinea;
                });

                const desc = parseFloat(descuentoInput?.value || 0);
                const totalAntesAbonos = Math.max(subtotal - desc, 0);

                // abonos → suma para saldo mostrado
                let totalAbonos = 0;
                abonosBody.querySelectorAll('.monto-abono-input').forEach(function (inp) {
                    totalAbonos += parseFloat(inp.value) || 0;
                });

                const saldo = Math.max(totalAntesAbonos - totalAbonos, 0);

                subtotalSpan.textContent = 'Q' + subtotal.toFixed(2);
                descuentoSpan.textContent = 'Q' + desc.toFixed(2);
                abonoSpan.textContent = 'Q' + totalAbonos.toFixed(2);
                totalSpan.textContent = 'Q' + saldo.toFixed(2);
            }

            // --- PRODUCTOS ---
            function bindProductoRow(row) {
                const sel = row.querySelector('.producto-select');
                const cant = row.querySelector('.cantidad-input');
                const pre = row.querySelector('.precio-input');
                const del = row.querySelector('.remove-row');

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
                    if (!productosBody.querySelector('tr')) addProductoRow();
                    actualizarTotales();
                });
            }

            function addProductoRow(productoId = '', cantidad = 1, precio = '') {
                const frag = productoTpl.content.cloneNode(true);
                const row = frag.querySelector('tr');
                const sel = row.querySelector('.producto-select');
                const cant = row.querySelector('.cantidad-input');
                const pre = row.querySelector('.precio-input');

                if (productoId) sel.value = productoId;
                cant.value = cantidad || 1;

                if (precio === '' || isNaN(parseFloat(precio))) {
                    const opt = sel.selectedOptions[0];
                    if (opt && opt.dataset.precio) pre.value = parseFloat(opt.dataset.precio).toFixed(2);
                } else {
                    pre.value = parseFloat(precio).toFixed(2);
                }

                productosBody.appendChild(row);
                bindProductoRow(row);
                actualizarTotales();
            }

            addProdBtn?.addEventListener('click', function () {
                addProductoRow();
            });

            // --- ABONOS ---
            function bindAbonoRow(row) {
                const monto = row.querySelector('.monto-abono-input');
                const del = row.querySelector('.remove-row');
                monto.addEventListener('input', actualizarTotales);
                del.addEventListener('click', function () {
                    row.remove();
                    actualizarTotales();
                });
            }

            function addAbonoRow(fecha = null, monto = null) {
                const frag = abonoTpl.content.cloneNode(true);
                const row = frag.querySelector('tr');

                const fechaInput = row.querySelector('.fecha-abono-input');
                const montoInput = row.querySelector('.monto-abono-input');

                const hoy = new Date().toISOString().slice(0, 10);
                fechaInput.value = (typeof fecha === 'string' && fecha.length >= 10) ? fecha.slice(0, 10) : hoy;

                const num = Number(monto);
                montoInput.value = (!Number.isNaN(num) && Number.isFinite(num)) ? num.toFixed(2) : '0.00';

                abonosBody.appendChild(row);
                bindAbonoRow(row);
                actualizarTotales();
            }

            addAbonoBtn?.addEventListener('click', function () {
                addAbonoRow();
            });

            addProductoRow();

            actualizarTotales();

            descuentoInput.addEventListener('input', actualizarTotales);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>