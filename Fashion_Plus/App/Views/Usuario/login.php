<?php if (!isset($_SESSION)) session_start(); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>

    <!-- Bootstrap y FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">

    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .login-card h2 {
            margin-bottom: 20px;
            text-align: center;
            color: var(--primary);
        }

        .btn-rosado {
            background-color: #5a2149;
            color: white;
            border: none;
        }

        .btn-rosado:hover {
            background-color: #8f3b92;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2><i class="fas fa-user-circle"></i> Iniciar Sesión</h2>

    <?php if (!empty($datos['error'])): ?>
        <div class="alert alert-danger"><?= $datos['error'] ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= URL ?>usuario/login">
        <div class="mb-3">
            <label for="usuario" class="form-label">Usuario:</label>
            <input type="text" name="usuario" id="usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="pass" class="form-label">Contraseña:</label>
            <input type="password" name="pass" id="pass" class="form-control" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-rosado"><i class="fas fa-sign-in-alt"></i> Entrar</button>
        </div>
    </form>
</div>

</body>
</html>
