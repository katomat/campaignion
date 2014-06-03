<?php

/**
 * @file
 *
 * This is file does a minimal drupal bootstrap and returns the current
 * recent supporter data.
 */

function campaignion_recent_supporters_bootstrap_inc() {
  # set base_url explicitly as SCRIPT_NAME would lead to
  # a 'wrong' base_url for the site
  global $base_url;
  $is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
  $http_protocol = $is_https ? 'https' : 'http';
  $base_root = $http_protocol . '://' . $_SERVER['HTTP_HOST'];
  # base_url gets stripped to it's correct location below
  $base_url = dirname($base_root . $_SERVER['SCRIPT_NAME']);

  $dir = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME']);
  while ($dir != '/') {
    $bootstrap = $dir . '/includes/bootstrap.inc';
    if (file_exists($bootstrap)) {
      define('DRUPAL_ROOT', $dir);
      return $bootstrap;
    }
    $dir = dirname($dir);
    $base_url = dirname($base_url);
  }
}

if ($bootstrap = campaignion_recent_supporters_bootstrap_inc()) {
  require_once $bootstrap;
}

_drupal_bootstrap_configuration();
_drupal_bootstrap_database();

require_once DRUPAL_ROOT . '/includes/common.inc';
require_once dirname(__FILE__) . '/campaignion_recent_supporters.module';

if (!isset($_GET['types'])) {
  campaignion_recent_supporters_send_empty_json();
  exit;
}


campaignion_recent_supporters_all_action_json($_GET['types']);
