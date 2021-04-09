<?php

class TranslationsModule extends OBFModule {
  public $name = 'Translations v1.0';
  public $description = 'Add multi-language support to OBServer.';

  public function callbacks () {

  }

  public function install () {
    $this->db->insert('users_permissions', [
      'category'    => 'administration',
      'description' => 'manage translations',
      'name'        => 'translations_module'
    ]);

    return true;
  }

  public function uninstall () {
    $this->db->where('name','translations_module');
    $permission = $this->db->get_one('users_permissions');

    $this->db->where('permission_id', $permission['id']);
    $this->db->delete('users_permissions_to_groups');

    $this->db->where('id', $permission['id']);
    $this->db->delete('users_permissions');

    return true;
  }
}
