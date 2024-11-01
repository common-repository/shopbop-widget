CREATE TABLE IF NOT EXISTS `%PREFIX%shopbop_cache` (
  `id_wp_shopbop_cache` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `date` varchar(32),
  `type` enum('marketing','pane1','pane2','pane3','promotion','category-keyword-links') DEFAULT NULL,
  `last_update` datetime DEFAULT '1970-01-01 00:00:00',
  `post_id` INT NOT NULL,
  `update_requested` datetime DEFAULT NULL,
  PRIMARY KEY (`id_wp_shopbop_cache`),
  UNIQUE KEY (`path`, `type`),
  INDEX `update_requested` (`update_requested` ASC)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
|
CREATE TABLE IF NOT EXISTS `%PREFIX%shopbop_category_assignments` (
  `post_id` INT NOT NULL,
  `selector_id` INT NOT NULL,
  `category_path` VARCHAR(255) NULL,
  `use_default` INT(1) DEFAULT 1,
  `is_random` INT(1) DEFAULT 1,
  `is_justarrived` INT(1) DEFAULT 0,
  `lastUpdated` datetime NOT NULL,
  PRIMARY KEY (`post_id`, `selector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
|
INSERT IGNORE INTO `%PREFIX%shopbop_category_assignments` (post_id, selector_id, use_default, is_random, is_justarrived, lastUpdated) VALUES (-1, 1, 0, 0, 1, NOW()), (-1 , 2, 0, 1, 0, NOW()), (-1, 3, 0, 1, 0, NOW());