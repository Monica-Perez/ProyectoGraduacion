-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-11-2025 a las 03:56:07
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fashion_plus`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `spActualizarUsuario` (IN `p_id` INT, IN `p_usuario` VARCHAR(100), IN `p_rol` VARCHAR(15), IN `p_estado` VARCHAR(15))   BEGIN
    UPDATE usuario
    SET usuario = p_usuario,
        Rol_us = p_rol,
        Estado_us = p_estado
    WHERE ID_us = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spDashboard_KPIs_Estados` (IN `p_ini` DATE, IN `p_fin` DATE)   BEGIN
    SELECT
        COUNT(p.ID_ped) AS total_pedidos,
        IFNULL(SUM(p.Total_ped), 0) AS total_ventas_brutas,
        (
            SELECT IFNULL(SUM(a.Monto_abono),0)
            FROM abono a
            WHERE a.Fecha_abono BETWEEN p_ini AND p_fin
        ) AS total_abonos,
        (IFNULL(SUM(p.Total_ped),0) -
         (
            SELECT IFNULL(SUM(a2.Monto_abono),0)
            FROM abono a2
            WHERE a2.Fecha_abono BETWEEN p_ini AND p_fin
         )) AS saldo_pendiente
    FROM pedido p
    WHERE p.Fecha_ped BETWEEN p_ini AND p_fin;

    SELECT p.Estado AS estado, COUNT(*) AS cantidad
    FROM pedido p
    WHERE p.Fecha_ped BETWEEN p_ini AND p_fin
    GROUP BY p.Estado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEditarCliente` (IN `p_ID_cli` INT, IN `p_ID_emp` INT, IN `p_Nombre_cli` VARCHAR(150), IN `p_Apellido_cli` VARCHAR(150), IN `p_Telefono_cli` VARCHAR(11), IN `p_Direccion_cli` VARCHAR(200), IN `p_Correo_cli` VARCHAR(150))   BEGIN
    UPDATE cliente
    SET
        ID_emp = p_ID_emp,
        Nombre_cli = p_Nombre_cli,
        Apellido_cli = p_Apellido_cli,
        Telefono_cli = p_Telefono_cli,
        Direccion_cli = p_Direccion_cli,
        Correo_cli = p_Correo_cli
    WHERE ID_cli = p_ID_cli;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEditarEmpresa` (IN `p_id` INT, IN `p_nombre` VARCHAR(200), IN `p_nit` VARCHAR(20), IN `p_contacto` VARCHAR(50), IN `p_telefono` VARCHAR(11), IN `p_direccion` VARCHAR(150), IN `p_correo` VARCHAR(120))   BEGIN
    UPDATE empresa
    SET Nombre_emp = p_nombre,
        NIT_emp = p_nit,
        Contacto_emp = p_contacto,
        Telefono_emp = p_telefono,
        Direccion_emp = p_direccion,
        Correo_emp = p_correo
    WHERE ID_emp = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEditarPedido` (IN `p_id` INT, IN `p_cliente` INT, IN `p_fecha` DATE, IN `p_descuento` DECIMAL(10,2), IN `p_total` DECIMAL(10,2), IN `p_estado` VARCHAR(50))   BEGIN
    UPDATE pedido
    SET ID_cli = p_cliente,
        Fecha_ped = p_fecha,
        Descuento = p_descuento,
        Total_ped = p_total,
        Estado = p_estado
    WHERE ID_ped = p_id;
    SELECT ROW_COUNT() AS filas;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEditarProducto` (IN `p_id` INT, IN `p_nombre` VARCHAR(150), IN `p_descripcion` VARCHAR(255), IN `p_precio` DECIMAL(10,2))   BEGIN
    UPDATE producto
    SET Nombre_pro = p_nombre,
        Descripcion_pro = p_descripcion,
        Precio_pro = p_precio
    WHERE ID_pro = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEditarUsuario` (IN `p_id` INT, IN `p_usuario` VARCHAR(100), IN `p_rol` VARCHAR(15), IN `p_estado` VARCHAR(15))   BEGIN
    UPDATE usuario
    SET usuario = p_usuario,
        Rol_us = p_rol,
        Estado_us = p_estado
    WHERE ID_us = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEliminarAbono` (IN `p_id` INT)   BEGIN
    DELETE FROM abono WHERE ID_abono = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEliminarCliente` (IN `p_id` INT)   BEGIN
    DELETE FROM cliente WHERE ID_cli = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEliminarDetallesPedido` (IN `p_id_ped` INT)   BEGIN
  DELETE FROM detalle_pedido
  WHERE ID_ped = p_id_ped;
  SELECT ROW_COUNT() AS filas;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEliminarEmpresa` (IN `p_id` INT)   BEGIN
	DELETE FROM cliente WHERE ID_emp = p_id;
    DELETE FROM empresa WHERE ID_emp = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEliminarPedido` (IN `p_id` INT)   BEGIN
    DELETE FROM pedidos WHERE ID_ped = p_id;
    SELECT ROW_COUNT() AS filas;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEliminarProducto` (IN `p_id` INT)   BEGIN
    DELETE FROM producto WHERE ID_pro = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEliminarUsuario` (IN `p_id` INT)   BEGIN
    DELETE FROM usuario WHERE ID_us = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarAbono` (IN `p_id_ped` INT, IN `p_fecha` DATE, IN `p_monto` DECIMAL(10,2))   BEGIN
    INSERT INTO abono (ID_ped, Fecha_abono, Monto_abono)
    VALUES (p_id_ped, p_fecha, p_monto);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarCliente` (IN `p_ID_emp` INT, IN `p_Nombre_cli` VARCHAR(150), IN `p_Apellido_cli` VARCHAR(150), IN `p_Telefono_cli` VARCHAR(11), IN `p_Direccion_cli` VARCHAR(200), IN `p_Correo_cli` VARCHAR(150))   BEGIN
    INSERT INTO cliente (
        ID_emp, 
        Nombre_cli, 
        Apellido_cli,
        Telefono_cli, 
        Direccion_cli, 
        Correo_cli
    )
    VALUES (
		p_ID_emp, 
        p_Nombre_cli, 
        p_Apellido_cli,
        p_Telefono_cli, 
        p_Direccion_cli, 
        p_Correo_cli
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarDetallePedido` (IN `p_id_ped` INT, IN `p_id_pro` INT, IN `p_cantidad` DECIMAL(10,2), IN `p_precio` DECIMAL(10,2))   BEGIN
  INSERT INTO detalle_pedido (ID_ped, ID_pro, Cantidad_det, PrecioUnitario_det) 
  VALUES (p_id_ped, p_id_pro, p_cantidad, p_precio);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarEmpresa` (IN `p_nombre` VARCHAR(200), IN `p_nit` VARCHAR(20), IN `p_contacto` VARCHAR(50), IN `p_telefono` VARCHAR(11), IN `p_direccion` VARCHAR(150), IN `p_correo` VARCHAR(120))   BEGIN
    INSERT INTO empresa (Nombre_emp, NIT_emp, Contacto_emp, Telefono_emp, Direccion_emp, Correo_emp)
    VALUES (p_nombre, p_nit, p_contacto, p_telefono, p_direccion, p_correo);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarPedido` (IN `p_id_cli` INT, IN `p_id_us` INT, IN `p_fecha` DATE, IN `p_descuento` DECIMAL(10,2), IN `p_total` DECIMAL(10,2), IN `p_estado` VARCHAR(10), OUT `p_id_pedido` INT)   BEGIN
    INSERT INTO pedido (ID_cli, ID_us, Fecha_ped, Descuento, Total_ped, Estado)
    VALUES (p_id_cli, p_id_us, p_fecha, p_descuento, p_total, p_estado);

    SET p_id_pedido = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarProducto` (IN `p_nombre` VARCHAR(150), IN `p_descripcion` VARCHAR(255), IN `p_precio` DECIMAL(10,2))   BEGIN
    INSERT INTO producto (Nombre_pro, Descripcion_pro, Precio_pro)
    VALUES (p_nombre, p_descripcion, p_precio);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarUsuario` (IN `p_usuario` VARCHAR(100), IN `p_pass` VARCHAR(255), IN `p_rol` VARCHAR(15), IN `p_estado` VARCHAR(10))   BEGIN
    INSERT INTO usuario (usuario, Pass, Rol_us, Estado_us, Fecha_Creacion)
    VALUES (p_usuario, p_pass, p_rol, p_estado, CURDATE());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerClientePorID` (IN `p_ID_cli` INT)   BEGIN
    SELECT 
        ID_cli,
        ID_emp,
        Nombre_cli,
        Apellido_cli,
        Telefono_cli,
        Direccion_cli,
        Correo_cli
    FROM cliente
    WHERE ID_cli = p_ID_cli;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerClientesPorEmpresa` (IN `p_id_emp` INT)   BEGIN
	SELECT	
		c.ID_cli,
		c.ID_emp,
		c.Nombre_cli,
		c.Apellido_cli,
		c.Telefono_cli,
		c.Direccion_cli,
		c.Correo_cli
	FROM cliente c
	WHERE c.ID_emp = p_id_emp
	ORDER BY c.Nombre_cli, c.Apellido_cli;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerDetallesPedido` (IN `p_id_ped` INT)   BEGIN
    SELECT 
		d.ID_det,
        d.ID_pro,
        d.Cantidad_det,
        d.PrecioUnitario_det,
        p.Nombre_pro,
        p.Descripcion_pro
    FROM detalle_pedido d
    LEFT JOIN producto p ON d.ID_pro = p.ID_pro
    WHERE d.ID_ped = p_id_ped;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerEmpresaPorID` (IN `p_id` INT)   BEGIN
    SELECT 
		ID_emp,
        Nombre_emp,
        NIT_emp,
        Contacto_emp,
        Telefono_emp,
        Direccion_emp,
        Correo_emp
    FROM empresa WHERE ID_emp = p_id LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerPedidoPorID` (IN `p_id` INT)   BEGIN
	SELECT p.ID_ped,
        e.ID_emp,
        c.ID_cli,
		p.Fecha_ped,
		p.Estado,
		p.Descuento,
		p.Total_ped
	FROM pedido p
	LEFT JOIN cliente c ON p.ID_cli = c.ID_cli
	LEFT JOIN empresa e ON c.ID_emp = e.ID_emp
    WHERE p.ID_ped = p_id
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerProductoPorID` (IN `p_id` INT)   BEGIN
    SELECT ID_pro, Nombre_pro, Descripcion_pro, Precio_pro
    FROM producto
    WHERE ID_pro = p_id
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerResumenPago` (IN `p_id` INT)   BEGIN
	SELECT 
		p.Total_ped,
        IFNULL((
			SELECT SUM(a.Monto_abono)
            FROM abono a
            WHERE a.ID_ped = p.ID_ped
		), 0) AS total_abonado
	FROM pedido p
    WHERE p.ID_ped = p.id
	LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerUsuarioPorID` (IN `p_id` INT)   BEGIN
    SELECT ID_us, usuario, Rol_us, Estado_us, Fecha_Creacion
    FROM usuario
    WHERE ID_us = p_id
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerAbonosPorPedido` (IN `p_id_ped` INT)   BEGIN
    SELECT 
        ID_abono,
        Fecha_abono,
        Monto_abono
    FROM abono
    WHERE ID_ped = p_id_ped
    ORDER BY Fecha_abono ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerClientes` ()   BEGIN
    SELECT 
        ID_cli,
        Nombre_emp,
        Nombre_cli,
        Apellido_cli,
        Telefono_cli,
        Direccion_cli,
        Correo_cli
    FROM cliente c
    INNER JOIN empresa e ON c.ID_emp = e.ID_emp
    ORDER BY ID_cli DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerEmpresas` ()   BEGIN
    SELECT 
		ID_emp,
        Nombre_emp,
        NIT_emp,
        Contacto_emp,
        Telefono_emp,
        Direccion_emp,
        Correo_emp
	FROM empresa ORDER BY ID_emp DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerificarUsuario` (IN `p_usuario` VARCHAR(100))   BEGIN
    SELECT ID_us, usuario, Pass, Rol_us, Estado_us
    FROM usuario
    WHERE usuario = p_usuario
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerPedidos` ()   BEGIN
	SELECT
		p.ID_ped,
		CONCAT(COALESCE(c.Nombre_cli,''),' ',COALESCE(c.Apellido_cli,'')) AS Cliente,
		c.Correo_cli,
		c.Telefono_cli,
		p.Fecha_ped,
		u.usuario AS Usuario,
		p.Estado,
		p.Descuento,
		p.Total_ped,
		IFNULL(ab.suma_abonos,0) AS Abono,
		GREATEST(p.Total_ped - IFNULL(ab.suma_abonos,0), 0) AS Saldo
	FROM pedido p
	LEFT JOIN cliente c ON p.ID_cli = c.ID_cli
	LEFT JOIN usuario u ON p.ID_us = u.ID_us
	LEFT JOIN (
		SELECT ID_ped, SUM(Monto_abono) AS suma_abonos
		FROM abono
		GROUP BY ID_ped
	) ab ON ab.ID_ped = p.ID_ped
	ORDER BY p.Estado DESC, p.Fecha_ped DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerProductos` ()   BEGIN
    SELECT ID_pro, Nombre_pro, Descripcion_pro, Precio_pro
    FROM producto
    ORDER BY ID_pro DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerUsuarios` ()   BEGIN
    SELECT ID_us, usuario, Rol_us, Estado_us, Fecha_Creacion
    FROM usuario
    ORDER BY ID_us;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `abono`
--

CREATE TABLE `abono` (
  `ID_abono` int(11) NOT NULL,
  `ID_ped` int(11) DEFAULT NULL,
  `Fecha_abono` date DEFAULT NULL,
  `Monto_abono` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `abono`
--

INSERT INTO `abono` (`ID_abono`, `ID_ped`, `Fecha_abono`, `Monto_abono`) VALUES
(11, 4, '2025-10-23', 50.00),
(30, 6, '2025-11-02', 50.00),
(31, 6, '2025-11-02', 50.00),
(59, 3, '2025-10-22', 10.00),
(60, 3, '2025-10-22', 50.00),
(61, 3, '2025-11-02', 45.00),
(62, 1, '2025-10-20', 100.00),
(63, 1, '2025-10-20', 20.00),
(64, 1, '2025-11-02', 100.00),
(66, 5, '2025-11-02', 110.00),
(67, 5, '2025-11-03', 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `ID_cli` int(11) NOT NULL,
  `ID_emp` int(11) DEFAULT NULL,
  `Nombre_cli` varchar(150) DEFAULT NULL,
  `Apellido_cli` varchar(150) DEFAULT NULL,
  `Telefono_cli` varchar(11) DEFAULT NULL,
  `Direccion_cli` varchar(200) DEFAULT NULL,
  `Correo_cli` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`ID_cli`, `ID_emp`, `Nombre_cli`, `Apellido_cli`, `Telefono_cli`, `Direccion_cli`, `Correo_cli`) VALUES
(3, 2, 'Juan Marcos', 'Lopez', '87569830', 'Calzada Roosevelt 10-20, Zona 7', 'jl@gmail.com'),
(8, 6, 'Javier', 'Avalos', '77777777', 'Calzada Roosevelt 10-20, Zona 7', 'jl@gmail.com'),
(9, 1, 'Rosa ', 'Muñoz', '78546325', '12 calle zona 1', 'rm@hotmail.com'),
(10, 6, 'Mario', 'Lopez', '85968596', 'Km 9 carretera al Atlantico', 'ml@gmail.com'),
(11, 5, 'Dana', 'Perez', '85852020', 'Zona 17 ', 'dp@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `ID_det` int(11) NOT NULL,
  `ID_ped` int(11) DEFAULT NULL,
  `ID_pro` int(11) DEFAULT NULL,
  `Cantidad_det` int(11) DEFAULT NULL,
  `PrecioUnitario_det` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`ID_det`, `ID_ped`, `ID_pro`, `Cantidad_det`, `PrecioUnitario_det`) VALUES
(18, 2, 2, 10, 75.00),
(41, 4, 4, 1, 60.00),
(42, 4, 2, 1, 75.00),
(64, 6, 5, 3, 90.00),
(80, 3, 4, 1, 60.00),
(81, 3, 2, 1, 75.00),
(82, 1, 2, 3, 75.00),
(91, 7, 4, 1, 60.00),
(92, 7, 2, 2, 75.00),
(93, 5, 4, 1, 60.00),
(94, 5, 2, 2, 75.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `ID_emp` int(11) NOT NULL,
  `Nombre_emp` varchar(200) DEFAULT NULL,
  `NIT_emp` varchar(20) DEFAULT NULL,
  `Contacto_emp` varchar(50) DEFAULT NULL,
  `Telefono_emp` varchar(11) DEFAULT NULL,
  `Direccion_emp` varchar(150) DEFAULT NULL,
  `Correo_emp` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`ID_emp`, `Nombre_emp`, `NIT_emp`, `Contacto_emp`, `Telefono_emp`, `Direccion_emp`, `Correo_emp`) VALUES
(1, 'Tabcin', '11111', 'Juan Perez', '52369857', 'Calzada Roosevelt 10-20, Zona 7', 'jp@gmail.com'),
(2, 'Cafetalito', '22222', 'Maria Lopez', '78596325', 'Parroquia zona 6', 'ml@gmail.com'),
(5, 'Pollo Campero', '1256987', 'Maria Lopez', '85698569', 'Zona 16', 'pc@gmail.com'),
(6, 'Samsung', '8965230', 'Lucas Lopez', '78963201', 'Calzada Roosevelt 10-20, Zona 7', 'ml@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `ID_ped` int(11) NOT NULL,
  `ID_cli` int(11) DEFAULT NULL,
  `ID_us` int(11) DEFAULT NULL,
  `Fecha_ped` date DEFAULT NULL,
  `Descuento` decimal(10,2) DEFAULT NULL,
  `Total_ped` decimal(10,2) DEFAULT NULL,
  `Estado` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`ID_ped`, `ID_cli`, `ID_us`, `Fecha_ped`, `Descuento`, `Total_ped`, `Estado`) VALUES
(1, 8, 2, '2025-09-29', 5.00, 220.00, 'completado'),
(2, 3, 2, '2025-10-18', 100.00, 650.00, 'pendiente'),
(3, 8, 2, '2025-10-20', 30.00, 105.00, 'completado'),
(4, 9, 2, '2025-10-23', 10.00, 125.00, 'pendiente'),
(5, 10, 2, '2025-11-02', 0.00, 210.00, 'en proceso'),
(6, 11, 2, '2025-11-02', 0.00, 270.00, 'en proceso'),
(7, 3, 2, '2025-11-03', 50.00, 160.00, 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `ID_pro` int(11) NOT NULL,
  `Nombre_pro` varchar(150) DEFAULT NULL,
  `Descripcion_pro` varchar(255) DEFAULT NULL,
  `Precio_pro` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`ID_pro`, `Nombre_pro`, `Descripcion_pro`, `Precio_pro`) VALUES
(2, 'Polo Sincatex', 'Camisa tipo polo en tela sincatex', 75.00),
(4, 'Chaleco', 'Chaleco de tela', 60.00),
(5, 'Polo Algodon ', 'Camisa tipo polo de algodon', 90.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `ID_us` int(11) NOT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `Pass` varchar(255) DEFAULT NULL,
  `Rol_us` varchar(15) DEFAULT NULL,
  `Estado_us` varchar(10) DEFAULT NULL,
  `Fecha_Creacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID_us`, `usuario`, `Pass`, `Rol_us`, `Estado_us`, `Fecha_Creacion`) VALUES
(2, 'admin', '$2y$10$hCy3EoBOZW84zb8IjZ7DbOx.XXEs/2G.uG8G6rZZkn8M31YOhGDCC', 'admin', 'activo', '2025-08-12'),
(3, 'vendedor', '$2y$10$wVlsym/8gSMyAuno5UdoH.Rj9Iw9sr15x3xa1bnra3i4KbzZ8szVi', 'vendedor', 'activo', '2025-08-13'),
(4, 'vendedor3', '$2y$10$yi6Q6Pp8c7YNs1ej.uEUW.0M6Ztg0A7sQI04lZicGVbbZOrWlvioi', 'vendedor', 'activo', '2025-08-13'),
(5, 'Monica', '$2y$10$n0II1.ddhw7UGCIuUgtFiunOTZTl8Y8uvlhBSTHqfSqeM95y9Hrlq', 'vendedor', 'activo', '2025-08-15'),
(6, 'adminEditado', '$2y$10$Q/YxfehLV.5zSdz6UqUoUOkYk5WzGuVAYaKow6HICbGkjeJj0hEQi', 'admin', 'activo', '2025-08-15'),
(7, 'admin', '$2y$10$pn/NGRj0RAxVhp6lbLKBm.jigCV71a71KzSuVm5ImKEpiXhor3EEO', 'admin', 'activo', '2025-08-15'),
(8, 'jaiva', '$2y$10$EsryTNYiWOUgwC5YCHVmUuvzyOGfOU1M88MTY24018MK/giQSAGMW', 'vendedor', 'inactivo', '2025-08-16'),
(9, 'pruebaadmin', '$2y$10$bZJAoNguSsKn1ZwQuyZdf.AdgXucrDVbB3BFONpXF.XdyiqRs5TWa', 'admin', 'activo', '2025-08-27'),
(10, 'CasoPrueba', '$2y$10$zIip2eBcLgRvT0WrTiGyGurxTwkPBQ/2cWaUUGY0AvBECmKYxkcj2', 'admin', 'activo', '2025-09-07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `abono`
--
ALTER TABLE `abono`
  ADD PRIMARY KEY (`ID_abono`),
  ADD KEY `fk_id_ped_abono` (`ID_ped`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`ID_cli`),
  ADD KEY `fk_id_emp` (`ID_emp`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`ID_det`),
  ADD KEY `fk_id_ped` (`ID_ped`),
  ADD KEY `fk_id_pro` (`ID_pro`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`ID_emp`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`ID_ped`),
  ADD KEY `fk_id_cli` (`ID_cli`),
  ADD KEY `fk_id_us` (`ID_us`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`ID_pro`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_us`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `abono`
--
ALTER TABLE `abono`
  MODIFY `ID_abono` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `ID_cli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `ID_det` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `ID_emp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `ID_ped` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_pro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_us` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `abono`
--
ALTER TABLE `abono`
  ADD CONSTRAINT `fk_id_ped_abono` FOREIGN KEY (`ID_ped`) REFERENCES `pedido` (`ID_ped`);

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_id_emp` FOREIGN KEY (`ID_emp`) REFERENCES `empresa` (`ID_emp`);

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `fk_id_ped` FOREIGN KEY (`ID_ped`) REFERENCES `pedido` (`ID_ped`),
  ADD CONSTRAINT `fk_id_pro` FOREIGN KEY (`ID_pro`) REFERENCES `producto` (`ID_pro`);

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_id_cli` FOREIGN KEY (`ID_cli`) REFERENCES `cliente` (`ID_cli`),
  ADD CONSTRAINT `fk_id_us` FOREIGN KEY (`ID_us`) REFERENCES `usuario` (`ID_us`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
