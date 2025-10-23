<?php
/* ================================================
   VALIDACIÓN DE SESIÓN Y DATOS INICIALES
   ================================================ */
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ' . URL . 'usuario/login'); exit; }

/* ---------- Variables recibidas desde el controlador ---------- */
$pedido    = $datos['pedido']    ?? [];
$empresas  = $datos['empresas']  ?? [];
$clientes  = $datos['clientes']  ?? [];
$productos = $datos['productos'] ?? [];
$estados   = $datos['estados']   ?? ['pendiente', 'en proceso', 'completado', 'cancelado'];
$errores   = $datos['errores']   ?? [];

/* ---------- Datos base del pedido ---------- */
$idPed     = (int)($pedido['ID_ped'] ?? 0);
$idEmp     = (int)($pedido['ID_emp'] ?? 0);
$idCli     = (int)($pedido['ID_cli'] ?? 0);
$fecha     = htmlspecialchars(substr($pedido['Fecha_ped'] ?? date('Y-m-d'), 0, 10));
$estadoVal = htmlspecialchars($pedido['Estado'] ?? 'pendiente');
$descuento = (float)($pedido['Descuento'] ?? 0);
$totalPed  = (float)($pedido['Total_ped'] ?? 0);

/* ---------- Detalles del pedido (productos) ---------- */
$rowsData = [];
if (!empty($pedido['detalles']) && is_array($pedido['detalles'])) {
    foreach ($pedido['detalles'] as $d) {
        $rowsData[] = [
            'producto_id' => $d['ID_pro'] ?? '',
            'cantidad'    => $d['Cantidad_det'] ?? 1,
            'precio'      => $d['PrecioUnitario_det'] ?? 0
        ];
    }
}

/* ---------- Abonos ---------- */
$abonosData = $pedido['abonos'] ?? [];
$abonado = 0.0;
foreach ($abonosData as $a) {
    $abonado += (float)($a['Monto_abono'] ?? 0);
}

