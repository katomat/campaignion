<?php

namespace Drupal\campaignion;

class ActivityType implements Interfaces\ActivityType {
  public function alterQuery(\SelectQuery $query, $operator) {
  }
  public function createActivityFromRow($data) {
    return new Activity($data);
  }
}
