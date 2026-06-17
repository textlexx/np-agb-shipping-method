CREATE TABLE IF NOT EXISTS `wp_for_example` (
  `id` BIGINT(20) UNSIGNED NOT NULL,
  `const_id` BIGINT(20) UNSIGNED NOT NULL,
  `filter_category_id` INT(11) DEFAULT 0,
  `from_wc_table_attr_taxonomy_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(254) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY(`const_id`),
  UNIQUE KEY(`filter_category_id`, `from_wc_table_attr_taxonomy_id`),
  INDEX `xid` (`id`),
  INDEX `xconst_id` (`const_id`),
  INDEX `xfilter_category_id` (`filter_category_id`),
  INDEX `xfrom_wc_table_attr_taxonomy_id` (`from_wc_table_attr_taxonomy_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;