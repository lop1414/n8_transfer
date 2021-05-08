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

 Date: 08/05/2021 17:32:12
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ocean_clicks
-- ----------------------------
DROP TABLE IF EXISTS `ocean_clicks`;
CREATE TABLE `ocean_clicks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `click_source` varchar(50) NOT NULL DEFAULT '' COMMENT '来源',
  `campaign_id` varchar(100) NOT NULL DEFAULT '' COMMENT '广告组id',
  `ad_id` varchar(100) NOT NULL DEFAULT '' COMMENT '计划id',
  `creative_id` varchar(100) NOT NULL DEFAULT '' COMMENT '创意id',
  `request_id` varchar(100) NOT NULL,
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `creative_type` varchar(50) NOT NULL DEFAULT '' COMMENT '创意样式',
  `creative_site` varchar(100) NOT NULL DEFAULT '' COMMENT '广告投放位置',
  `convert_id` varchar(100) NOT NULL DEFAULT '' COMMENT '转化id',
  `muid` varchar(100) NOT NULL DEFAULT '' COMMENT '安卓为IMEI, IOS为IDFA',
  `android_id` varchar(100) NOT NULL DEFAULT '' COMMENT '安卓id',
  `oaid` varchar(100) NOT NULL DEFAULT '' COMMENT 'Android Q及更高版本的设备号',
  `oaid_md5` varchar(64) NOT NULL DEFAULT '' COMMENT 'Android Q及更高版本的设备号的md5摘要',
  `os` varchar(50) NOT NULL DEFAULT '' COMMENT '操作系统平台',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `ua` varchar(1024) NOT NULL DEFAULT '' COMMENT 'user agent',
  `click_at` timestamp NULL DEFAULT NULL COMMENT '点击时间',
  `callback_param` varchar(512) NOT NULL DEFAULT '' COMMENT '回调参数',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '手机型号',
  `union_site` varchar(100) NOT NULL DEFAULT '',
  `caid` varchar(100) NOT NULL DEFAULT '' COMMENT '不同版本的中国广告协会互联网广告标识，CAID1是20201230版，暂无CAID2',
  `link` varchar(512) NOT NULL DEFAULT '' COMMENT '落地页原始url',
  `extends` text NOT NULL COMMENT '扩展字段',
  `status` varchar(50) NOT NULL COMMENT '上报状态',
  `fail_data` text COMMENT '失败数据',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`) USING BTREE,
  KEY `click_at` (`click_at`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量点击表';

SET FOREIGN_KEY_CHECKS = 1;
