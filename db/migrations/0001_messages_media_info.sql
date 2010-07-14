ALTER TABLE `sc_mb`.`messages` ADD COLUMN `msg_type` VARCHAR(20) NOT NULL DEFAULT 'text' AFTER `msg_thread`,
 ADD COLUMN `msg_media` TEXT AFTER `msg_type`,
 ADD COLUMN `msg_media_caption` VARCHAR(255) AFTER `msg_media`;


ALTER TABLE `sc_mb`.`messages` MODIFY COLUMN `msg_text` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;
