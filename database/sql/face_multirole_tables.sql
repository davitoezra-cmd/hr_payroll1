-- Multi-role Face Recognition tables for HR Payroll
-- Safe companion for environments initialized from dump-hr.sql.
-- Existing employee_face_templates is intentionally preserved.

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `user_face_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `embedding` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insightface',
  `engine_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding_dimension` smallint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_face_templates_user_id_unique` (`user_id`),
  CONSTRAINT `user_face_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supervisor_face_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint unsigned NOT NULL,
  `embedding` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insightface',
  `engine_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding_dimension` smallint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supervisor_face_templates_supervisor_id_unique` (`supervisor_id`),
  CONSTRAINT `supervisor_face_templates_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `finance_face_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finance_id` bigint unsigned NOT NULL,
  `embedding` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insightface',
  `engine_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding_dimension` smallint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `finance_face_templates_finance_id_unique` (`finance_id`),
  CONSTRAINT `finance_face_templates_finance_id_foreign` FOREIGN KEY (`finance_id`) REFERENCES `finances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT
  '2026_08_12_180000_create_role_face_templates_tables',
  COALESCE((SELECT MAX(`batch`) FROM `migrations`), 0) + 1
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_12_180000_create_role_face_templates_tables'
);

SET FOREIGN_KEY_CHECKS=1;
