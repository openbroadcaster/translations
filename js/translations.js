OBModules.Translations = new function () {

  this.init = function () {
    OB.Callbacks.add('ready', 0, OBModules.Translations.initMenu);
  }

  this.initMenu = function () {
    OB.UI.addSubMenuItem('admin', 'Translations', 'translations', OBModules.Translations.open, 150, 'translations_module');
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
        OBModules.Translations.languageOverview();
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
        $lang.append($('<td/>').text(element.translations + ' / ' + element.total + ' (' + ((element.translations / element.total) * 100).toFixed(2) + '%)'));

        var $edit = '<button class="edit" onclick="OBModules.Translations.languageView(' + element.id + ')">Edit</button>';
        var $delete = '<button class="delete" onclick="OBModules.Translations.languageDelete(' + element.id + ')">Delete</button>';

        $lang.append($('<td/>').html($edit + ' ' + $delete));

        $('#translations_languages tbody').append($lang);
      });
    });
  }

  this.languageView = function (lang_id) {
    OB.UI.replaceMain('modules/translations/translations_single.html');
    $('#translations_single_id').val(lang_id);

    $('#translations_single_values tbody').empty();
    OB.API.post('translations', 'language_view', { language_id: lang_id }, function (response) {
      var msg_result = response.status ? 'success' : 'error';
      if (!response.status) {
        OBModules.Translations.open();
        $('#translations_message').obWidget(msg_result, response.msg);
        return false;
      }

      $(response.data).each(function (index, element) {
        var $html = $('<tr/>');

        var $source_str = $('<td/>').text(element.source_str);
        if (!element.source_exists) $source_str.addClass('translation-source-noexist');
        var $result_str = $('<td/>').append($('<textarea/>').val(element.result_str));

        $html.append($source_str).append($result_str);

        if (!element.source_exists) {
          $html.addClass('translation-nosrc');
        } else if (element.result_str == '') {
          $html.addClass('translation-empty');
        } else {
          $html.addClass('translation-done');
        }

        $('#translations_single_values tbody').append($html);
      });

    });
  }

  this.languageDelete = function (lang_id) {
    OB.UI.confirm({
      text: "Are you sure you want to delete this language?",
      okay_class: "delete",
      callback: function () {
        OBModules.Translations.languageDeleteConfirm(lang_id);
      }
    });
  }

  this.languageDeleteConfirm = function (lang_id) {
    var post = {language_id: lang_id};

    OB.API.post('translations', 'language_delete', post, function (response) {
      var msg_result = (response.status) ? 'success' : 'error';
      $('#translations_message').obWidget(msg_result, response.msg);

      OBModules.Translations.languageOverview();
    });
  }

  /*************
   TRANSLATIONS
  *************/

  this.translationsFilter = function (elem) {
    $('.translations_filter').val($(elem).val());

    switch ($(elem).val()) {
      case 'translated':
        $('.translation-done').show();
        $('.translation-empty, .translation-nosrc').hide();
      break;
      case 'not_translated':
        $('.translation-empty').show();
        $('.translation-done, .translation-nosrc').hide();
      break;
      case 'missing_src':
        $('.translation-nosrc').show();
        $('.translation-done, .translation-empty').hide();
      break;
      default:
        $('.translation-done, .translation-empty, .translation-nosrc').show();
      break;
    }

  }

  this.translationsUpdate = function () {
    var post = {};
    post.language_id = $('#translations_single_id').val();
    post.translations = [];

    $('#translations_single_values tbody tr').each(function (i, elem) {
      if ($(elem).find('td:eq(1) textarea').val().trim() != '') {
        post.translations.push([
          $(elem).find('td:eq(0)').html(),
          $(elem).find('td:eq(1) textarea').val()
        ]);
      }
    });

    OB.API.post('translations', 'language_update', post, function (response) {
      var msg_result = (response.status) ? 'success' : 'error';
      if (msg_result == 'success') {
        OBModules.Translations.languageView($('#translations_single_id').val());
      }
      $('#translations_single_message').obWidget(msg_result, response.msg);
    });

  }

}
