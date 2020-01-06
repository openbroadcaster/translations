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
    if (!empty($this->db->get_one('translations_languages'))) {
      return [false, 'Language code already exists.'];
    }

    return [true, 'Validation successful.'];
  }

  public function language_save ($data) {
    $language = [
      'name' => $data['name'],
      'code' => $data['code']
    ];

    if (!$this->db->insert('translations_languages', $language)) {
      return [false, 'Failed to insert language into database.'];
    }

    return [true, 'Successfully added language.'];
  }

  public function language_overview () {
    $result = $this->db->get('translations_languages');

    $this->db->query('SELECT COUNT(`id`) FROM `translations_sources`');
    $total = $this->db->assoc_row()['COUNT(`id`)'];

    foreach ($result as $index => $lang) {
      $this->db->where('language_id', $lang['id']);
      $translations = $this->db->get('translations_values');
      $result[$index]['translations'] = count($translations);
      $result[$index]['total'] = $total;
    }

    return [true, 'Successfully loaded language overview.', $result];
  }

  public function language_delete ($data) {
    $this->db->where('id', $data['language_id']);
    $this->db->delete('translations_languages');

    $this->db->where('language_id', $data['language_id']);
    $this->db->delete('translations_values');

    return [true, 'Successfully removed language.'];
  }

  public function language_view ($data) {
    $this->db->where('language_id', $data['language_id']);
    $values = $this->db->get('translations_values');
    $sources = $this->db->get('translations_sources');

    $results = array();

    foreach ($values as $key => $value) {
      $this->db->where('string', $value['source_str']);
      $src_exists = ($this->db->get_one('translations_sources') ? true : false);

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

  public function language_update_validate ($data) {
    $this->db->where('id', $data['language_id']);
    if (!$this->db->get_one('translations_languages')) {
      return [false, 'Language does not exist.'];
    }

    foreach ($data['translations'] as $elem) {
      if (count($elem) != 2) {
        return [false, 'One or more translations have an invalid number of elements.'];
      }
    }

    return [true, 'Validation successful.'];
  }

  public function language_update ($data) {
    $this->db->where('language_id', $data['language_id']);
    $this->db->delete('translations_values');

    foreach ($data['translations'] as $translation) {
      $this->db->insert('translations_values', [
        'source_str'  => $translation[0],
        'result_str'  => $translation[1],
        'language_id' => $data['language_id']
      ]);
    }

    return [true, 'Successfully updated translations.'];
  }

}
