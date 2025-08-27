-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 27, 2025 at 03:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `frotas_gov`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'Usuário que realizou a ação. NULL se for uma ação do sistema.',
  `action` varchar(50) NOT NULL COMMENT 'Ex: create, update, delete, login_success, login_fail',
  `table_name` varchar(100) DEFAULT NULL COMMENT 'A tabela que foi afetada',
  `record_id` int(11) DEFAULT NULL COMMENT 'O ID do registro que foi afetado',
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `hashed_validator` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `selector`, `hashed_validator`, `user_id`, `expires_at`) VALUES
(1, '180aa2acdc124ee93f8f9e8a57ee8af4', 'd0b48351bd4d6812f4a3b579e2efeaeeed7804f2f0cb40d12799474b7542a1c4', 2, '2025-09-25 22:30:22'),
(2, '9b74589c36b83982f563170df2fbc286', 'e5237a868f0db8cc1baf5526c4495cae499ebbc1429444aa6760bda44d59d61c', 2, '2025-09-25 22:30:30'),
(3, 'cf4bb009a9aac87ef0d2f905141fec64', '7304019ddaec9efbb5f1db0617dbd65bd369c7f7fc550618701832c604956753', 2, '2025-09-25 22:31:10'),
(4, '718fe68ce9e65fab627938827619a040', '103e92f4cb18726625b33716f221e10c5224d643cc2b7cfdd6d5002b3103399f', 2, '2025-09-25 22:54:11'),
(5, 'f1ce386bc68e772771462f2883b1acba', '89cf4f67209e4acfeb13d119c47a839da10d76a8980b64e24917c6bf099f35ad', 2, '2025-09-26 13:10:41'),
(6, '93fcbe729f7e7cddc57af65ead4e4334', '52584bfa5507fb48bb87a1e2c09f93e01b19414729256315e4aff9db15eb150e', 2, '2025-09-26 13:29:25'),
(7, '05c5c78a1119af1067e9566de7f8cc9e', 'd34f3ce2c0976135f3bbc0b20c04a6821af1a624acde90382e65045e6c428794', 2, '2025-09-26 13:37:06'),
(8, 'b93501e3e9449970f84e6350ef873d9b', '134d05fa043340841870e862f234940de12e614894fcc9bf2669f1a42ed53927', 2, '2025-09-26 13:38:16'),
(9, '6e186438a66d13c6dd4108f57b49434a', '4e23fa6e564b6fb960e85f104a1dfce3c103b4dcd4f031954cf629dfe71309e6', 2, '2025-09-26 15:22:04'),
(10, '7af825d8923c94a406554a38c5a5d60d', 'e988ee111f46692a65531e825867ff015f6a8baec29e86d4c5d9f4d82469ec1c', 2, '2025-09-26 15:35:21'),
(11, 'd116dbd16a9408ef483cdb9aa7103029', 'ee1a02676586ce02ea37bc0231cad001cfbb671f13ea4ea81b92fc1aba681c09', 2, '2025-09-26 15:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `checklists`
--

CREATE TABLE `checklists` (
  `id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_answers`
--

CREATE TABLE `checklist_answers` (
  `id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `status` enum('ok','attention','problem') NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_items`
--

CREATE TABLE `checklist_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checklist_items`
--

INSERT INTO `checklist_items` (`id`, `name`, `description`) VALUES
(1, 'Combustível', NULL),
(2, 'Água', 'Nível do reservatório do radiador'),
(3, 'Óleo', 'Nível do óleo do motor'),
(4, 'Bateria', NULL),
(5, 'Pneus', NULL),
(6, 'Filtro de Ar', NULL),
(7, 'Lâmpadas', 'Faróis, setas, luz de freio'),
(8, 'Sistema Elétrico', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `secretariat_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fuelings`
--

CREATE TABLE `fuelings` (
  `id` int(11) NOT NULL,
  `run_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `gas_station_id` int(11) DEFAULT NULL,
  `gas_station_name` varchar(150) DEFAULT NULL COMMENT 'Para abastecimento manual',
  `km` int(10) UNSIGNED NOT NULL,
  `liters` decimal(10,2) NOT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `total_value` decimal(10,2) DEFAULT NULL,
  `invoice_path` varchar(255) DEFAULT NULL,
  `is_manual` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gas_stations`
--

CREATE TABLE `gas_stations` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `fuel_price_per_liter` decimal(10,3) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Ex: general_manager, sector_manager, mechanic, driver',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'general_manager', 'Gestor Geral - Acesso total ao sistema'),
(2, 'sector_manager', 'Gestor Setorial - Acesso à sua secretaria'),
(3, 'mechanic', 'Mecânico - Acesso ao painel de manutenção'),
(4, 'driver', 'Motorista - Acesso ao diário de bordo');

-- --------------------------------------------------------

--
-- Table structure for table `runs`
--

CREATE TABLE `runs` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `start_km` int(10) UNSIGNED NOT NULL,
  `end_km` int(10) UNSIGNED DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `destination` varchar(255) NOT NULL,
  `stop_point` varchar(255) DEFAULT NULL,
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'in_progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secretariats`
--

CREATE TABLE `secretariats` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `secretariats`
--

INSERT INTO `secretariats` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Administração', '2025-08-26 20:00:39', '2025-08-26 20:00:39'),
(2, 'Educação', '2025-08-26 20:00:39', '2025-08-26 20:00:39'),
(3, 'Saúde', '2025-08-26 20:00:39', '2025-08-26 20:00:39'),
(4, 'Obras e Serviços Públicos', '2025-08-26 20:00:39', '2025-08-26 20:00:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cpf` varchar(17) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Armazena o hash da senha (Bcrypt)',
  `role_id` int(11) NOT NULL,
  `secretariat_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `cnh_number` varchar(20) DEFAULT NULL,
  `cnh_expiry_date` date DEFAULT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `cpf`, `email`, `password`, `role_id`, `secretariat_id`, `department_id`, `cnh_number`, `cnh_expiry_date`, `profile_photo_path`, `phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin Geral', '11122233344', 'admin@frotas.gov', '$2y$10$WknxPAb.e./JpX8Idgs6SemVv.7g75Lz29kE7J1wJ5VvshXn5B.eK', 1, 1, NULL, NULL, NULL, NULL, NULL, 'active', '2025-08-26 20:02:32', '2025-08-26 20:02:32'),
(2, 'ALEXANDRE ZANATA', '12345678911', 'admin@example.com', '$2y$10$TLVbMeZPafx1qLYqCWGqcOtAhw2NrYfczQcCG5hK872ARZlgEqY1y', 4, 4, NULL, NULL, NULL, NULL, NULL, 'active', '2025-08-26 20:29:49', '2025-08-26 20:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Ex: FORD/RANGER XL CD4',
  `plate` varchar(10) NOT NULL COMMENT 'Placa do veículo',
  `prefix` varchar(20) NOT NULL COMMENT 'Prefixo ou abreviação, ex: V-123',
  `current_secretariat_id` int(11) NOT NULL,
  `fuel_tank_capacity_liters` decimal(5,2) DEFAULT NULL,
  `avg_km_per_liter` decimal(5,2) DEFAULT NULL,
  `status` enum('available','in_use','maintenance','blocked') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `selector_idx` (`selector`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `checklists`
--
ALTER TABLE `checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `run_id` (`run_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `checklist_answers`
--
ALTER TABLE `checklist_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `checklist_items`
--
ALTER TABLE `checklist_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secretariat_id` (`secretariat_id`);

--
-- Indexes for table `fuelings`
--
ALTER TABLE `fuelings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `run_id` (`run_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `gas_station_id` (`gas_station_id`);

--
-- Indexes for table `gas_stations`
--
ALTER TABLE `gas_stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `runs`
--
ALTER TABLE `runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `secretariats`
--
ALTER TABLE `secretariats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `secretariat_id` (`secretariat_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate` (`plate`),
  ADD UNIQUE KEY `prefix` (`prefix`),
  ADD KEY `current_secretariat_id` (`current_secretariat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `checklists`
--
ALTER TABLE `checklists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklist_answers`
--
ALTER TABLE `checklist_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklist_items`
--
ALTER TABLE `checklist_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fuelings`
--
ALTER TABLE `fuelings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gas_stations`
--
ALTER TABLE `gas_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `runs`
--
ALTER TABLE `runs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `secretariats`
--
ALTER TABLE `secretariats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `auth_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `checklists`
--
ALTER TABLE `checklists`
  ADD CONSTRAINT `checklists_ibfk_1` FOREIGN KEY (`run_id`) REFERENCES `runs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checklists_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checklists_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `checklist_answers`
--
ALTER TABLE `checklist_answers`
  ADD CONSTRAINT `checklist_answers_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `checklists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checklist_answers_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `checklist_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`secretariat_id`) REFERENCES `secretariats` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fuelings`
--
ALTER TABLE `fuelings`
  ADD CONSTRAINT `fuelings_ibfk_1` FOREIGN KEY (`run_id`) REFERENCES `runs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fuelings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fuelings_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fuelings_ibfk_4` FOREIGN KEY (`gas_station_id`) REFERENCES `gas_stations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `runs`
--
ALTER TABLE `runs`
  ADD CONSTRAINT `runs_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `runs_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`secretariat_id`) REFERENCES `secretariats` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`current_secretariat_id`) REFERENCES `secretariats` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;