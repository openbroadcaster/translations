<?php

class TranslationsModule extends OBFModule {
  public $name = 'Translations';
  public $description = 'Add multi-language support to OpenBroadcaster.';

  public function callbacks () {

  }

  public function install () {
    $this->db->insert('users_permissions', [
      'category'    => 'administration',
      'description' => 'manage translations',
      'name'        => 'translations_module'
    ]);

    $this->db->query('CREATE TABLE IF NOT EXISTS `module_translations_sources` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `string` text NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

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
