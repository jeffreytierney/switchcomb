CREATE TABLE `assets` (
  `asset_id` INT NOT NULL AUTO_INCREMENT,
  `asset_user_id` INT NOT NULL,
  `asset_hash` VARCHAR(50) NOT NULL,
  `asset_createdate` DATETIME NOT NULL,
  `asset_type` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`asset_id`),
  INDEX IDX_ASSET_HASH(`asset_hash`),
  INDEX IDX_ASSET_USER(`asset_user_id`),
  CONSTRAINT `FK_ASSET_USER` FOREIGN KEY `FK_ASSET_USER` (`asset_user_id`)
    REFERENCES `users` (`user_id`)
)
CHARACTER SET utf8;

ALTER TABLE `assets` ENGINE = InnoDB;

ALTER TABLE `assets` ADD COLUMN `asset_mime_type` VARCHAR(35) NOT NULL AFTER `asset_type`;


ALTER TABLE `assets` ADD COLUMN `asset_orig_path` VARCHAR(255) AFTER `asset_mime_type`,
 ADD COLUMN `asset_size` INT UNSIGNED NOT NULL AFTER `asset_orig_path`,
 ADD COLUMN `asset_folder` VARCHAR(20) AFTER `asset_size`;
