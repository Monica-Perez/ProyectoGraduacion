<?php
class Usuario {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    public function verificar($usuario, $pass) {
        $stmt = $this->db->prepare("CALL spVerificarUsuario(:usuario)");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($resultado && password_verify($pass, $resultado['Pass'])) {
            return $resultado;
        }

        return false;
    }

    public function insertar($usuario, $pass, $rol, $estado) {
        $stmt = $this->db->prepare("CALL spInsertarUsuario(:usuario, :pass, :rol, :estado)");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':pass', $pass);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':estado', $estado);
        $exito = $stmt->execute();
        $stmt->closeCursor();

        return $exito;
    }

    public function ver() {
        $stmt = $this->db->prepare("CALL spVerUsuarios()");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $usuarios;
    }

    public function editar($id, $datos) {
        try {
            $stmt = $this->db->prepare("CALL spEditarUsuario(?, ?, ?, ?)");

            $stmt->execute([
                $id,
                $datos['usuario'],
                $datos['rol'],
                $datos['estado']
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die("ERROR PDO: " . $e->getMessage()); // Para ver el error directamente en pantalla
        }
    }

    public function obtenerUsuarioPorId($id) {
        try {
            $query = "CALL spObtenerUsuarioPorID(?)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado) {
                return false;
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage());
            return false;
        }
    }

}
