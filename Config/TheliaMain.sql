
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- sub_order
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `sub_order`;

CREATE TABLE `sub_order`
(
    `sub_order_id` INTEGER(50) NOT NULL,
    `parent_order_id` INTEGER NOT NULL,
    `token` VARCHAR(255),
    `authorized_payment_option` JSON,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`sub_order_id`),
    UNIQUE INDEX `token_UNIQUE` (`token`),
    INDEX `fi_sub_order_order_id` (`parent_order_id`),
    CONSTRAINT `fk_sub_order_id`
        FOREIGN KEY (`sub_order_id`)
        REFERENCES `order` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_sub_order_order_id`
        FOREIGN KEY (`parent_order_id`)
        REFERENCES `order` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
