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

 Date: 04/06/2021 14:32:53
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for kuai_shou_clicks
-- ----------------------------
DROP TABLE IF EXISTS `kuai_shou_clicks`;
CREATE TABLE `kuai_shou_clicks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `click_source` varchar(50) NOT NULL DEFAULT '' COMMENT '来源',
  `click_at` timestamp NULL DEFAULT NULL COMMENT '点击时间',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道ID',
  `extends` text NOT NULL COMMENT '扩展字段',
  `status` varchar(50) NOT NULL COMMENT '上报状态',
  `fail_data` text COMMENT '失败数据',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新事件',
  PRIMARY KEY (`id`),
  KEY `click_at` (`click_at`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快手点击表';

SET FOREIGN_KEY_CHECKS = 1;
