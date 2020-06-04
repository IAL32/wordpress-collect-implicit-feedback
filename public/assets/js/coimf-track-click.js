// TODO: add click tracking: https://nevyan.blogspot.com/2006/12/free-website-click-heatmap-diy.html
(function($, window, document) {
    'use strict';

    // we do not track admin users
    // if (gCoimf.isUserAdmin) {
    //     return;
    // }

    $(document).click(function(aMouseClickEvent) {
        let vPageX = aMouseClickEvent.pageX;
        let vPageY = aMouseClickEvent.pageY;

        console.log("coimf-track-click", vPageX, vPageY);
        $.post( "/wp-json/coimf/v1/track/" + vPageX + "," + vPageY, {
            "pageLocation": window.location.pathname
        });

    });

})(jQuery, window, document, undefined);
