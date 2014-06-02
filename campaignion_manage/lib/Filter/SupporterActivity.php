<?php

namespace Drupal\campaignion_manage\Filter;

class SupporterActivity extends Base implements FilterInterface {
  protected $query;

  public function __construct(\SelectQueryInterface $query) {
    $this->query = $query;
  }

  protected function actionsWithActivity() {
    $query = db_select('campaignion_activity_webform', 'wact');
    $query->innerJoin('node', 'n', "wact.nid = n.nid");
    $query->fields('n', array('nid', 'type', 'title'));
    $query->where('n.tnid = 0 OR n.tnid = n.nid');

    $actions = array();
    foreach ($query->execute()->fetchAllAssoc('nid') as $nid => $action) {
      $actions[$action->type][$nid] = $action->title;
    }

    return $actions;
  }

  protected function typesInUse() {
    $available_activities = array(
      'any_activity'          => t('Any type'),
      'redhen_contact_create' => t('Contact created'),
      'webform_submission'    => t('Form submission'),
      'webform_payment'       => t('Online payment'),
    );

    $activities_in_use = array('any_activity' => t('Any activity'));

    $query = db_select('campaignion_activity', 'act');
    $query->condition('act.type', array_keys($available_activities), 'IN');
    $query->fields('act', array('type'));
    $query->groupBy('act.type');

    $activities_in_use += $query->execute()->fetchAllKeyed(0,0);
    return array_intersect_key($available_activities, $activities_in_use);
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $frequency_id  = drupal_html_id('activity-frequency');
    $date_range_id = drupal_html_id('activity-date-range');
    $activity_type_id = drupal_html_id('activity-type');
    $form['activity'] = array(
      '#type'          => 'select',
      '#id'            => $activity_type_id,
      '#options'       => $this->typesInUse(),
      '#default_value' => isset($values['activity']) ? $values['activity'] : NULL,
    );
    $action_types = array(
      'any'           => t('Any type of action'),
      'donation'      => t('Donation'),
      'email_protest' => t('Email Protest'),
      'petition'      => t('Petition'),
      'webform'       => t('Flexible Form'),
    );
    $action_type_id = drupal_html_id('action-type');
    $form['action_type'] = array(
      '#type' => 'select',
      '#id' => $action_type_id,
      '#options' => $action_types,
      '#states'  => array('visible' => array('#' . $activity_type_id => array('value' => 'webform_submission'))),
      '#default_value' => isset($values['action_type']) ? $values['action_type'] : 'any',
    );
    $actions = $this->actionsWithActivity();
    foreach ($action_types as $type => $type_name) {
      if (!empty($actions[$type])) {
        $form['action_' . $type] = array(
          '#type'          => 'select',
          '#options'       => array('no_specific' => t('No specific action')) + $actions[$type],
          '#states'        => array('visible' => array('#' . $action_type_id => array('value' => $type), '#' . $activity_type_id => array('value' => 'webform_submission'))),
          '#default_value' => isset($values['action_' . $type]) ? $values['action_' . $type] : NULL,
        );
      }
    }
    $form['date_range'] = array(
      '#type'          => 'select',
      '#attributes'    => array('id' => $date_range_id),
      '#options'       => array('all' => t('Any time'), 'range' => t('Date range'), 'to' => t('to'), 'from' => t('from')),
      '#default_value' => isset($values['date_range']) ? $values['date_range'] : NULL,
    );
    $form['date_from'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('from'),
      '#description'   => t('Specify a date in the format YYYY/MM/DD'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'from'))),
      '#default_value' => isset($values['date_from']) ? $values['date_from'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
    $form['date_to'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('to'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'to'))),
      '#default_value' => isset($values['date_to']) ? $values['date_to'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
    $form['date_range_from'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('from'),
      '#description'   => t('Specify a date in the format YYYY/MM/DD'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'range'))),
      '#default_value' => isset($values['date_from']) ? $values['date_from'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
    $form['date_range_to'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('to'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'range'))),
      '#default_value' => isset($values['date_to']) ? $values['date_to'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
  }

  public function title() { return t('Activity'); }

  public function apply($query, array $values) {
    $inner = db_select('campaignion_activity', 'act');
    $inner->fields('act', array('contact_id'));
    // "RedHen contact was edited" activities are never shown
    $inner->condition('act.type', 'redhen_contact_edit', '!=');

    if ($values['activity'] != 'any_activity') {
      $inner->condition('act.type', $values['activity']);
    }
    if ($values['activity'] == 'webform_submission' && $values['action_type'] != 'any') {
      $type = $values['action_type'];
      $inner->innerJoin('campaignion_activity_webform', 'wact', "act.activity_id = wact.activity_id");
      $inner->innerJoin('node', 'n', "wact.nid = n.nid");
      if (!empty($values["action_$type"]) && $values["action_$type"] !== 'no_specific') {
        $inner->where('n.nid = :nid OR n.tnid = :nid', array(':nid' => $values['action_' . $type]));
      }
      else {
        $inner->condition('n.type', $values['action_type']);
      }
    }

    switch ($values['date_range']) {
      case 'range':
        $date_range = array(strtotime($values['date_range_from']), strtotime($values['date_range_to']));
        $inner->condition('act.created', $date_range, 'BETWEEN');
        break;
      case 'to':
        $to = strtotime($values['date_to']);
        $inner->condition('act.created', $to, '<');
        break;
      case 'from':
        $from  = strtotime($values['date_from']);
        $inner->condition('act.created', $from, '>');
        break;
    }
    $query->condition('r.contact_id', $inner, 'IN');
  }

  public function isApplicable($current) {
    return empty($current) && count($this->typesInUse()) > 1;
  }

  public function defaults() {
    $types = $this->typesInUse();
    return array(
      'frequency'   => 'any',
      'how_many_op' => '=',
      'how_many_nr' => '1',
      'activity'    => key($types),
      'action_type' => 'any',
      'date_range'  => 'all',
      'date_after'  => '',
      'date_before' => '',
    );
  }
}
