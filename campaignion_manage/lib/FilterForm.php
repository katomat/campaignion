<?php

namespace Drupal\campaignion_manage;

class FilterForm {
  protected $filters;
  protected $values;
  protected $key;

  /**
   * @param $filters array of filters with the structure
   *   $filters = array(
   *     machineName => filterObject,
   *   )
   *   machineName      is the machine name of the filter
   *   filterObject     instance of a filter object
   */
  public function __construct($session_key, $filters = array(), $defaults = array()) {
    $this->key = $session_key;
    $this->filters = $filters;
    $this->values = array();
    $this->values += isset($_SESSION['campaignion_manage']) && isset($_SESSION['campaignion_manage'][$session_key]) ? $_SESSION['campaignion_manage'][$session_key] : array();
    foreach ($defaults as $index => $values) {
      $values += array('values' => array());
      $values['values'] += $this->filters[$values['type']]->defaults();
      if (isset($this->values[$index])) {
        $this->values[$index] = drupal_array_merge_deep($this->values[$index], $values);
      }
      else {
        $this->values[] = $values;
      }
    }
  }

  public function applyFilters($query) {
    foreach ($this->values as $values) {
      if (!is_array($values) || !isset($values['type']) || !isset($this->filters[$values['type']])) {
        continue;
      }
      $filter = $this->filters[$values['type']];
      $filter->apply($query, $values['values']);
    }
  }

  public function form(&$form, &$form_state, $values = NULL) {
    if ($values) {
      $this->values = $values['filter'];
    }
    ctools_add_js('auto-submit');
    $form['#attributes']['class'][] = 'ctools-auto-submit-full-form';
    $form['add_filter'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Add filter'),
      '#weight' => 0,
      '#options' => $this->availableFilterOptions(),
      '#attributes' => array('class' => array('filter-add')),
    );
    $form['filter'] = array(
      '#type' => 'container',
      '#weight' => 1,
      '#attributes' => array('class' => array('filter-fieldsets')),
    );

    foreach ($this->values as $delta => &$values) {
      if (!isset($values['type']) || !isset($this->filters[$values['type']])) {
        continue;
      }
      $values += array('values' => array());
      $filter = $this->filters[$values['type']];
      $removable = !isset($values['removable']) || $values['removable'];
      $form['filter'][$delta] = array(
        '#type'       => 'fieldset',
        '#title'      => $filter->title(),
        '#weight' => 3+$delta,
        '#attributes' => array('class' => array('clearfix', 'campaignion-manage-filter-' . $values['type'])),
      );
      $element = &$form['filter'][$delta];
      $element['type'] = array(
        '#type' => 'value',
        '#value' => $values['type'],
      );
      $element['removable'] = array(
        '#type' => 'value',
        '#value' => $removable,
      );
      if ($removable) {
        $element['#attributes']['class'][] = 'filter-removable';
      }
      $element['active'] = array(
        '#type'          => $removable ? 'checkbox' : 'value',
        '#title'         => t('active'),
        '#description'   => t('The filter will only be applied if this checkbox is checked.'),
        '#default_value' => TRUE,
        '#wrapper_attributes' => array('class' => array('filter-active-toggle')),
      );
      $element['values'] = array(
        '#type' => 'container',
      );
      $filter->formElement($element['values'], $form_state, $values['values']);
    }

    $form['submit'] = array(
      '#type'  => 'submit',
      '#weight' => 100,
      '#value' => t('Filter'),
      '#ajax' => array(
        'callback' => 'campaignion_manage_ajax_filter',
      ),
      '#attributes' => array('class' => array('ctools-use-ajax', 'ctools-auto-submit-click')),
    );
  }

  public function availableFilterOptions() {
    $valuesByType = array();
    foreach ($this->values as $config) {
      $valuesByType[$config['type']][] = $config['values'];
    }
    $options = array();
    foreach ($this->filters as $name => $filter) {
      $current = isset($valuesByType[$name]) ? $valuesByType[$name] : array();
      if ($filter->isApplicable($current)) {
        $options[$name] = $filter->title();
      }
    }
    return $options;
  }

  public function submit(&$form, &$form_state) {
    $form_state['redirect'] = FALSE;
    $fvalues = &drupal_array_get_nested_value($form_state['values'], $form['#parents']);
    $finput = &drupal_array_get_nested_value($form_state['input'], $form['#parents']);
    $this->values = &$fvalues['filter'];
    foreach ($this->values as $delta => $values) {
      if (empty($values['active'])) {
        unset($this->values[$delta]);
      }
    }
    foreach ($fvalues['add_filter'] as $name => $active) {
      if ($active) {
        $this->values[] = array(
          'type' => $name,
          'values' => $this->filters[$name]->defaults(),
        );
      }
    }
    // We want to uncheck all checkboxes again (else we keep adding filters)
    // form_builder() uses $form_state['input'] to set $element['#value'] on
    // the individual checkboxes. So we need to unset the values there.
    $finput['add_filter'] = array();
    $form_state['rebuild'] = TRUE;
    $_SESSION['campaignion_manage'][$this->key] = $this->values;
  }
}
