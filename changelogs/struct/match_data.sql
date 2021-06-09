/*
 Navicat Premium Data Transfer

 Source Server         : 虚拟机 192.168.10.10
 Source Server Type    : MySQL
 Source Server Version : 50731
 Source Host           : localhost:3306
 Source Schema         : n8_transfer

 Target Server Type    : MySQL
 Target Server Version : 50731
 File Encoding         : 65001

 Date: 09/06/2021 09:42:26
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for match_data
-- ----------------------------
DROP TABLE IF EXISTS `match_data`;
CREATE TABLE `match_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL COMMENT '产品ID',
  `open_id` varchar(64) NOT NULL COMMENT '书城用户ID',
  `cp_channel_id` varchar(50) NOT NULL DEFAULT '' COMMENT '书城渠道ID',
  `adv_alias` varchar(50) NOT NULL COMMENT '广告商',
  `type` varchar(50) NOT NULL COMMENT '类型',
  `request_id` varchar(100) NOT NULL DEFAULT '',
  `data` text NOT NULL COMMENT '扩展字段',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新事件',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='匹配数据';

SET FOREIGN_KEY_CHECKS = 1;
