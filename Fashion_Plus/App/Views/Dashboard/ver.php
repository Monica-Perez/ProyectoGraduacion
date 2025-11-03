<?php
if (!isset($_SESSION))
  session_start();

if (!isset($_SESSION['usuario'])) {
  header('Location: ' . URL . 'usuario/login');
  exit;
}

$ini = $datos['ini'] ?? date('Y-m-01', strtotime('-5 months'));
$fin = $datos['fin'] ?? date('Y-m-d');
$kpis = $datos['kpis'] ?? ['total_pedidos' => 0, 'total_ventas_brutas' => 0, 'total_abonos' => 0, 'saldo_pendiente' => 0];
$estados = $datos['estados'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
  <link rel="icon" type="image/png" href="<?= URL ?>public/img/Icono.png">

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    .chart-box {
      position: relative;
      height: 320px;
      /* alto controlado */
      width: 100%;
    }

    @media (max-width: 768px) {
      .chart-box {
        height: 260px;
      }
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
          <li><a href="<?= URL ?>pedido/ver"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
          <li><a href="<?= URL ?>dashboard/ver" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
          <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
      </ul>
  </div>

  <div class="main-content">
    <div class="content-container">
      <div class="header">
        <div class="header-title">
          <h1><i class="fas fa-chart-pie fa-rosado"></i> Dashboard</h1>
          <p class="text-muted">Indicadores y estado general por rango de fechas.</p>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
              <label class="form-label">Desde</label>
              <input type="date" id="ini" class="form-control" value="<?= htmlspecialchars($ini) ?>">
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Hasta</label>
              <input type="date" id="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
            </div>
            <div class="col-12 col-md-3">
              <button id="btnAplicar" type="button" class="btn btn-rosado w-100">
                <i class="fas fa-sync"></i> Aplicar
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
          <div class="card shadow-sm p-3">
            <div class="text-muted small">Pedidos</div>
            <div class="fs-3 fw-bold" id="kpiPedidos"><?= (int) $kpis['total_pedidos'] ?></div>
          </div>
        </div>
        <div class="col-12 col-md-3">
          <div class="card shadow-sm p-3">
            <div class="text-muted small">Ventas (Bruto)</div>
            <div class="fs-3 fw-bold" id="kpiBruto">Q <?= number_format((float) $kpis['total_ventas_brutas'], 2) ?></div>
          </div>
        </div>
        <div class="col-12 col-md-3">
          <div class="card shadow-sm p-3">
            <div class="text-muted small">Abonos</div>
            <div class="fs-3 fw-bold" id="kpiAbonos">Q <?= number_format((float) $kpis['total_abonos'], 2) ?></div>
          </div>
        </div>
        <div class="col-12 col-md-3">
          <div class="card shadow-sm p-3">
            <div class="text-muted small">Saldo Pendiente</div>
            <div class="fs-3 fw-bold" id="kpiSaldo">Q <?= number_format((float) $kpis['saldo_pendiente'], 2) ?></div>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0"><i class="fas fa-chart-bar fa-rosado"></i> Pedidos por estado</h5>
        </div>
        <div class="card-body">
          <div class="chart-box">
            <canvas id="chartEstados" style="width:100%; height:100%"></canvas>
          </div>
        </div>

      </div>

      <div class="card">
        <div class="card-footer bg-white">
          <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-rosado me-2" onclick="window.print()" title="Imprimir">
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
    const BASE = "<?= rtrim(URL, '/'); ?>/";

    function normalizarEstados(arr) {
      return (arr || []).map(r => ({
        estado: r.estado ?? r.Estado ?? r.ESTADO ?? 'Sin estado',
        cantidad: Number(r.cantidad ?? r.Cantidad ?? r.CANTIDAD ?? 0)
      }));
    }

    let chartEstados;

    function buildEstados(raw) {
      const data = normalizarEstados(raw);
      const labels = data.map(r => r.estado);
      const values = data.map(r => r.cantidad);

      if (chartEstados) chartEstados.destroy();

      const el = document.getElementById('chartEstados');

      // Si no hay datos o todo es 0 -> dona "sin datos"
      const sinDatos = !values.length || values.every(v => v === 0);

      chartEstados = new Chart(el, {
        type: sinDatos ? 'doughnut' : 'doughnut',
        data: sinDatos ? {
          labels: ['Sin datos'],
          datasets: [{ data: [1], backgroundColor: ['#e9ecef'], borderWidth: 0 }]
        } : {
          labels,
          datasets: [{
            label: 'Pedidos',
            data: values,
            backgroundColor: ['#6f1d7a', '#ff6b8a', '#3da5ff', '#55d187', '#ffb020', '#9b59b6']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false, // respeta la altura de .chart-box
          cutout: sinDatos ? '75%' : '65%',
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12 } },
            tooltip: { enabled: !sinDatos }
          }
        }
      });
    }

    // KPIs (igual)
    function renderKPIs(k) {
      $('#kpiPedidos').text(k?.total_pedidos ?? 0);
      $('#kpiBruto').text('Q ' + Number(k?.total_ventas_brutas || 0));
      $('#kpiAbonos').text('Q ' + Number(k?.total_abonos || 0));
      $('#kpiSaldo').text('Q ' + Number(k?.saldo_pendiente || 0));
    }
    async function recargar() {
      const btn = document.getElementById('btnAplicar');
      const ini = document.getElementById('ini').value;
      const fin = document.getElementById('fin').value;
      const url = `${BASE}dashboard/kpisEstados?ini=${encodeURIComponent(ini)}&fin=${encodeURIComponent(fin)}`;

      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aplicando';

      const failsafe = setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> Aplicar';
      }, 12000);

      try {
        const controller = new AbortController();
        const t = setTimeout(() => controller.abort(), 15000);
        const resp = await fetch(url, { cache: 'no-store', signal: controller.signal });
        clearTimeout(t);

        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const j = await resp.json();

        renderKPIs(j?.kpis || {});
        buildEstados(j?.estados || []);

        const params = new URLSearchParams({ ini, fin });
        history.replaceState({}, '', `${BASE}dashboard/ver?${params.toString()}`);
      } catch (err) {
        console.error(err);
        alert('No se pudo actualizar el dashboard. Revisa la ruta /dashboard/kpisEstados o el SP.');
      } finally {
        clearTimeout(failsafe);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> Aplicar';
      }
    }

    // Bind y render inicial
    document.getElementById('btnAplicar').addEventListener('click', recargar);
    buildEstados(<?= json_encode($estados, JSON_UNESCAPED_UNICODE) ?>);
    document.addEventListener('DOMContentLoaded', recargar);
  </script>


</body>

</html>