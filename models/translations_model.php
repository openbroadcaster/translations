<?php

class TranslationsModel extends OBFModel {

  public function language_validate ($data) {
    if (empty($data['name'])) {
      return [false, 'Language name needs to be set.'];
    }

    if (empty($data['code'])) {
      return [false, 'Language code needs to be set.'];
    }

    $this->db->where('code', $data['code']);
    if (!empty($this->db->get_one('module_translations_languages'))) {
      return [false, 'Language code already exists.'];
    }

    return [true, 'Validation successful.'];
  }

  public function language_save ($data) {
    $language = [
      'name' => $data['name'],
      'code' => $data['code']
    ];

    if (!$this->db->insert('module_translations_languages', $language)) {
      return [false, 'Failed to insert language into database.'];
    }

    return [true, 'Successfully added language.'];
  }

  public function language_overview () {
    $result = $this->db->get('module_translations_languages');

    foreach ($result as $index => $lang) {
      $this->db->where('language_id', $lang['id']);
      $translations = $this->db->get('module_translations_values');
      $result[$index]['translations'] = count($translations);
    }

    return [true, 'Successfully loaded language overview.', $result];
  }

  public function language_delete ($data) {
    $this->db->where('id', $data['language_id']);
    $this->db->delete('module_translations_languages');

    $this->db->where('language_id', $data['language_id']);
    $this->db->delete('module_translations_values');

    return [true, 'Successfully removed language.'];
  }

  public function language_view ($data) {
    $this->db->where('language_id', $data['language_id']);
    $values = $this->db->get('module_translations_values');
    $sources = $this->db->get('module_translations_sources');

    $results = array();

    foreach ($values as $key => $value) {
      $this->db->where('string', $value['source_str']);
      $src_exists = ($this->db->get_one('module_translations_sources') ? true : false);

      $results[] = array(
        'source_str'    => $value['source_str'],
        'result_str'    => $value['result_str'],
        'source_exists' => $src_exists
      );
    }

    foreach ($sources as $source) {
      $val_exists = false;
      foreach ($values as $value) {
        if ($value['source_str'] == $source['string']) {
          $val_exists = true;
          break;
        }
      }

      if (!$val_exists) $results[] = array(
        'source_str'    => $source['string'],
        'result_str'    => '',
        'source_exists' => true
      );
    }

    return [true, 'Successfully loaded language translations.', $results];
  }

}
