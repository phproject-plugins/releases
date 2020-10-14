CREATE TABLE `release`(
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `created_date` DATETIME NOT NULL,
  `target_date` DATE,
  `closed_date` DATETIME,
  PRIMARY KEY (`id`)
) ENGINE=INNODB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `release_issue`(
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `release_id` INT UNSIGNED NOT NULL,
  `issue_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `release_issue_issue` (`issue_id`),
  KEY `release_issue_release` (`release_id`),
  CONSTRAINT `release_issue_release` FOREIGN KEY (`release_id`) REFERENCES `release`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `release_issue_issue` FOREIGN KEY (`issue_id`) REFERENCES `issue`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE VIEW `release_detail` AS (
  SELECT r.*, COUNT(DISTINCT i.`id`) AS `issue_count`, COUNT(i.`closed_date`) AS `closed_count`
  FROM `release` r
  LEFT JOIN `release_issue` ri ON ri.`release_id` = r.`id`
  LEFT JOIN `issue` i ON ri.`issue_id` = i.`id`
  GROUP BY r.`id`
);

CREATE VIEW `release_issue_detail` AS (
  SELECT i.*, r.`release_id`
  FROM `release_issue` r
  JOIN `issue_detail` i ON r.`issue_id` = i.`id`
);
