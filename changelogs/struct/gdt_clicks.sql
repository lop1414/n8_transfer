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

 Date: 30/04/2021 17:42:45
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for gdt_clicks
-- ----------------------------
DROP TABLE IF EXISTS `gdt_clicks`;
CREATE TABLE `gdt_clicks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `click_source` varchar(50) NOT NULL DEFAULT '' COMMENT '来源',
  `click_at` timestamp NULL DEFAULT NULL COMMENT '点击时间',
  `request_id` varchar(100) NOT NULL,
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道ID',
  `extends` text NOT NULL COMMENT '扩展字段',
  `status` varchar(50) NOT NULL COMMENT '上报状态',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新事件',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`) USING BTREE,
  KEY `click_at` (`click_at`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='GDT点击表';

SET FOREIGN_KEY_CHECKS = 1;
