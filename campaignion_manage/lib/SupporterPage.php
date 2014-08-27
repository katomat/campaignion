<?php

namespace Drupal\campaignion_manage;

class SupporterPage extends Page {
  public function __construct($query) {
    $this->baseQuery = $query;

    $filters['name'] = new Filter\SupporterName();
    if (module_exists('campaignion_supporter_tags')) {
      $filters['tags'] = new Filter\SupporterTag($this->baseQuery->query());
    }
    $filters['country']  = new Filter\SupporterCountry($this->baseQuery->query());
    $filters['activity'] = new Filter\SupporterActivity($this->baseQuery->query());
    $default[] = array('type' => 'name', 'removable' => FALSE);
    $this->filterForm = new FilterForm('supporter', $filters, $default);

    $bulkOps = array();
    if (module_exists('campaignion_supporter_tags')) {
      $bulkOps['tag']   = new BulkOp\SupporterTag(TRUE);
      $bulkOps['untag'] = new BulkOp\SupporterTag(FALSE);
    }
    $bulkOps['export'] = new BulkOp\SupporterExport();
    $this->listing = new SupporterListing(20);
    $this->bulkOpForm = new BulkOpForm($bulkOps);
  }

  public function form($form, &$form_state) {
    $form = parent::form($form, $form_state);
    $form['filter']['submit']['#attributes']['class'][] = 'ctools-auto-submit-exclude';
    if (!isset($form_state['values']['filter']['live_filters'])) {
      $form_state['values']['filter']['live_filters'] = variable_get('campaignion_manage_live_filters_default', TRUE);
    }
    $live_filters = array(
      '#type' => 'checkbox',
      '#title' => t('Update the listing right away'),
      '#attributes' => array(
        'class' => array('toggle-live-filters', 'ctools-auto-submit-exclude'),
      ),
      '#weight' => 99,
    );
    if($form_state['values']['filter']['live_filters']) {
      $live_filters['#attributes']['checked'] = 'checked';
    }
    $form['filter']['live_filters'] = $live_filters;
    return $form;

  }
}
