-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 26, 2025 at 04:35 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_cursos`
--

-- --------------------------------------------------------

--
-- Table structure for table `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `nombre_curso` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `instructor` varchar(100) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `cupos_disponibles` int(11) NOT NULL DEFAULT 20,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cursos`
--

INSERT INTO `cursos` (`id_curso`, `nombre_curso`, `descripcion`, `instructor`, `fecha_inicio`, `fecha_fin`, `cupos_disponibles`, `precio`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Desarrollo Web con PHP', 'Curso completo de desarrollo web usando PHP y MySQL. Aprenderás desde los conceptos básicos hasta técnicas avanzadas para crear aplicaciones web dinámicas y seguras.', 'Dr. Juan Pérez', '2025-06-01', '2025-08-30', 25, 150.00, 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(2, 'JavaScript Avanzado', 'Programación avanzada en JavaScript y frameworks modernos como React, Vue.js y Node.js. Ideal para desarrolladores que quieren dominar el ecosistema JavaScript.', 'Ing. María González', '2025-06-15', '2025-09-15', 20, 180.00, 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(3, 'Base de Datos MySQL', 'Diseño y administración de bases de datos MySQL. Incluye normalización, optimización de consultas, procedimientos almacenados y técnicas de respaldo.', 'Lic. Carlos Rodríguez', '2025-07-01', '2025-09-30', 30, 120.00, 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(4, 'Python para Data Science', 'Introducción al análisis de datos con Python usando pandas, numpy, matplotlib y scikit-learn. Perfecto para iniciarse en la ciencia de datos.', 'Dra. Ana Martínez', '2025-06-20', '2025-08-20', 18, 200.00, 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(5, 'Diseño UX/UI', 'Fundamentos del diseño de experiencia de usuario e interfaz. Aprenderás a crear prototipos, wireframes y diseños centrados en el usuario usando Figma y Adobe XD.', 'Dis. Roberto Silva', '2025-07-15', '2025-10-15', 15, 175.00, 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(6, 'Ciberseguridad Básica', 'Conceptos fundamentales de seguridad informática, ethical hacking, y protección de sistemas. Incluye prácticas con herramientas de pentesting.', 'Ing. Patricia López', '2025-08-01', '2025-11-01', 22, 165.00, 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22');

-- --------------------------------------------------------

--
-- Table structure for table `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id_estudiante` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `id_usuario`, `cedula`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `direccion`, `fecha_registro`, `fecha_actualizacion`) VALUES
(1, 1, '1234567890', 'Juan Carlos', 'Pérez González', 'juan.perez@email.com', '0987654321', '1990-05-15', 'Av. 6 de Diciembre y Colón, Quito', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(2, 2, '0987654321', 'María Elena', 'López Martínez', 'maria.lopez@email.com', '0912345678', '1985-08-22', 'Calle García Moreno 123, Quito', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(3, 3, '1122334455', 'Carlos Andrés', 'Rodríguez Silva', 'carlos.rodriguez@email.com', '0923456789', '1992-12-10', 'Sector La Carolina, Quito', '2025-05-26 01:35:22', '2025-05-26 01:35:22'),
(4, 4, '1719537654', 'Jhonatan', 'Casaliglla', 'jhoisaac1191@gmail.com', '0939527322', '1991-11-11', 'Joaquín Sumaita, Quito Ecuador', '2025-05-26 01:52:31', '2025-05-26 02:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id_inscripcion` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_inscripcion` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `codigo_confirmacion` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inscripciones`
--

INSERT INTO `inscripciones` (`id_inscripcion`, `id_curso`, `id_estudiante`, `fecha_inscripcion`, `estado_inscripcion`, `codigo_confirmacion`, `observaciones`, `fecha_actualizacion`) VALUES
(1, 1, 1, '2025-05-26 01:35:22', 'confirmada', 'CONF-ABC12345', NULL, '2025-05-26 01:35:22'),
(2, 2, 1, '2025-05-26 01:35:22', 'confirmada', 'CONF-DEF67890', NULL, '2025-05-26 01:35:22'),
(3, 3, 2, '2025-05-26 01:35:22', 'confirmada', 'CONF-GHI13579', NULL, '2025-05-26 01:35:22'),
(4, 1, 2, '2025-05-26 01:35:22', 'pendiente', 'CONF-JKL24680', NULL, '2025-05-26 01:35:22'),
(5, 4, 3, '2025-05-26 01:35:22', 'confirmada', 'CONF-MNO97531', NULL, '2025-05-26 01:35:22'),
(7, 4, 4, '2025-05-26 02:19:14', 'confirmada', 'CONF-642A6642', NULL, '2025-05-26 02:19:14'),
(8, 1, 4, '2025-05-26 02:33:11', 'cancelada', 'CONF-22151E0B', 'Cambio por otro curso.', '2025-05-26 02:33:41');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido') DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_ultimo_acceso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
-- Clave 12345678

INSERT INTO `usuarios` (`id_usuario`, `email`, `password_hash`, `nombres`, `apellidos`, `cedula`, `telefono`, `fecha_nacimiento`, `direccion`, `estado`, `fecha_registro`, `fecha_actualizacion`, `fecha_ultimo_acceso`) VALUES
(1, 'juan.perez@email.com', '$2y$10$xN9Yykjr0ZfRj8DIgaCqie92TTph74/bU9gfQhRPCo7.BjZrlln8K', 'Juan Carlos', 'Pérez González', '1234567890', '0987654321', '1990-05-15', 'Av. 6 de Diciembre y Colón, Quito', 'activo', '2025-05-26 01:35:22', '2025-05-26 01:36:02', '2025-05-26 01:36:02'),
(2, 'maria.lopez@email.com', '$2y$10$xN9Yykjr0ZfRj8DIgaCqie92TTph74/bU9gfQhRPCo7.BjZrlln8K', 'María Elena', 'López Martínez', '0987654321', '0912345678', '1985-08-22', 'Calle García Moreno 123, Quito', 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22', NULL),
(3, 'carlos.rodriguez@email.com', '$2y$10$xN9Yykjr0ZfRj8DIgaCqie92TTph74/bU9gfQhRPCo7.BjZrlln8K', 'Carlos Andrés', 'Rodríguez Silva', '1122334455', '0923456789', '1992-12-10', 'Sector La Carolina, Quito', 'activo', '2025-05-26 01:35:22', '2025-05-26 01:35:22', NULL),
(4, 'jhoisaac1191@gmail.com', '$2y$10$oWGjgwDeo6PA46BEzrftz.RJA/88CYARTNEQl9T9QXIvPqsMfHVmG', 'Jhonatan', 'Casaliglla', '1719537654', '0939527322', '1991-11-11', 'Joaquín Sumaita, Quito Ecuador', 'activo', '2025-05-26 01:52:31', '2025-05-26 02:35:10', '2025-05-26 02:19:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_nombre_curso` (`nombre_curso`);

--
-- Indexes for table `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id_estudiante`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_cedula` (`cedula`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_nombres_apellidos` (`nombres`,`apellidos`);

--
-- Indexes for table `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD UNIQUE KEY `unique_inscripcion` (`id_curso`,`id_estudiante`),
  ADD UNIQUE KEY `codigo_confirmacion` (`codigo_confirmacion`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `idx_codigo_confirmacion` (`codigo_confirmacion`),
  ADD KEY `idx_estado_inscripcion` (`estado_inscripcion`),
  ADD KEY `idx_fecha_inscripcion` (`fecha_inscripcion`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_cedula` (`cedula`),
  ADD KEY `idx_estado` (`estado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id_estudiante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
