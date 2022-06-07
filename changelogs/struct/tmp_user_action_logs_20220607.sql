ALTER TABLE `n8_transfer`.`tmp_user_action_logs`
DROP INDEX `user`,
ADD UNIQUE INDEX `user`(`open_id`, `product_id`, `type`, `cp_channel_id`, `action_id`, `ip`) USING BTREE;
