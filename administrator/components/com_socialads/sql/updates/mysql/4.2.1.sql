CREATE TABLE IF NOT EXISTS `#__ad_third_party_enrollment` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`created_by` INT(11)  NOT NULL DEFAULT 0,
`business_name` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'Name of the third party business',
`description` TEXT DEFAULT NULL COMMENT 'Third party enrollment information',
`map_zoom_size` INT(11)  NOT NULL DEFAULT 10,
`state` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT 'Users state',
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modify_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ad_third_party_locations` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`third_party_id` INT(11)  NOT NULL DEFAULT 0,
`location` VARCHAR(100)  NOT NULL DEFAULT '' COMMENT 'Third party lat and lng address',
`radius` float(10,2) NOT NULL  DEFAULT 0,
`city` VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'City of third party',
`region` VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'State of third party',
`country` VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'Country of third party',
`count` INT(11)  NOT NULL DEFAULT 0 COMMENT 'Number of people present in given area',
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modify_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ad_map_target` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ad_id` INT(11)  NOT NULL DEFAULT 0 COMMENT 'FK to #__ad_data',
`location_on` TINYINT(11)  NOT NULL DEFAULT 0 COMMENT '0 for map and 1 for select box',
`count` INT(11)  NOT NULL DEFAULT 0 COMMENT 'Number of people present in given area',
`state` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT 'Users state',
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modify_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ad_map_target_locations` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ad_map_target_id` INT(11)  NOT NULL DEFAULT 0 COMMENT 'FK to #__ad_data',
`location` VARCHAR(100)  NOT NULL DEFAULT '' COMMENT 'Ad  lat and lng address',
`population` INT(11)  NOT NULL DEFAULT 0 COMMENT 'Number of people present in given area',
`radius` float(10,2) NOT NULL  DEFAULT 0,
`city` VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'City of Ad ',
`region` VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'State of Ad ',
`country` VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'Country of Ad ',
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modify_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__third_party_ad_displayed` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`third_party_id` INT(11)  NOT NULL DEFAULT 0 COMMENT 'FK to #__ad_third_party_enrollment',
`ad_ids` text DEFAULT NULL,
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modify_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__ad_stats_third_parties` (
-- `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
-- `ad_id` INT(11)  NOT NULL DEFAULT 0 COMMENT 'FK to #__ad_data',
-- `third_party_id` INT(11)  NOT NULL DEFAULT 0 COMMENT 'FK to #__ad_third_party_enrollment',
-- `display_type` TINYINT(4)  NOT NULL DEFAULT 0 COMMENT 'Impression - 0 or Click - 1',
-- `time` TIMESTAMP NOT NULL COMMENT 'Time on which click or impression is done',
-- `ip_address` VARCHAR(100)  NOT NULL DEFAULT '' COMMENT 'IP address of a machine from where click or impression is done',
-- PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `#__ad_archive_stats` add column `third_party_id` INT(11)  NOT NULL DEFAULT 0 COMMENT 'FK to #__ad_third_party_enrollment';
ALTER TABLE `#__ad_stats` add column `third_party_id` INT(11)  NOT NULL DEFAULT 0 COMMENT 'FK to #__ad_third_party_enrollment';
ALTER TABLE `#__ad_campaign` add column `start_date` DATE DEFAULT NULL;
ALTER TABLE `#__ad_campaign` add column `end_date` DATE DEFAULT NULL;
ALTER TABLE `#__ad_campaign` add column `total_budget` INT(11)  NOT NULL DEFAULT 0 COMMENT 'Total budget assigned for campaign';
ALTER TABLE `#__ad_zone` add column  `use_image_ratio` TINYINT(1)  NOT NULL DEFAULT 0;
ALTER TABLE `#__ad_zone` add column `img_width_ratio` INT(11)  NOT NULL DEFAULT 0 COMMENT 'Width ratio of ad image';
ALTER TABLE `#__ad_zone` add column `img_height_ratio` INT(11)  NOT NULL DEFAULT 0 COMMENT 'Height ratio of ad image';
ALTER TABLE `#__ad_zone` add column `is_responsive` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT 'Used for display ads in third party with non JS device';

ALTER TABLE `#__ad_map_target_locations`
ADD COLUMN `third_party_id` INT NULL AFTER `ad_map_target_id`;

