<?php

class Translations extends OBFController {

  public function __construct () {
    parent::__construct();
    $this->model = $this->load->model('Translations');
    $this->user->require_permission('translations_module');
  }

  public function language_save () {
    $result = $this->model('language_validate', $this->data);
    if (!$result[0]) {
      return $result;
    }

    return $this->model('language_save', $this->data);
  }

  public function language_overview () {
    return $this->model('language_overview');
  }

  public function language_delete () {
    return $this->model('language_delete', $this->data);
  }

}
