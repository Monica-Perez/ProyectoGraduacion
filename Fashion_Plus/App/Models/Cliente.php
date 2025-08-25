<?php
class Cliente {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    public function insertar($datos) {
        try {
            $stmt = $this->db->prepare("CALL spInsertarCliente(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $datos['ID_emp'],
                $datos['Nombre_cli'],
                $datos['Apellido_cli'],
                $datos['Contacto_cli'],
                $datos['Telefono_cli'],
                $datos['Direccion_cli'],
                $datos['Correo_cli']
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al insertar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function editar($id, $datos) {
        try {
            $stmt = $this->db->prepare("CALL spEditarCliente(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $datos['ID_emp'],
                $datos['Nombre_cli'],
                $datos['Apellido_cli'],
                $datos['Contacto_cli'],
                $datos['Telefono_cli'],
                $datos['Direccion_cli'],
                $datos['Correo_cli']
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
}
