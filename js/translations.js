OBModules.Translations = new function () {

  this.init = function () {
    OB.Callbacks.add('ready', 0, OBModules.Translations.initMenu);
  }

  this.initMenu = function () {
    OB.UI.addSubMenuItem('admin', 'Translations', 'translations', OBModules.Translations.open, 150);
  }

  this.open = function () {
    OB.UI.replaceMain('modules/translations/translations.html');
  }
  
}
