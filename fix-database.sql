-- ============================================
-- KPI Dashboard - Fix Missing Database Tables
-- ============================================
-- Upload via cPanel phpMyAdmin or WP-CLI
-- Database: wp_* (dengan prefix WordPress Anda)
-- ============================================

-- 1. Create kpi_kpis table (if not exists)
CREATE TABLE IF NOT EXISTS `wp_kpi_kpis` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `description` text,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `position_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `measurement_type` enum('number','percentage','currency','time','text') DEFAULT 'number',
  `target_value` decimal(15,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `frequency` enum('daily','weekly','monthly','quarterly','yearly') DEFAULT 'monthly',
  `weight` decimal(5,2) DEFAULT 1.00,
  `formula` text,
  `data_source` varchar(255) DEFAULT NULL,
  `responsible_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `department_id` (`department_id`),
  KEY `position_id` (`position_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create kpi_data_entries table (if not exists)
CREATE TABLE IF NOT EXISTS `wp_kpi_data_entries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kpi_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `actual_value` decimal(15,2) NOT NULL,
  `target_value` decimal(15,2) DEFAULT NULL,
  `achievement_percentage` decimal(5,2) DEFAULT NULL,
  `notes` text,
  `evidence_files` text,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `submitted_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejection_reason` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kpi_id` (`kpi_id`),
  KEY `user_id` (`user_id`),
  KEY `period_start` (`period_start`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create kpi_approvals table (if not exists)
CREATE TABLE IF NOT EXISTS `wp_kpi_approvals` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `data_entry_id` bigint(20) UNSIGNED NOT NULL,
  `approver_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `comments` text,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `data_entry_id` (`data_entry_id`),
  KEY `approver_id` (`approver_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Verify tables created
SELECT
  TABLE_NAME,
  TABLE_ROWS,
  CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE 'wp_kpi_%'
ORDER BY TABLE_NAME;

-- ============================================
-- SELESAI!
-- ============================================
-- Hasil query terakhir harus menampilkan 8 tabel:
-- - wp_kpi_approvals
-- - wp_kpi_data_entries
-- - wp_kpi_departments
-- - wp_kpi_kpis
-- - wp_kpi_notifications
-- - wp_kpi_positions
-- - wp_kpi_settings
-- - wp_kpi_users
-- ============================================
