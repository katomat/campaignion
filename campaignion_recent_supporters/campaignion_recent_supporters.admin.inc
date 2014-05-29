<?php
/**
 * @file
 */

/**
 * Menu/form callback: settings form.
 */
function recent_supporters_settings_form($form, &$form_state) {
  $saved_settings  = variable_get('recent_supporters_all_actions_settings', array());
  $defaults = array(
    'visible_count'  => 6,
    'cycle_toggle'   => 0,
    'country_toggle' => 1,
    'name_display' => CAMPAIGNION_RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT,
    'types'          => array(),
  );
  $saved_settings = $saved_settings + $defaults;

  $form['general_settings'] = array(
    '#title'       => t('General settings for recent supporter block.'),
    '#type'        => 'fieldset',
  );

  $form['general_settings']['name_display'] = array(
    '#title' => t('Supporter name display'),
    '#description' => t('Configure the information displayed about the supporters.'),
    '#type' => 'select',
    '#options' => _campaignion_recent_supporters_name_display_options(),
    '#attributes' => array(),
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

  $form['content_types'] = array(
    '#title'       => t('Choose types of actions and text.'),
    '#type'        => 'fieldset',
  );

  $available_types = array_intersect_key(node_type_get_types(), Drupal\campaignion\Action\TypeBase::types());
  foreach ($available_types as $type => $type_config) {
    $type_id = drupal_html_id('recent-supporters-type-' . str_replace('_', '-', $type));
    $form['content_types'][$type] = array(
      '#title'         => $type_config->name,
      '#type'          => 'checkbox',
      '#attributes'    => array('id' => $type_id),
      '#default_value' => empty($saved_settings['types'][$type]['enabled']) ? 0 : 1,
    );
    $form['content_types'][$type . '_text'] = array(
      '#title'         => t('Text to display for %type actions.', array('%type' => $type_config->name)),
      '#type'          => 'textfield',
      '#default_value' => isset($saved_settings['types'][$type]['text']) ? $saved_settings['types'][$type]['text']: '',
      '#states'        => array('visible' => array('#' . $type_id => array('checked' => TRUE))),
    );
  }


  $form['actions'] = array(
    '#type'   => 'actions',
    '#weight' => 3,
  );
  $form['actions']['submit'] = array(
    '#type'  => 'submit',
    '#value' => t('Save settings'),
  );

  return $form;
}

function recent_supporters_settings_form_submit($form, &$form_state) {
  $result = array(
    'name_display'   => $form_state['values']['name_display'],
    'visible_count'  => $form_state['values']['visible_count'],
    'cycle_toggle'   => $form_state['values']['cycle_toggle'],
    'country_toggle' => $form_state['values']['country_toggle'],
    'types'          => array(),
  );

  foreach (array_keys(Drupal\campaignion\Action\TypeBase::types()) as $type) {
    $result['types'][$type]['enabled'] = $form_state['values'][$type];
    $result['types'][$type]['text']    = $form_state['values'][$type . '_text'];
  }

  variable_set('recent_supporters_all_actions_settings', $result);

  drupal_set_message(t('Saved recent supporter settings.'));
}