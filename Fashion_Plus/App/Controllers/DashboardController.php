<?php
class DashboardController extends Controller
{
    public function __construct() {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }
    }

    public function ver() {
        // Últimos 6 meses por defecto (desde el primer día de hace 5 meses)
        $fin = $_GET['fin'] ?? date('Y-m-d');
        $ini = $_GET['ini'] ?? date('Y-m-01', strtotime('-5 months'));

        $dashboardModel = $this->model('Dashboard');
        $res = $dashboardModel->kpisEstados(['ini' => $ini, 'fin' => $fin]);

        $this->view('dashboard/ver', [
            'ini'     => $ini,
            'fin'     => $fin,
            'kpis'    => $res['kpis'] ?? [],
            'estados' => $res['estados'] ?? []
        ]);
    }

    // /dashboard/kpis-estados  -> JSON (usado por fetch)
    public function kpisEstados() {
        header('Content-Type: application/json; charset=utf-8');

        $fin = $_GET['fin'] ?? date('Y-m-d');
        $ini = $_GET['ini'] ?? date('Y-m-01', strtotime('-5 months'));

        $dashboardModel = $this->model('Dashboard');
        $res = $dashboardModel->kpisEstados(['ini' => $ini, 'fin' => $fin]);

        echo json_encode([
            'ini'     => $ini,
            'fin'     => $fin,
            'kpis'    => $res['kpis'] ?? [],
            'estados' => $res['estados'] ?? []
        ]);
        exit;
    }
}
