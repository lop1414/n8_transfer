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

 Date: 22/02/2021 17:56:54
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tmp_user_action_logs
-- ----------------------------
DROP TABLE IF EXISTS `tmp_user_action_logs`;
CREATE TABLE `tmp_user_action_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL COMMENT '产品ID',
  `open_id` varchar(64) NOT NULL COMMENT '平台用户ID',
  `action_time` datetime NOT NULL COMMENT '时间',
  `type` varchar(50) NOT NULL COMMENT '行为类型',
  `data` text NOT NULL COMMENT '数据',
  `status` varchar(50) NOT NULL COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`product_id`,`action_time`,`open_id`,`type`) USING BTREE,
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='行为日志表';

SET FOREIGN_KEY_CHECKS = 1;
