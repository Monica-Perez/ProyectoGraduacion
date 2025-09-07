<?php
class Empresa {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    public function insertar($datos) {
        try {
            $stmt = $this->db->prepare("CALL spInsertarEmpresa(?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $datos['nombre'],
                $datos['nit'],
                $datos['contacto'],
                $datos['telefono'],
                $datos['direccion'],
                $datos['correo']
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al insertar empresa: " . $e->getMessage());
            return false;
        }
    }

    public function editar($id, $datos) {
        try {
            $stmt = $this->db->prepare("CALL spEditarEmpresa(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $datos['nombre'],
                $datos['nit'],
                $datos['contacto'],
                $datos['telefono'],
                $datos['direccion'],
                $datos['correo']
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al editar empresa: " . $e->getMessage());
            return false;
        }
    }

    public function ver() {
        try {
            $stmt = $this->db->prepare("CALL spVerEmpresas()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener lista de empresas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerEmpresaPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerEmpresaPorID(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();

            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $empresa ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener empresa por ID: " . $e->getMessage());
            return null;
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("CALL spEliminarEmpresa(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al eliminar empresa: " . $e->getMessage());
            return false;
        }
    }
}