<?php
class Dashboard
{
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    // $datos: ['ini' => 'YYYY-MM-DD', 'fin' => 'YYYY-MM-DD']
    public function kpisEstados(array $datos): array {
        $ini = $datos['ini'] ?? date('Y-m-d', strtotime('-90 days'));
        $fin = $datos['fin'] ?? date('Y-m-d');

        $stmt = $this->db->prepare("CALL spDashboard_KPIs_Estados(?, ?)");
        $stmt->execute([$ini, $fin]);

        $kpis = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_pedidos'        => 0,
            'total_ventas_brutas'  => 0.0,
            'total_abonos'         => 0.0,
            'saldo_pendiente'      => 0.0
        ];

        $stmt->nextRowset();
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmt->closeCursor();

        return ['kpis' => $kpis, 'estados' => $estados];
    }
}
