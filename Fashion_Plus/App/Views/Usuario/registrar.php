<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Registrar Usuario</h2>

    <?php if (!empty($datos['error'])): ?>
        <div class="alert alert-danger"><?= $datos['error'] ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= URL ?>usuario/registrar" class="card p-4 shadow-sm rounded" style="max-width: 500px;">
        <div class="mb-3">
            <label class="form-label">Usuario:</label>
            <input type="text" name="usuario" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña:</label>
            <input type="password" name="pass" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Rol:</label>
            <select name="rol" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="vendedor">Vendedor</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado:</label>
            <select name="estado" class="form-select" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Guardar</button>
    </form>
</div>
</body>
</html>
