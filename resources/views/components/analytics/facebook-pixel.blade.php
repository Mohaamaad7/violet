{{-- Facebook Pixel Integration (delayed load for performance) --}}
@props(['pixelId' => null])

@if($pixelId)
<!-- Meta Pixel Code -->
<script>
(function() {
    var pixelId = '{{ $pixelId }}';
    var loaded = false;
    var userEvents = ['scroll', 'click', 'mousemove', 'touchstart'];

    function loadPixel() {
        if (loaded) return;
        loaded = true;

        userEvents.forEach(function(evt) {
            window.removeEventListener(evt, loadPixel);
        });

        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', pixelId);
        fbq('track', 'PageView');
    }

    userEvents.forEach(function(evt) {
        window.addEventListener(evt, loadPixel);
    });

    setTimeout(loadPixel, 4000);
})();
</script>
<noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/>
</noscript>
<!-- End Meta Pixel Code -->
@endif
