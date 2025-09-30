<?php
class Cliente {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    public function insertar($datos) {
        try {
            $stmt = $this->db->prepare("CALL spInsertarCliente(?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $datos['ID_emp'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['telefono'],
                $datos['direccion'],
                $datos['correo']
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al insertar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function editar($id, $datos) {
        try {
            $stmt = $this->db->prepare("CALL spEditarCliente(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $datos['ID_emp'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['telefono'],
                $datos['direccion'],
                $datos['correo']
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al editar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function ver() {
        try {
            $stmt = $this->db->prepare("CALL spVerClientes()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener lista de clientes: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerClientePorId($id) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerClientePorID(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();

            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $cliente ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener cliente por ID: " . $e->getMessage());
            return null;
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("CALL spEliminarCliente(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerClientesPorEmpresa($id) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerClientesPorEmpresa(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();

            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC); // <-- todos los registros
            $stmt->closeCursor();

            return $clientes ?: []; // siempre devuelve array
        } catch (PDOException $e) {
            error_log("Error al obtener Clientes por Empresa: " . $e->getMessage());
            return [];
        }
    }


}
