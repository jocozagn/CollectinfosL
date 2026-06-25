(function () {
  var deferredInstallPrompt = null;

  function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
      return;
    }

    window.addEventListener('load', function () {
      navigator.serviceWorker
        .register('/sw.js', { scope: '/' })
        .catch(function () {});
    });
  }

  function createInstallBanner() {
    var banner = document.getElementById('pwa-install-banner');
    if (banner) {
      return banner;
    }

    banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.className = 'pwa-install-banner';
    banner.hidden = true;
    banner.innerHTML =
      '<div class="pwa-install-banner__content">' +
      '<p><strong>Installer Collectinfos</strong> — accédez à la plateforme comme une application sur votre téléphone.</p>' +
      '<div class="pwa-install-banner__actions">' +
      '<button type="button" class="ci-btn ci-btn--primary ci-btn--sm" id="pwa-install-btn">Installer</button>' +
      '<button type="button" class="ci-btn ci-btn--outline ci-btn--sm" id="pwa-install-dismiss">Plus tard</button>' +
      '</div>' +
      '</div>';

    document.body.appendChild(banner);

    document.getElementById('pwa-install-dismiss').addEventListener('click', function () {
      banner.hidden = true;
      try {
        localStorage.setItem('collectinfos_pwa_install_dismissed', '1');
      } catch (error) {}
    });

    document.getElementById('pwa-install-btn').addEventListener('click', function () {
      if (!deferredInstallPrompt) {
        return;
      }

      deferredInstallPrompt.prompt();
      deferredInstallPrompt.userChoice.finally(function () {
        deferredInstallPrompt = null;
        banner.hidden = true;
      });
    });

    return banner;
  }

  function showInstallUi() {
    try {
      if (localStorage.getItem('collectinfos_pwa_install_dismissed') === '1') {
        return;
      }
    } catch (error) {}

    if (window.matchMedia('(display-mode: standalone)').matches) {
      return;
    }

    var banner = createInstallBanner();
    banner.hidden = false;
  }

  function initInstallPrompt() {
    window.addEventListener('beforeinstallprompt', function (event) {
      event.preventDefault();
      deferredInstallPrompt = event;
      showInstallUi();
    });

    window.addEventListener('appinstalled', function () {
      deferredInstallPrompt = null;
      var banner = document.getElementById('pwa-install-banner');
      if (banner) {
        banner.hidden = true;
      }
    });
  }

  registerServiceWorker();
  initInstallPrompt();
})();
