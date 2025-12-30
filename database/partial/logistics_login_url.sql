-- Add logistics login URL to data_settings table
INSERT INTO `data_settings` (`key`, `value`, `type`, `created_at`, `updated_at`) 
VALUES ('logistics_login_url', 'logistics', 'login_logistics', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = 'logistics', `updated_at` = NOW();

