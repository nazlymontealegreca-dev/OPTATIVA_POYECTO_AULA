-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2026 at 03:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `deportes_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `descripcion`, `estado`) VALUES
(1, 'Pelota', 'Balones y pelotas deportivas', 'activa'),
(2, 'Raqueta', 'Raquetas para deportes de cancha', 'activa'),
(3, 'Uniforme', 'Uniformes deportivos institucionales', 'activa'),
(4, 'Protección', 'Elementos de protección deportiva', 'activa'),
(5, 'Fitness', 'Implementos para entrenamiento físico', 'activa'),
(6, 'Porterías y Redes', 'Mallas, redes y porterías deportivas', 'activa'),
(7, 'Calzado', 'Calzado deportivo institucional', 'activa'),
(8, 'Accesorios', 'Accesorios deportivos generales', 'activa');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_prestamo`
--

CREATE TABLE `detalle_prestamo` (
  `id_detalle` int(11) NOT NULL,
  `id_prestamo` int(11) NOT NULL,
  `id_implemento` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `estado_detalle` enum('prestado','devuelto') NOT NULL DEFAULT 'prestado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detalle_prestamo`
--

INSERT INTO `detalle_prestamo` (`id_detalle`, `id_prestamo`, `id_implemento`, `cantidad`, `estado_detalle`) VALUES
(1, 1, 7, 1, 'prestado'),
(2, 2, 5, 2, 'devuelto'),
(3, 3, 3, 2, 'devuelto'),
(4, 4, 2, 2, 'prestado'),
(5, 5, 2, 1, 'devuelto');

-- --------------------------------------------------------

--
-- Table structure for table `implementos`
--

CREATE TABLE `implementos` (
  `id_implemento` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nombre_implemento` varchar(100) NOT NULL,
  `codigo_implemento` varchar(30) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad_total` int(11) NOT NULL DEFAULT 0,
  `cantidad_disponible` int(11) NOT NULL DEFAULT 0,
  `estado` enum('disponible','prestado','mantenimiento','inactivo') NOT NULL DEFAULT 'disponible',
  `ubicacion` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `implementos`
--

INSERT INTO `implementos` (`id_implemento`, `id_categoria`, `nombre_implemento`, `codigo_implemento`, `descripcion`, `cantidad_total`, `cantidad_disponible`, `estado`, `ubicacion`, `fecha_registro`) VALUES
(1, 1, 'Balón de Fútbol Adidas', 'BAL-001', 'Balón para entrenamientos y partidos recreativos', 15, 15, 'disponible', 'Bodega deportiva FET', '2026-05-09 01:55:53'),
(2, 1, 'Balón de Baloncesto Spalding', 'BAL-002', 'Balón para prácticas de baloncesto', 8, 6, 'disponible', 'Bodega deportiva FET', '2026-05-09 01:55:53'),
(3, 2, 'Raqueta de Tenis Wilson', 'RAQ-001', 'Raqueta para entrenamientos deportivos', 6, 6, 'disponible', 'Bodega deportiva FET', '2026-05-09 01:55:53'),
(4, 3, 'Uniforme Deportivo Azul', 'UNI-001', 'Uniforme institucional deportivo', 25, 25, 'disponible', 'Bodega deportiva FET', '2026-05-09 01:55:53'),
(5, 4, 'Guantes de Portero', 'PRO-001', 'Guantes para entrenamientos de fútbol', 10, 10, 'disponible', 'Bodega deportiva FET', '2026-05-09 01:55:53'),
(6, 5, 'Mancuernas de Entrenamiento', 'FIT-001', 'Implementos de fuerza para rutinas básicas', 12, 12, 'disponible', 'Bodega deportiva FET', '2026-05-09 01:55:53'),
(7, 1, 'balon', '32', '3 colores', 1, 0, 'prestado', 'fet', '2026-05-11 20:13:15'),
(8, 3, 'uniforme futbol', 'UN 1', 'ninguna', 13, 0, 'prestado', 'FET', '2026-05-12 20:12:29'),
(9, 5, 'Conos de Entrenamiento', 'FIT-010', 'Conos plásticos para delimitación de espacios, circuitos de velocidad y ejercicios de coordinación.', 20, 20, 'disponible', 'Bodega deportiva FET', '2026-05-12 20:40:39'),
(10, 1, 'Balón de Voleibol Mikasa', 'BAL-003', 'Balón deportivo para prácticas y partidos de voleibol.', 10, 10, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(11, 1, 'Balón de Rugby Gilbert', 'BAL-004', 'Balón de rugby para entrenamientos deportivos.', 7, 7, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(12, 1, 'Pelota de Tenis Wilson', 'BAL-005', 'Set de pelotas de tenis para prácticas deportivas.', 30, 30, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(13, 2, 'Raqueta de Bádminton Yonex', 'RAQ-002', 'Raqueta liviana para entrenamientos de bádminton.', 12, 12, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(14, 2, 'Raqueta de Tenis Head', 'RAQ-003', 'Raqueta deportiva para prácticas de tenis.', 5, 5, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(15, 3, 'Uniforme Deportivo Rojo Talla M', 'UNI-002', 'Uniforme institucional para actividades deportivas.', 18, 18, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(16, 3, 'Uniforme Deportivo Verde Talla L', 'UNI-003', 'Uniforme institucional para entrenamientos y competencias.', 20, 20, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(17, 4, 'Rodilleras Deportivas', 'PRO-002', 'Rodilleras para protección durante entrenamientos deportivos.', 14, 14, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(18, 4, 'Coderas Deportivas', 'PRO-003', 'Coderas para actividades deportivas de contacto.', 14, 14, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(19, 5, 'Lazos para Saltar', 'FIT-002', 'Lazos deportivos para ejercicios de coordinación y resistencia.', 16, 16, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(20, 5, 'Colchonetas de Ejercicio', 'FIT-003', 'Colchonetas para rutinas de entrenamiento físico.', 10, 10, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(21, 6, 'Malla de Voleibol', 'RED-001', 'Malla deportiva para prácticas y partidos de voleibol.', 2, 2, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(22, 6, 'Red de Tenis de Mesa', 'RED-002', 'Red para mesa de ping pong o tenis de mesa.', 4, 4, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(23, 7, 'Guayos Deportivos Talla 40', 'CAL-001', 'Calzado deportivo para prácticas de fútbol.', 6, 6, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54'),
(24, 8, 'Silbato Deportivo', 'ACC-001', 'Silbato para control de entrenamientos y actividades deportivas.', 8, 8, 'disponible', 'Bodega deportiva FET', '2026-05-12 21:37:54');

-- --------------------------------------------------------

--
-- Table structure for table `prestamos`
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_administrador` int(11) NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_devolucion` date NOT NULL,
  `estado` enum('activo','devuelto','vencido','cancelado') NOT NULL DEFAULT 'activo',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `id_usuario`, `id_administrador`, `fecha_prestamo`, `fecha_devolucion`, `estado`, `observaciones`, `fecha_registro`) VALUES
(1, 6, 1, '2026-05-12', '2026-05-13', 'activo', '', '2026-05-12 20:21:23'),
(2, 6, 1, '2026-05-13', '2026-05-14', 'devuelto', '', '2026-05-12 20:28:43'),
(3, 6, 1, '2026-05-12', '2026-05-13', 'devuelto', '', '2026-05-12 20:45:53'),
(4, 6, 1, '2026-05-12', '2026-05-12', 'activo', '', '2026-05-12 20:55:03'),
(5, 6, 1, '2026-05-21', '2026-05-21', 'devuelto', '', '2026-05-12 21:02:36');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `documento` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('usuario','administrador') NOT NULL DEFAULT 'usuario',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_completo`, `correo`, `telefono`, `documento`, `password`, `rol`, `estado`, `fecha_registro`) VALUES
(1, 'Juan Díaz', 'juan.diaz@fet.edu.co', '+57 300 123 4567', '1000000001', '$2y$10$3JEBh/Eq9wRYnlkqzC2nO.e8EwrDfFtuT0OCcekwoLoU9TEEbCE1y', 'administrador', 'activo', '2026-05-09 01:55:53'),
(2, 'María González', 'maria.gonzalez@email.com', '+57 312 345 6789', '1079177490', '12345678', 'usuario', 'activo', '2026-05-09 01:55:53'),
(3, 'Carlos Ramírez', 'carlos.ramirez@email.com', '+57 310 555 7788', '1079000002', '12345678', 'usuario', 'activo', '2026-05-09 01:55:53'),
(4, 'Ana Torres', 'ana.torres@email.com', '+57 311 222 3344', '1079000003', '12345678', 'usuario', 'activo', '2026-05-09 01:55:53'),
(5, 'nazly', 'nazly_montealegreca@fet.edu.co', '3219881696', NULL, '$2y$10$clwSH3dSn6i.eC.m8XsXWu3AhG.srIohmWQyczg0rpytD7HyDs5yq', 'usuario', 'activo', '2026-05-11 19:13:12'),
(6, 'michel', 'michel@fet.edu.co', '3229881696', '1234567890', '$2y$10$3JRnhdzT1lvaf8OIhb3FJeB4nenM5yho6O8g2oaxR6EV6MDV0D1yS', 'usuario', 'activo', '2026-05-11 19:31:36'),
(7, 'maria del mar', 'mariadelmar@fet.edu.co', '3209224052', '1079176584', '$2y$10$NiarcKqP6.Iofs4PIRf4R.QmeYUCRJ.cTm48Gv.GGo7swOwHdC6b.', 'usuario', 'activo', '2026-05-13 13:08:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indexes for table `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_prestamo` (`id_prestamo`),
  ADD KEY `id_implemento` (`id_implemento`);

--
-- Indexes for table `implementos`
--
ALTER TABLE `implementos`
  ADD PRIMARY KEY (`id_implemento`),
  ADD UNIQUE KEY `codigo_implemento` (`codigo_implemento`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indexes for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_administrador` (`id_administrador`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `implementos`
--
ALTER TABLE `implementos`
  MODIFY `id_implemento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD CONSTRAINT `detalle_prestamo_ibfk_1` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamos` (`id_prestamo`),
  ADD CONSTRAINT `detalle_prestamo_ibfk_2` FOREIGN KEY (`id_implemento`) REFERENCES `implementos` (`id_implemento`);

--
-- Constraints for table `implementos`
--
ALTER TABLE `implementos`
  ADD CONSTRAINT `implementos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Constraints for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `prestamos_ibfk_2` FOREIGN KEY (`id_administrador`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
