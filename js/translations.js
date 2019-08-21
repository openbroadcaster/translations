OBModules.Translations = new function () {

  this.init = function () {
    OB.Callbacks.add('ready', 0, OBModules.Translations.initMenu);
  }

  this.initMenu = function () {
    OB.UI.addSubMenuItem('admin', 'Translations', 'translations', OBModules.Translations.open, 150);
  }

  this.open = function () {
    OB.UI.replaceMain('modules/translations/translations.html');

    OBModules.Translations.languageOverview();
  }

  /**********
   LANGUAGES
  **********/

  this.languageAdd = function () {
    OB.UI.openModalWindow('modules/translations/translations_newlang.html');
  }

  this.languageSave = function () {
    var post = {
      name: $('#translations_lang_name').val(),
      code: $('#translations_lang_code').val()
    };

    OB.API.post('translations', 'language_save', post, function (response) {
      if (response.status) {
        OB.UI.closeModalWindow();
        OBModules.Translations.open();
        $('#translations_message').obWidget('success', response.msg);
      } else {
        $('#translations_newlang_message').obWidget('error', response.msg);
      }
    });
  }

  this.languageOverview = function () {
    $('#translations_languages tbody').empty();

    OB.API.post('translations', 'language_overview', {}, function (response) {
      if (!response.status) {
        $('#translations_message').obWidget('error', response.msg);
        return false;
      }

      $(response.data).each(function (index, element) {
        var $lang = $('<tr/>');
        $lang.append($('<td/>').text(element.code));
        $lang.append($('<td/>').text(element.name));
        $lang.append($('<td/>').text(element.translations));

        var $edit = '<button class="edit" onclick="OBModules.Translations.languageView(' + element.id + ')">Edit</button>';
        var $delete = '<button class="delete" onclick="OBModules.Translations.languageDelete(' + element.id + ')">Delete</button>';

        $lang.append($('<td/>').html($edit + ' ' + $delete));

        $('#translations_languages tbody').append($lang);
      });
    });
  }

  this.languageView = function () {
    // TODO
  }

  this.languageDelete = function () {
    // TODO
  }

  this.languageDeleteConfirm = function () {
    // TODO
  }

  /*************
   TRANSLATIONS
  *************/

  this.translationsUpdate = function () {
    // TODO
  }

}
