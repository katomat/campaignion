<?php
/**
 * @file
 */

/**
 * Menu/form callback: settings form.
 */
function recent_supporters_settings_form($form, &$form_state) {
  $defaults = array(
    'visible_count'  => 6,
    'cycle_toggle'   => 0,
    'country_toggle' => 1,
    'name_display'   => CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT,
  );
  $saved_settings = variable_get('campaignion_recent_supporters_settings', $defaults);

  $form['#tree'] = TRUE;

  $form['general_settings'] = array(
    '#title' => t('General settings for recent supporter block.'),
    '#type'  => 'fieldset',
  );

  $form['general_settings']['name_display'] = array(
    '#title'         => t('Supporter name display'),
    '#description'   => t('Configure the information displayed about the supporters.'),
    '#type'          => 'select',
    '#options'       => _campaignion_recent_supporters_name_display_options(),
    '#attributes'    => array(),
    '#default_value' => $saved_settings['name_display'],
  );
  $form['general_settings']['visible_count'] = array(
    '#title'         => t('Number of max. visible supporters.'),
    '#description'   => t('Number of max. visible supporters.'),
    '#type'          => 'textfield',
    '#size'          => 12,
    '#maxlength'     => 10,
    '#attributes'    => array(),
    '#default_value' => $saved_settings['visible_count'],
  );
  $form['general_settings']['cycle_toggle'] = array(
    '#title'         => t('Cycle supporters.'),
    '#description'   => t('Cycle supporters.'),
    '#type'          => 'checkbox',
    '#default_value' => $saved_settings['cycle_toggle'],
  );
  $form['general_settings']['country_toggle'] = array(
    '#title'         => t('Show country of supporters.'),
    '#description'   => t('Show country of supporters.'),
    '#type'          => 'checkbox',
    '#default_value' => $saved_settings['country_toggle'],
  );
  $lang_code = NULL;
  if (module_exists('variable_realm') && module_exists('i18n_variable')) {
    global $language;
    $keys      = variable_realm_current_keys();
    $lang_code = $language->language;
    if (isset($keys['language'])) {
      $lang_code = $keys['language'];
    }
  }
  $type_settings = variable_get('campaignion_recent_supporters_settings_types', _campaignion_recent_supporters_get_types_defaults($lang_code));
  $form['campaignion_recent_supporters_settings_types'] = array(
    '#title' => t('Choose types of actions and texts.'),
    '#type'  => 'fieldset',
  );
  $available_types = array_intersect_key(node_type_get_types(), Drupal\campaignion\Action\TypeBase::types());
  foreach ($available_types as $type => $type_config) {
    $type_id = drupal_html_id('campaignion-recent-supporters-type-' . str_replace('_', '-', $type));
    $form['campaignion_recent_supporters_settings_types'][$type]['enabled'] = array(
      '#title'         => $type_config->name,
      '#type'          => 'checkbox',
      '#attributes'    => array('id' => $type_id),
      '#default_value' => $type_settings[$type]['enabled'],
    );
    $form['campaignion_recent_supporters_settings_types'][$type]['text'] = array(
      '#title'         => t('Text to display for %type actions.', array('%type' => $type_config->name)),
      '#type'          => 'textfield',
      '#default_value' => $type_settings[$type]['text'],
      '#states'        => array('visible' => array('#' . $type_id => array('checked' => TRUE))),
    );
  }
  $token_legend = array(
    'items' => array(
      '!action_title - The page title of the action page',
      '!supporter_name - Firstname and lastname of the supporter that just took action',
    ),
  );
  $form['campaignion_recent_supporters_settings_types']['token_legend'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Available tokens:'),
    '#collapsible' => TRUE,
    '#collapsed'   => TRUE,
    '#children'    => '<div>' . theme('item_list', $token_legend) . '</div>',
    '#attributes'  => array('class' => array('collapsible', 'collapsed')),
  );
  if (module_exists('variable_realm') && module_exists('i18n_variable')) {
    $form['#realm_variables']['language']['campaignion_recent_supporters_settings_types'] = 'campaignion_recent_supporters_settings_types';
    //copied from variable_realm.variable.inc
    form_load_include($form_state, 'form.inc', 'variable_realm');
    _variable_realm_variable_settings_form_switcher($form);
  }
  $form = system_settings_form($form);
  $form['#submit'][] = 'campaignion_recent_supporters_settings_form_submit';
  return $form;
}

function campaignion_recent_supporters_settings_form_submit($form, &$form_state) {
  $result = array(
    'visible_count'  => $form_state['values']['general_settings']['visible_count'],
    'cycle_toggle'   => $form_state['values']['general_settings']['cycle_toggle'],
    'country_toggle' => $form_state['values']['general_settings']['country_toggle'],
    'name_display'   => $form_state['values']['general_settings']['name_display'],
  );
  variable_set('campaignion_recent_supporters_settings', $result);
}
