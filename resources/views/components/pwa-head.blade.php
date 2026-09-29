{{-- PWA: manifest, íconos y registro del service worker (public/sw.js) --}}
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#F7F4EE">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="EclesTres">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
</script>
