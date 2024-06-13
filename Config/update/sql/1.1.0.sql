SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE sub_order add column amount_already_paid float not null default 0;

SET FOREIGN_KEY_CHECKS = 1;
