-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-08-2025 a las 07:46:24
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `spEditarUsuario` (IN `p_id` INT, IN `p_usuario` VARCHAR(100), IN `p_rol` VARCHAR(15), IN `p_estado` VARCHAR(15))   BEGIN
    UPDATE usuario
    SET usuario = p_usuario,
        Rol_us = p_rol,
        Estado_us = p_estado
    WHERE ID_us = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spInsertarUsuario` (IN `p_usuario` VARCHAR(100), IN `p_pass` VARCHAR(255), IN `p_rol` VARCHAR(15), IN `p_estado` VARCHAR(10))   BEGIN
    INSERT INTO usuario (usuario, Pass, Rol_us, Estado_us, Fecha_Creacion)
    VALUES (p_usuario, p_pass, p_rol, p_estado, CURDATE());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spObtenerUsuarioPorID` (IN `p_id` INT)   BEGIN
    SELECT ID_us, usuario, Rol_us, Estado_us, Fecha_Creacion
    FROM usuario
    WHERE ID_us = p_id
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerificarUsuario` (IN `p_usuario` VARCHAR(100))   BEGIN
    SELECT ID_us, usuario, Pass, Rol_us, Estado_us
    FROM usuario
    WHERE usuario = p_usuario
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spVerUsuarios` ()   BEGIN
    SELECT ID_us, usuario, Rol_us, Estado_us, Fecha_Creacion
    FROM usuario
    ORDER BY ID_us DESC;
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
  `Monto_abono` decimal(10,2) DEFAULT NULL,
  `Total_Neto` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `ID_cli` int(11) NOT NULL,
  `ID_emp` int(11) DEFAULT NULL,
  `Nombre_cli` varchar(150) DEFAULT NULL,
  `Apellido_cli` varchar(150) DEFAULT NULL,
  `Contacto_cli` varchar(150) DEFAULT NULL,
  `Telefono_cli` varchar(11) DEFAULT NULL,
  `Direccion_cli` varchar(200) DEFAULT NULL,
  `Correo_cli` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `ID_pro` int(11) NOT NULL,
  `Nombre_pro` int(11) DEFAULT NULL,
  `Descripcion_pro` int(11) DEFAULT NULL,
  `Precio_pro` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 'vendedor3', '$2y$10$yi6Q6Pp8c7YNs1ej.uEUW.0M6Ztg0A7sQI04lZicGVbbZOrWlvioi', 'vendedor', 'activo', '2025-08-13');

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
  MODIFY `ID_abono` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `ID_cli` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `ID_det` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `ID_emp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `ID_ped` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_pro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_us` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
