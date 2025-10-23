// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js')
            .then(function (registration) {
                console.log('Service Worker registered with scope:', registration.scope);
            })
            .catch(function (error) {
                console.log('Service Worker registration failed:', error);
            });
    });
}

// Handle before install prompt
let deferredPrompt;
const installButton = document.createElement('button');

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;

    // Show install promotion (you can customize this)
    showInstallPromotion();
});

function showInstallPromotion() {
    // Create a custom install button or banner
    installButton.style.cssText = `
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: #10b981;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        z-index: 1000;
        font-family: inherit;
    `;
    installButton.innerHTML = '📱 Install App';
    installButton.addEventListener('click', installApp);

    document.body.appendChild(installButton);
}

function installApp() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
                // Hide the install button
                installButton.style.display = 'none';
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
        });
    }
}

// Detect if app is running in standalone mode
function isRunningStandalone() {
    return (window.matchMedia('(display-mode: standalone)').matches) ||
        (window.navigator.standalone) ||
        (document.referrer.includes('android-app://'));
}

// Add standalone mode specific behaviors
if (isRunningStandalone()) {
    document.documentElement.classList.add('pwa-standalone');

    // Hide browser UI elements if needed
    console.log('App is running in standalone mode');
}

// Network status detection
window.addEventListener('online', function () {
    console.log('App is online');
    // You can show a notification or update UI
});

window.addEventListener('offline', function () {
    console.log('App is offline');
    // You can show offline notification
});

// Export for use in other modules
export { isRunningStandalone, installApp };