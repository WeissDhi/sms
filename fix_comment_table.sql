-- Rename column crated_at to created_at in comment table
ALTER TABLE `comment` CHANGE `crated_at` `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP; 