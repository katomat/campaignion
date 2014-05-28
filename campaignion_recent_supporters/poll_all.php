<?php

/**
 * @file
 *
 * This is file does a minimal drupal bootstrap and returns the current
 * recent supporter data.
 */

function campaignion_recent_supporters_bootstrap_inc() {
  $dir = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME']);
  while ($dir != '/') {
    $bootstrap = $dir . '/includes/bootstrap.inc';
    if (file_exists($bootstrap)) {
      define('DRUPAL_ROOT', $dir);
      return $bootstrap;
    }
    $dir = dirname($dir);
  }
}

if ($bootstrap = campaignion_recent_supporters_bootstrap_inc()) {
  require_once $bootstrap;
}

_drupal_bootstrap_configuration();
_drupal_bootstrap_database();

if (!isset($_GET['types'])) {
  exit;
}

require_once DRUPAL_ROOT . '/includes/common.inc';
require_once dirname(__FILE__) . '/campaignion_recent_supporters.module';

campaignion_recent_supporters_all_action_json($_GET['types']);