function h($s){ return htmlspecialchars((string)$s); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Pedido</title>
    <!-- ========== ESTILOS Y FUENTES ========== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
    <style>
        /* ========== ESTILOS PERSONALIZADOS ==========
           Igual que en registrar.php */
        .section-title { color: var(--secondary); margin-bottom: 1rem; }
        .resumen-pedido { background-color: #f8f9fa; }
        .resumen-pedido span { font-size: .95rem; }
        .table-container { overflow-x: auto; }
        .sidebar .active { font-weight: 600; }
    </style>
</head>

<body>

<!-- ===============================================
     SIDEBAR - igual que en registrar.php
     =============================================== -->
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

<!-- ===============================================
     CONTENIDO PRINCIPAL
     =============================================== -->
<div class="main-content">
    <div class="content-container">

        <!-- Encabezado del formulario -->
        <div class="header">
            <div class="header-title">
                <h1><i class="fas fa-shopping-cart fa-rosado"></i> Editar Pedido #<?= h($idPed) ?></h1>
                <p class="text-muted">Actualice los datos del pedido.</p>
            </div>
        </div>

        <!-- Mostrar errores si existen -->
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Revise la información:</h6>
                <ul class="mb-0">
                    <?php foreach ($errores as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- ===============================================
             TARJETA PRINCIPAL DEL FORMULARIO
             =============================================== -->
        <div class="card">
            <div class="card-body">

                <!-- Formulario completo (igual que registrar.php) -->
                <form method="POST" action="<?= URL ?>pedido/editar" id="editarForm">
                    <input type="hidden" name="ID_ped" value="<?= h($idPed) ?>">

                    <!-- ======= EMPRESA / CLIENTE ======= -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Empresa</label>
                                <select name="ID_emp" id="empresaSelect" class="form-select" required>
                                    <option value="">Seleccione una empresa</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?= $empresa['ID_emp']; ?>"
                                            <?= ($idEmp && (int)$empresa['ID_emp'] === $idEmp) ? 'selected' : ''; ?>>
                                            <?= h($empresa['Nombre_emp']) ?>
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

                    <!-- ======= FECHA / ESTADO / DESCUENTO ======= -->
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
                            <input type="number" name="descuento" id="descuentoInput" class="form-control" min="0" step="0.01"
                                value="<?= number_format($descuento, 2, '.', ''); ?>">
                        </div>
                    </div>

                    <!-- ======= PRODUCTOS ======= -->
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

                    <!-- ======= ABONOS ======= -->
                    <div class="form-section my-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="section-title mb-0">
                                <i class="fas fa-dollar-sign fa-rosado"></i> Abonos del pedido
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
                                    <!-- Abonos existentes del pedido -->
                                    <?php if (!empty($abonosData)): ?>
                                        <?php foreach ($abonosData as $ab): ?>
                                            <tr>
                                                <td><input type="date" name="fecha_abono[]" value="<?= h(substr($ab['Fecha_abono'],0,10)) ?>" class="form-control fecha-abono-input" required></td>
                                                <td><input type="number" name="monto_abono[]" value="<?= number_format((float)$ab['Monto_abono'],2,'.','') ?>" step="0.01" class="form-control text-end monto-abono-input" required></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <!-- 🔹 MENSAJE CUANDO NO EXISTEN ABONOS -->
                                        <tr class="text-center">
                                            <td colspan="3" style="color:#707070;">
                                                <i class="fas fa-info-circle"></i> No hay abonos ingresados.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ======= RESUMEN + BOTONES ======= -->
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
                                    <span id="abonoTotal">Q<?= number_format($abonado, 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <strong>Saldo:</strong>
                                    <strong id="total">Q0.00</strong>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-rosado">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                                <a href="<?= URL ?>pedido/ver" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </form> <!-- FIN DEL FORMULARIO PRINCIPAL -->

            </div>
        </div>
    </div>
</div>

<!-- ======= TEMPLATE DE FILA DE PRODUCTO ======= -->
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

<!-- ======= TEMPLATE DE FILA DE ABONO (para agregar nuevos) ======= -->
<template id="abono-row-template">
    <tr>
        <td><input type="date" name="fecha_abono[]" class="form-control fecha-abono-input" value="<?= date('Y-m-d') ?>" required></td>
        <td><input type="number" name="monto_abono[]" class="form-control text-end monto-abono-input" min="0.01" step="0.01" value="0.00" required></td>
        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<!-- ===============================================
     JAVASCRIPT PRINCIPAL
     =============================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // === Elementos base ===
    const empresaSelect = document.getElementById('empresaSelect');
    const clienteSelect = document.getElementById('clienteSelect');
    const noClientesAlert = document.getElementById('noClientesAlert');
    const productosBody = document.getElementById('productosBody');
    const abonosBody = document.getElementById('abonosBody');
    const productoTpl = document.getElementById('producto-row-template');
    const abonoTpl = document.getElementById('abono-row-template');
    const addProdBtn = document.getElementById('agregarProducto');
    const addAbonoBtn = document.getElementById('agregarAbono');
    const descuentoInput = document.getElementById('descuentoInput');
    const subtotalSpan = document.getElementById('subtotal');
    const descuentoSpan = document.getElementById('descuentoTotal');
    const abonoSpan = document.getElementById('abonoTotal');
    const totalSpan = document.getElementById('total');
    const filasIniciales = <?= json_encode($rowsData, JSON_UNESCAPED_UNICODE); ?>;
    const abonadoServidor = parseFloat('<?= number_format($abonado,2,".","") ?>') || 0;

    // === Clientes dinámicos por empresa ===
    function mostrarMensajeSinClientes(mostrar){ noClientesAlert?.classList.toggle('d-none', !mostrar); }
    function limpiarClientes(){ clienteSelect.innerHTML='<option value="">Seleccione un cliente</option>'; clienteSelect.disabled=true; }
    function renderClientes(list){
        limpiarClientes();
        if(!Array.isArray(list)||list.length===0){ mostrarMensajeSinClientes(Boolean(empresaSelect.value)); return; }
        clienteSelect.disabled=false; mostrarMensajeSinClientes(false);
        list.forEach(function(c){
            const opt=document.createElement('option');
            opt.value=c.ID_cli;
            const nombre=[c.Nombre_cli||'',c.Apellido_cli||''].join(' ').trim();
            opt.textContent=nombre||('Cliente '+c.ID_cli);
            if(String(c.ID_cli)==='<?= $idCli ?>') opt.selected=true;
            clienteSelect.appendChild(opt);
        });
    }
    empresaSelect?.addEventListener('change', function(){
        const id=this.value;
        clienteSelect.innerHTML='<option value="">Cargando...</option>';
        fetch('<?= URL ?>pedido/clientesPorEmpresa/'+id)
            .then(resp=>resp.ok?resp.json():[])
            .then(data=>renderClientes(Array.isArray(data)?data:[]))
            .catch(()=>renderClientes([]));
    });

    // === Calcular totales ===
    function actualizarTotales(){
        let subtotal=0;
        productosBody.querySelectorAll('tr').forEach(row=>{
            const c=parseFloat(row.querySelector('.cantidad-input')?.value||0);
            const p=parseFloat(row.querySelector('.precio-input')?.value||0);
            const sub=c*p; subtotal+=sub;
            const celda=row.querySelector('.subtotal-linea');
            if(celda) celda.textContent='Q'+sub.toFixed(2);
        });
        const desc=parseFloat(descuentoInput.value||0);
        const total=Math.max(subtotal-desc,0);
        const saldo=Math.max(total-abonadoServidor,0);
        subtotalSpan.textContent='Q'+subtotal.toFixed(2);
        descuentoSpan.textContent='Q'+desc.toFixed(2);
        abonoSpan.textContent='Q'+abonadoServidor.toFixed(2);
        totalSpan.textContent='Q'+saldo.toFixed(2);
    }

    // === Productos dinámicos ===
    function bindProductoRow(row){
        const sel=row.querySelector('.producto-select');
        const cant=row.querySelector('.cantidad-input');
        const pre=row.querySelector('.precio-input');
        const del=row.querySelector('.remove-row');
        sel.addEventListener('change',function(){
            const opt=sel.selectedOptions[0];
            if(opt&&opt.dataset.precio&&!pre.value) pre.value=parseFloat(opt.dataset.precio).toFixed(2);
            actualizarTotales();
        });
        cant.addEventListener('input',actualizarTotales);
        pre.addEventListener('input',actualizarTotales);
        del.addEventListener('click',()=>{ row.remove(); if(!productosBody.querySelector('tr')) addProductoRow(); actualizarTotales(); });
    }
    function addProductoRow(id='',cant=1,pre=''){
        const frag=productoTpl.content.cloneNode(true);
        const row=frag.querySelector('tr');
        const s=row.querySelector('.producto-select'); const c=row.querySelector('.cantidad-input'); const p=row.querySelector('.precio-input');
        if(id) s.value=id; c.value=cant;
        if(pre===''){ const opt=s.selectedOptions[0]; if(opt&&opt.dataset.precio) p.value=parseFloat(opt.dataset.precio).toFixed(2); }
        else p.value=parseFloat(pre).toFixed(2);
        productosBody.appendChild(row); bindProductoRow(row); actualizarTotales();
    }
    addProdBtn?.addEventListener('click',()=>addProductoRow());

    // === Abonos dinámicos ===
    function bindAbonoRow(row){
        const del=row.querySelector('.remove-row');
        del.addEventListener('click',()=>{ row.remove(); actualizarTotales(); });
        row.querySelectorAll('input').forEach(inp=>inp.addEventListener('input',actualizarTotales));
    }
    function addAbonoRow(fecha=null,monto=null){
        const frag=abonoTpl.content.cloneNode(true); const row=frag.querySelector('tr');
        if(fecha) row.querySelector('.fecha-abono-input').value=fecha;
        if(monto) row.querySelector('.monto-abono-input').value=parseFloat(monto).toFixed(2);
        abonosBody.appendChild(row); bindAbonoRow(row); actualizarTotales();
    }
    addAbonoBtn?.addEventListener('click',()=>addAbonoRow());

    // === Inicializar productos ===
    if(filasIniciales.length){ filasIniciales.forEach(f=>addProductoRow(f.producto_id,f.cantidad,f.precio)); } else addProductoRow();
    actualizarTotales();
    descuentoInput.addEventListener('input',actualizarTotales);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
