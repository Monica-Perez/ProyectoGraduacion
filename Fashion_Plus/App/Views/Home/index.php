<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/css/estilos.css">
    <style>
        .imagen-logo {
            max-width: 400px;
            width: 90%;
            height: auto;
            display: block;
            margin: 30px auto;
        }

        .card-bienvenida {
            text-align: center;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .header-title h1 {
            font-size: 2.5rem;
            color: var(--secondary);
        }

        .header-title p {
            font-size: 1.1rem;
            color: var(--gray);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Fashion Plus</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= URL ?>inicio" class="active"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="<?= URL ?>usuario/ver"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="<?= URL ?>usuario/logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
        </ul>
    </div>

    <!-- Contenido principal -->
    <div class="main-content">
        <div class="content-container">
            <div class="card card-bienvenida">
                <div class="header-title">
                    <h1><i class="fas fa-home fa-rosado"></i> Bienvenido</h1>
                    <p class="text-muted">¡Bienvenida al sistema de Fashion Plus!</p>
                </div>
                <img src="<?= URL ?>public/img/Logo.png" alt="Logo" class="imagen-logo">
            </div>
        </div>
    </div>
</body>
</html>
