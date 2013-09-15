DROP TABLE IF EXISTS `pac_roles`;
CREATE TABLE `pac_roles` (
  `id`    BIGINT(20) UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(64)          NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Roles';

DROP TABLE IF EXISTS `pac_role_includes`;
CREATE TABLE `pac_role_includes` (
  `role_id`          BIGINT(20) UNSIGNED  NOT NULL,
  `included_role_id` BIGINT(20) UNSIGNED  NOT NULL,
  PRIMARY KEY (`role_id`, `included_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Role Includes Map';

DROP TABLE IF EXISTS `pac_users_roles`;
CREATE TABLE `pac_users_roles` (
  `user_id` VARCHAR(96)  NOT NULL,
  `role_id` BIGINT(20)   NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User Role Map';

DROP TABLE IF EXISTS `pac_roles_destinations_values`;
CREATE TABLE `pac_roles_destinations_values` (
  `destination` VARCHAR(96)  NOT NULL,
  `token`       VARCHAR(96)  NOT NULL,
  `role_id`     BIGINT(20)   NOT NULL,
  `value`       VARCHAR(96)  NOT NULL,
  PRIMARY KEY (`token`,`role_id`,`value`,`destination`),
  KEY `finder` (`destination`,`token`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Permission Values';
