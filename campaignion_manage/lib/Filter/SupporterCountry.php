<?php

namespace Drupal\campaignion_manage\Filter;

class SupporterCountry extends Base implements FilterInterface {
  protected $query;

  public function __construct(\SelectQueryInterface $query) {
    $this->query = $query;
  }

  protected function getOptions() {
    $result = db_query("SELECT field_address_country FROM field_data_field_address GROUP BY field_address_country");

    if ($in_use = $result->fetchCol()) {
      $in_use = array_flip($in_use);
      return array_intersect_key(country_get_list(), $in_use);
    }
    return array();
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['country'] = array(
      '#type'          => 'select',
      '#title'         => t('Country'),
      '#options'       => $this->getOptions(),
      '#default_value' => isset($values) ? $values : NULL,
    );
  }

  public function title() { return t('Is from'); }

  public function apply($query, array $values) {
    $inner = db_select('field_data_field_address', 'ctr')
      ->fields('ctr', array('entity_id'))
      ->condition('ctr.entity_type', 'redhen_contact')
      ->condition('ctr.field_address_country', $values['country']);
    $query->condition('r.contact_id', $inner, 'IN');
  }

  public function isApplicable($current) {
    return empty($current) && count($this->getOptions()) > 0;
  }

  public function defaults() {
    $options = $this->getOptions();
    return array('country' => key($options));
  }
}
