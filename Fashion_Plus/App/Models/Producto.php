<?php
class Producto {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    public function insertar($datos) {
        try {
            $stmt = $this->db->prepare("CALL spInsertarProducto(?, ?, ?)");
            $stmt->execute([
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio']
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al insertar producto: " . $e->getMessage());
            return false;
        }
    }

    public function editar($id, $datos) {
        try {
            $stmt = $this->db->prepare("CALL spEditarProducto(?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio']
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al editar producto: " . $e->getMessage());
            return false;
        }
    }

    public function ver() {
        try {
            $stmt = $this->db->prepare("CALL spVerProductos()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener productos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerProductoPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerProductoPorID(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();

            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $producto ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener producto por ID: " . $e->getMessage());
            return null;
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("CALL spEliminarProducto(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al eliminar producto: " . $e->getMessage());
            return false;
        }
    }
}
?>
