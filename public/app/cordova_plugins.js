cordova.define('cordova/plugin_list', function (require, exports, module) {
  module.exports = [
    {
      file: 'app/plugins/cordova-plugin-statusbar/www/statusbar.js',
      id: 'cordova-plugin-statusbar.statusbar',
      pluginId: 'cordova-plugin-statusbar',
      clobbers: ['window.StatusBar'],
    },
    {
      file:
        'app/plugins/cordova-plugin-statusbar/src/browser/StatusBarProxy.js',
      id: 'cordova-plugin-statusbar.StatusBarProxy',
      pluginId: 'cordova-plugin-statusbar',
      runs: true,
    },
    {
      file: 'app/plugins/cordova-plugin-device/www/device.js',
      id: 'cordova-plugin-device.device',
      pluginId: 'cordova-plugin-device',
      clobbers: ['device'],
    },
    {
      file: 'app/plugins/cordova-plugin-device/src/browser/DeviceProxy.js',
      id: 'cordova-plugin-device.DeviceProxy',
      pluginId: 'cordova-plugin-device',
      runs: true,
    },
    {
      file: 'app/plugins/cordova-plugin-splashscreen/www/splashscreen.js',
      id: 'cordova-plugin-splashscreen.SplashScreen',
      pluginId: 'cordova-plugin-splashscreen',
      clobbers: ['navigator.splashscreen'],
    },
    {
      file:
        'app/plugins/cordova-plugin-splashscreen/src/browser/SplashScreenProxy.js',
      id: 'cordova-plugin-splashscreen.SplashScreenProxy',
      pluginId: 'cordova-plugin-splashscreen',
      runs: true,
    },
    {
      file: 'app/plugins/cordova-plugin-ionic-webview/src/www/util.js',
      id: 'cordova-plugin-ionic-webview.IonicWebView',
      pluginId: 'cordova-plugin-ionic-webview',
      clobbers: ['Ionic.WebView'],
    },
  ];
  module.exports.metadata =
    // TOP OF METADATA
    {
      'cordova-plugin-whitelist': '1.3.3',
      'cordova-plugin-statusbar': '2.4.2',
      'cordova-plugin-device': '2.0.2',
      'cordova-plugin-splashscreen': '5.0.2',
      'cordova-plugin-ionic-webview': '4.2.1',
      'cordova-plugin-ionic-keyboard': '2.2.0',
    };
  // BOTTOM OF METADATA
});
