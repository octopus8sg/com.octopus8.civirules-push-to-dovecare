CREATE TABLE IF NOT EXISTS civirule_trigger (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Trigger ID',
  `name` varchar(80) DEFAULT NULL ,
  `label` varchar(128) DEFAULT NULL ,
  `object_name` varchar(45) DEFAULT NULL ,
  `op` varchar(45) DEFAULT NULL ,
  `cron` int DEFAULT 0 ,
  `class_name` varchar(128) DEFAULT NULL ,
  `is_active` int NOT NULL  DEFAULT 1 ,
  `created_date` date DEFAULT NULL ,
  `created_user_id` int DEFAULT NULL ,
  `modified_date` date DEFAULT NULL ,
  `modified_user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
)  ENGINE=Innodb  ;
