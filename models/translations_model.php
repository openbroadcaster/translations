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
      // BINARY to force case sensitive: https://stackoverflow.com/questions/19462919/mysql-select-distinct-should-be-case-sensitive
      $this->db->query('
        SELECT DISTINCT (BINARY translations_values.source_str) AS string FROM translations_values
        LEFT JOIN translations_sources ON translations_values.source_str = translations_sources.string
        WHERE translations_sources.string IS NOT NULL AND translations_values.language_id="'.$this->db->escape($lang['id']).'"
      ');
      
      $translations = $this->db->indexed_list();
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
    
    $source_indices = [];
    foreach ($sources as $index=>$source) {
    
      $source_indices[$source['string']] = $index;
    
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
    
    foreach($results as $index=>$result) $results[$index]['source_index'] = $source_indices[$result['source_str']] ?? -1;
    usort($results, function($a, $b) { return $a['source_index'] > $b['source_index']; });

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

    // put into a key/value array to remove duplicates (client-side weirdness)
    $strings = [];
    foreach($data['translations'] as $translation) $strings[$translation[0]] = $translation[1];

    // add to table
    foreach ($strings as $source=>$result) {
      $this->db->insert('translations_values', [
        'source_str'  => $source,
        'result_str'  => $result,
        'language_id' => $data['language_id']
      ]);
    }

    return [true, 'Successfully updated translations.'];
  }

}
