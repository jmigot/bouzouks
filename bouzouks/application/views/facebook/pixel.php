<!-- Facebook Conversion Code for Inscriptions au jeu -->
<script>(function() {
var _fbq = window._fbq || (window._fbq = []);
if (!_fbq.loaded) {
var fbds = document.createElement('script');
fbds.async = true;
fbds.src = '//connect.facebook.net/en_US/fbds.js';
var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(fbds, s);
_fbq.loaded = true;
}
})();
window._fbq = window._fbq || [];
window._fbq.push(['track', <?= $pixel_id ?>, {'value':'0.00','currency':'EUR'}]);
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=<?= $pixel_id ?>&amp;cd[value]=0.00&amp;cd[currency]=EUR&amp;noscript=1" /></noscript>