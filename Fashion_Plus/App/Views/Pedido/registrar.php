<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL . 'usuario/login');
    exit;
}

$clientes = $datos['clientes'] ?? [];
$empresas = $datos['empresas'] ?? [];
$productos = $datos['productos'] ?? [];
$estados = $datos['estados'] ?? ['pendiente', 'en proceso', 'completado', 'cancelado'];
$errores = $datos['errores'] ?? [];
$form = $datos['form'] ?? [];

$rowsData = [];
if (!empty($form['productos']) && is_array($form['productos'])) {
    $count = count($form['productos']);
    for ($i = 0; $i < $count; $i++) {
        $rowsData[] = [
            'producto_id' => $form['productos'][$i] ?? '',
            'cantidad' => $form['cantidades'][$i] ?? 1,
            'precio' => $form['precios'][$i] ?? ''
        ];
    }
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
    <style>
        .section-title {
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        .resumen-pedido {
            background-color: #f8f9fa;
        }
        .resumen-pedido span {
            font-size: 0.95rem;
        }
        .tabla-detalle thead th {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header"><h3>Fashion Plus</h3></div>
    <ul class="sidebar-menu">
        <li><a href="<?= URL ?>inicio"><i class="fas fa-home"></i> Inicio</a></li>
        <?php if ($_SESSION['usuario']['Rol_us'] === 'admin'): ?>
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
                <h1><i class="fas fa-shopping-cart fa-rosado"></i> Registrar Pedido</h1>
                <p class="text-muted">Complete los datos del nuevo pedido.</p>
            </div>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Revise la información ingresada:</h6>
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= URL ?>pedido/registrar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Empresa</label>
                                <select name="ID_emp" id="empresaSelect" class="form-select" required>
                                    <option value="">Seleccione una empresa</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?= $empresa['ID_emp']; ?>" <?= (!empty($form['ID_emp']) && (int) $form['ID_emp'] === (int) $empresa['ID_emp']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($empresa['Nombre_emp'] ?? ('Empresa ' . $empresa['ID_emp'])); ?>
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
                                        <?php $clienteNombre = trim(($cliente['Nombre_cli'] ?? '') . ' ' . ($cliente['Apellido_cli'] ?? '')); ?>
                                        <option value="<?= $cliente['ID_cli']; ?>" <?= (!empty($form['ID_cli']) && (int) $form['ID_cli'] === (int) $cliente['ID_cli']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($clienteNombre ?: 'Cliente ' . $cliente['ID_cli']); ?>
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
                                <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($form['fecha'] ?? date('Y-m-d')); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select" required>
                                    <?php foreach ($estados as $estado): ?>
                                        <option value="<?= htmlspecialchars($estado); ?>" <?= (!empty($form['estado']) && strcasecmp($form['estado'], $estado) === 0) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars(ucwords($estado)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Descuento (Q)</label>
                                <input type="number" name="descuento" id="descuentoInput" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($form['descuento'] ?? 0); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h5 class="section-title"><i class="fas fa-box-open fa-rosado"></i> Productos del pedido</h5>
                        <div class="table-container">
                        <table class="table table-bordered table-hover" id="usuariosTable">
                            <thead>
                                        <th>Producto</th>
                                        <th class="text-center" style="width: 120px;">Cantidad</th>
                                        <th class="text-end" style="width: 160px;">Precio Unitario (Q)</th>
                                        <th class="text-end" style="width: 150px;">Subtotal</th>
                                        <th class="text-center" style="width: 70px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="detallesBody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="agregarProducto"><i class="fas fa-plus"></i> Agregar producto</button>
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
                                    <span id="descuentoTotal">Q<?= number_format((float)($form['descuento'] ?? 0), 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <strong>Total:</strong>
                                    <strong id="total">Q0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-end mt-4">
                        <button type="submit" class="btn btn-rosado"><i class="fas fa-save"></i> Guardar Pedido</button>
                        <a href="<?= URL ?>pedido/ver" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="producto-row-template">
    <tr>
        <td>
            <select name="producto_id[]" class="form-select producto-select" required>
                <option value="">Seleccione un producto</option>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?= $producto['ID_pro']; ?>" data-precio="<?= htmlspecialchars($producto['Precio_pro']); ?>">
                        <?= htmlspecialchars($producto['Nombre_pro']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" name="cantidad[]" class="form-control text-center cantidad-input" min="1" value="1" required>
        </td>
        <td>
            <input type="number" name="precio[]" class="form-control text-end precio-input" min="0" step="0.01" value="0.00" required>
        </td>
        <td class="text-end subtotal-linea">Q0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const empresaSelect = document.getElementById('empresaSelect');
        const clienteSelect = document.getElementById('clienteSelect');
        const noClientesAlert = document.getElementById('noClientesAlert');
        const clientesIniciales = <?= json_encode($clientes, JSON_UNESCAPED_UNICODE); ?>;
        const clienteSeleccionado = <?= json_encode($form['ID_cli'] ?? '', JSON_UNESCAPED_UNICODE); ?>;
        const detallesBody = document.getElementById('detallesBody');
        const template = document.getElementById('producto-row-template');
        const agregarBtn = document.getElementById('agregarProducto');
        const descuentoInput = document.getElementById('descuentoInput');
        const subtotalSpan = document.getElementById('subtotal');
        const descuentoSpan = document.getElementById('descuentoTotal');
        const totalSpan = document.getElementById('total');
        const filasIniciales = <?= json_encode($rowsData, JSON_UNESCAPED_UNICODE); ?>;
        let clientePreseleccionado = clienteSeleccionado ? String(clienteSeleccionado) : '';

        function limpiarClientes() {
            clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';
        }

        function mostrarMensajeSinClientes(mostrar) {
            if (!noClientesAlert) {
                return;
            }
            if (mostrar) {
                noClientesAlert.classList.remove('d-none');
            } else {
                noClientesAlert.classList.add('d-none');
            }
        }

        function renderClientes(clientes) {
            limpiarClientes();

            if (!Array.isArray(clientes) || clientes.length === 0) {
                clienteSelect.disabled = true;
                mostrarMensajeSinClientes(Boolean(empresaSelect.value));
                return;
            }

            clienteSelect.disabled = false;
            mostrarMensajeSinClientes(false);

            clientes.forEach(function (cliente) {
                const option = document.createElement('option');
                option.value = cliente.ID_cli;

                const nombre = [cliente.Nombre_cli || '', cliente.Apellido_cli || '']
                    .join(' ')
                    .trim();

                option.textContent = nombre || 'Cliente ' + cliente.ID_cli;

                if (clientePreseleccionado && String(cliente.ID_cli) === clientePreseleccionado) {
                    option.selected = true;
                }

                clienteSelect.appendChild(option);
            });

            clientePreseleccionado = '';
        }

        function cargarClientesPorEmpresa(empresaId) {
            if (!empresaId) {
                clientePreseleccionado = '';
                renderClientes([]);
                return;
            }

            limpiarClientes();
            clienteSelect.disabled = true;
            clienteSelect.innerHTML = '<option value="">Cargando clientes...</option>';
            mostrarMensajeSinClientes(false);

            fetch('<?= URL ?>pedido/clientesPorEmpresa/' + empresaId)
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Error al cargar clientes');
                    }
                    return response.json();
                })
                .then(function (data) {
                    renderClientes(Array.isArray(data) ? data : []);
                })
                .catch(function () {
                    renderClientes([]);
                });
        }

        if (empresaSelect) {
            empresaSelect.addEventListener('change', function () {
                clientePreseleccionado = '';
                cargarClientesPorEmpresa(this.value);
            });
        }

        if (clientesIniciales.length > 0) {
            renderClientes(clientesIniciales);
        } else if (empresaSelect && empresaSelect.value) {
            cargarClientesPorEmpresa(empresaSelect.value);
        } else {
            renderClientes([]);
        }

        function actualizarTotales() {
            let subtotal = 0;
            detallesBody.querySelectorAll('tr').forEach(function (row) {
                const cantidad = parseFloat(row.querySelector('.cantidad-input').value) || 0;
                const precio = parseFloat(row.querySelector('.precio-input').value) || 0;
                const subtotalLinea = cantidad * precio;
                row.querySelector('.subtotal-linea').textContent = 'Q' + subtotalLinea.toFixed(2);
                subtotal += subtotalLinea;
            });

            const descuento = parseFloat(descuentoInput.value) || 0;
            const total = Math.max(subtotal - descuento, 0);

            subtotalSpan.textContent = 'Q' + subtotal.toFixed(2);
            descuentoSpan.textContent = 'Q' + descuento.toFixed(2);
            totalSpan.textContent = 'Q' + total.toFixed(2);
        }

        function enlazarEventos(row) {
            const select = row.querySelector('.producto-select');
            const cantidadInput = row.querySelector('.cantidad-input');
            const precioInput = row.querySelector('.precio-input');
            const removeBtn = row.querySelector('.remove-row');

            select.addEventListener('change', function () {
                const option = select.selectedOptions[0];
                if (option && option.dataset.precio) {
                    precioInput.value = parseFloat(option.dataset.precio).toFixed(2);
                }
                actualizarTotales();
            });

            cantidadInput.addEventListener('input', actualizarTotales);
            precioInput.addEventListener('input', actualizarTotales);

            removeBtn.addEventListener('click', function () {
                row.remove();
                if (!detallesBody.querySelector('tr')) {
                    agregarFila();
                } else {
                    actualizarTotales();
                }
            });
        }

        function agregarFila(productoId = '', cantidad = 1, precio = '') {
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('tr');
            const select = row.querySelector('.producto-select');
            const cantidadInput = row.querySelector('.cantidad-input');
            const precioInput = row.querySelector('.precio-input');

            if (productoId) {
                select.value = productoId;
            }

            cantidadInput.value = cantidad || 1;

            if (precio === '') {
                const option = select.selectedOptions[0];
                if (option && option.dataset.precio) {
                    precioInput.value = parseFloat(option.dataset.precio).toFixed(2);
                }
            } else {
                precioInput.value = parseFloat(precio).toFixed(2);
            }

            detallesBody.appendChild(row);
            enlazarEventos(row);
            actualizarTotales();
        }

        agregarBtn.addEventListener('click', function () {
            agregarFila();
        });

        descuentoInput.addEventListener('input', actualizarTotales);

        if (filasIniciales.length) {
            filasIniciales.forEach(function (fila) {
                agregarFila(fila.producto_id, fila.cantidad, fila.precio);
            });
        } else {
            agregarFila();
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
