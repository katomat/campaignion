<?php

namespace Drupal\campaignion_manage\Filter;

interface FilterInterface {
  /**
   * Insert additional form elements into the elements array.
   *
   * @param element An already prepared fieldset form element.
   * @param form_state The form_state array of the whole filterForm.
   * @param values The array of values from previous submissions (ie. use as default values!)
   */
  public function formElement(array &$element, array &$form_state, array &$values);
  /**
   * A human readable title for this filter. „Filter by …“
   *
   * @return string
   */
  public function title();
  /**
   * Apply all necessary conditions to the $query using the values from $values.
   *
   * @param BaseQuery the query for the to-be-filtered listing.
   * @param array Array of values from previous form submissions.
   */
  public function apply($query, array $values);

  /**
   * Provide information if the filter is currently applicable
   *
   * @param $current array of already applied instances of this filter.
   * @return TRUE if the filter is currently applicable, FALSE otherwise
   */
  public function isApplicable($current);
}
