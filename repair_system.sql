
-- Modified and Filled SQL Dump for repair_system

-- Set up environment
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Database: repair_system
CREATE DATABASE IF NOT EXISTS `repair_system`;
USE `repair_system`;

-- Table: customer
DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `tel` varchar(15) NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `customer` (`user_id`, `name`, `email`, `address`, `tel`) VALUES
(6, 'mo nai', 'thitakfvvo@hotmail.com', 'Bangkok', '0986579865'),
(7, 'mo nai', 'thltakfvvo@hotmail.com', 'Chiang Mai', '0958381090'),
(8, 'yam', 'thitakf@hotmail.com', 'Khon Kaen', '0986579865'),
(9, 'john doe', 'john@example.com', 'Nonthaburi', '0899999999'),
(10, 'jane smith', 'jane@example.com', 'Phuket', '0888888888');

-- Table: equipment
DROP TABLE IF EXISTS `equipment`;
CREATE TABLE `equipment` (
  `equipment_id` int NOT NULL AUTO_INCREMENT,
  `equipment_name` varchar(255) DEFAULT 'Unknown',
  `equipment_status` varchar(255) DEFAULT 'ใหม่',
  `warranty` varchar(255) DEFAULT NULL,
  `warranty_start_date` date DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `equipment_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `equipment` (`equipment_name`, `equipment_status`, `warranty`, `warranty_start_date`, `warranty_end_date`, `user_id`, `equipment_price`) VALUES
('จอ', 'ใช้งานปกติ', 'มีประกัน', '2024-01-01', '2025-01-01', 6, 2500.00),
('จอ', 'ใช้งานปกติ', 'ไม่มีประกัน', NULL, NULL, 6, 1800.00),
('จอคอม', 'ใหม่', 'ไม่มีการรับประกัน', NULL, NULL, 7, 3200.00),
('คอมตั้งโต้ะ', 'ใหม่', 'ไม่มีการรับประกัน', NULL, NULL, 8, 12000.00),
('โน๊ตบุ๊ค', 'เสีย', 'มีประกัน', '2023-06-01', '2024-06-01', 8, 15000.00);

-- Table: repairs
DROP TABLE IF EXISTS `repairs`;
CREATE TABLE `repairs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `issue_title` varchar(255) NOT NULL,
  `issue_description` text NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `repairs` (`username`, `issue_title`, `issue_description`, `status`) VALUES
('mo nai', 'จอมีเส้น', 'จอมีเส้นขึ้นแนวนอน', 'pending'),
('yam', 'คอมเปิดไม่ติด', 'กดเปิดแล้วไม่มีไฟขึ้นเลย', 'in progress'),
('jane smith', 'จอไม่แสดงภาพ', 'เชื่อมต่อกับคอมแล้วไม่ติด', 'done'),
('john doe', 'เครื่องร้อน', 'ใช้งานแค่แป๊บเดียวแล้วร้อนมาก', 'pending'),
('mo nai', 'เสียงลำโพงขาดๆหายๆ', 'เวลาเล่นวิดีโอจะได้ยินเสียงไม่ชัด', 'done');

COMMIT;
