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
# lock.inc needed by locale.module
require_once DRUPAL_ROOT . '/includes/lock.inc';
require_once DRUPAL_ROOT . '/modules/locale/locale.module';
require_once dirname(__FILE__) . '/campaignion_recent_supporters.module';

if (!isset($_GET['types'])) {
  campaignion_recent_supporters_send_empty_json();
  exit;
}

global $conf;
// we want to avoid the whole _drupal_bootstrap_variables() so we only
// load what we need (eg. we are not setting any caches)
// loading variables into $conf should suffice as the code which gets
// called only needs variable_get()
// (code from variable_initialize())
$variables = array_map('unserialize', db_query('SELECT name, value FROM {variable}')->fetchAllKeyed());
foreach ($variables as $var_key => $var_value) {
  $conf[$var_key] = $var_value;
}

$lang = NULL;
if (drupal_multilingual()) {
  if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
  }
}

// stub for module_exists called in language_list() which gets called
// by our code. we want to prevent loading module.inc and cache.inc which
// would be needed for module_list() --> so stub it.
// simply returning false does the trick for now.
function module_exists($name) {
  return FALSE;
}

campaignion_recent_supporters_all_action_json($_GET['types'], $lang);
