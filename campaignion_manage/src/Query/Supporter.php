<?php

namespace Drupal\campaignion_manage\Query;

class Supporter extends Base {
  public function __construct() {
    $query = db_select('redhen_contact', 'r');
    $query->innerJoin('field_data_redhen_contact_email', 'e', 'e.entity_id = r.contact_id');
    $query->fields('r', array('contact_id', 'first_name', 'middle_name', 'last_name'))
      ->fields('e', array('redhen_contact_email_value', 'redhen_contact_email_hold'))
      ->condition('r.type', 'contact')
      ->orderBy('r.updated', 'DESC');

    parent::__construct($query);
  }
  public function modifyResult(&$rows) {
    if (empty($rows)) {
      return;
    }

    $rows_by_id = array();
    foreach ($rows as $row) {
      $row->tags = array();
      $rows_by_id[$row->contact_id] = $row;
    }

    $sql = <<<SQL
SELECT st.entity_id, t.tid, t.name
FROM {field_data_supporter_tags} st 
  INNER JOIN {taxonomy_term_data} t ON st.supporter_tags_tid=t.tid
WHERE st.entity_type='redhen_contact' AND st.entity_id IN(:ids)
ORDER BY st.entity_id, t.name
SQL;
    $result = db_query($sql, array(':ids' => array_keys($rows_by_id)));
    foreach ($result as $row) {
      $rows_by_id[$row->entity_id]->tags[$row->tid] = $row->name;
    }
  }
}
