<?php

return array (
  'number' => '0001',
  'always_ok' => true,
  'status' => 0,
  'executed' => false,
  'description' => 'Create Table `risk_countries`',
  'requests' => 
  array (
    0 => 'CREATE TABLE `risk_countries` (
  `id` int(11) NOT NULL,
  `subsidiary_id` int(6) NOT NULL,
  `bank_id` int(6) NOT NULL,
  `period` char(6) NOT NULL,
  `bankid` char(25) NOT NULL,
  `update_flag` smallint(3) DEFAULT NULL,
  `r_valid_from` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `r_valid_to` timestamp NULL DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `deleted_by` varchar(50) DEFAULT NULL,
  `country_iso` int(11) NOT NULL,
  `country_name` int(11) NOT NULL,
  `country_risk_weight` smallint(6) NOT NULL,
  `country_regulation` varchar(50) NOT NULL,
  `comment` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
    1 => 'ALTER TABLE `risk_countries`
  ADD PRIMARY KEY (`id`);',
    2 => 'ALTER TABLE `risk_countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;',
  ),
  'logs' => 
  array (
  ),
);