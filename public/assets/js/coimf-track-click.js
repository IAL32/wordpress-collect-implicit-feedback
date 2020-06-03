// TODO: add click tracking: https://nevyan.blogspot.com/2006/12/free-website-click-heatmap-diy.html
(function($, window) {
    'use strict';

    // we do not track admin users
    if (gCoimf.isUserAdmin) {
        return;
    }

    $("body").mousemove(function(aMouseMoveEvent) {
        let vPageX = aMouseMoveEvent.pageX;
        let vPageY = aMouseMoveEvent.pageY;

        console.log("coimf-track-click", vPageX, vPageY);
        $.post( + vPageX + "," + vPageY, {
            "pageLocation": window.location.pathName
        });

    });

})(jQuery, window, undefined);
