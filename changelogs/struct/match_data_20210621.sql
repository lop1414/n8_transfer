ALTER TABLE `n8_transfer`.`match_data`
ADD INDEX `open_id`(`open_id`) USING BTREE,
ADD INDEX `product_id`(`product_id`) USING BTREE;
