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

 Date: 01/06/2021 19:48:23
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tmp_user_action_logs
-- ----------------------------
DROP TABLE IF EXISTS `tmp_user_action_logs`;
CREATE TABLE `tmp_user_action_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL COMMENT '产品ID',
  `open_id` varchar(64) NOT NULL COMMENT '平台用户ID',
  `action_time` datetime NOT NULL COMMENT '时间',
  `type` varchar(50) NOT NULL COMMENT '行为类型',
  `cp_channel_id` varchar(50) NOT NULL DEFAULT '' COMMENT '书城渠道ID',
  `request_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告商的请求ID',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'ip',
  `extend` text NOT NULL COMMENT '扩展信息',
  `data` text NOT NULL COMMENT '数据',
  `status` varchar(50) NOT NULL COMMENT '状态',
  `action_id` varchar(255) NOT NULL DEFAULT '' COMMENT '行为ID',
  `matcher` varchar(50) NOT NULL DEFAULT '' COMMENT '归因方',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '数据来源',
  `fail_data` text COMMENT '失败数据',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`open_id`,`product_id`,`type`,`cp_channel_id`,`action_id`) USING BTREE,
  KEY `type` (`action_time`,`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='行为日志表';

SET FOREIGN_KEY_CHECKS = 1;
