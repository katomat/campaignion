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

/**
 * as our bootstrap does not load this function definition (but we do need it
 * in the .module file) we
 */
function module_exists($name) {
  return FALSE;
}

require_once DRUPAL_ROOT . '/includes/common.inc';
require_once dirname(__FILE__) . '/campaignion_recent_supporters.module';

if (!isset($_GET['nid'])) {
  campaignion_recent_supporters_send_empty_json();
  exit;
}

campaignion_recent_supporters_json((int) $_GET['nid']);
