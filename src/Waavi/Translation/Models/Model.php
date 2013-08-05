<?php namespace Waavi\Translation\Models;

use LaravelBook\Ardent\Ardent;

class Model extends Ardent {

	/**
   *  Since both models have a unique validation constraint, we must call Ardent::buildUniqueExclusionRules when validating an existing model, so that
   *  the model's id is injected in the ignore id field.
   */
  public function validate(array $rules = array(), array $customMessages = array()) {
    if ($this->exists) {
      $rules = $this->buildUniqueExclusionRules();
    }
    return parent::validate($rules, $customMessages);
  }

}