<!DOCTYPE html>
<html>
<head>
    <title>Inicio</title>
    <link href="<?= URL ?>css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1><?= $datos['mensaje'] ?></h1>
        <p>Ya tienes tu MVC funcionando correctamente 🟢</p>
        <form method="POST" action="<?= URL ?>usuario/login">
        <a href="<?= URL ?>usuario/logout" class="btn btn-danger">Cerrar sesión</a>


    </div>
</body>
</html>
