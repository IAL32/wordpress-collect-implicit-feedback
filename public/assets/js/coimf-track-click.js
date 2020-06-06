// TODO: add click tracking: https://nevyan.blogspot.com/2006/12/free-website-click-heatmap-diy.html
(function ($, window, document) {
    'use strict';

    // we do not track admin users
    // if (gCoimf.isUserAdmin) {
    //     return;
    // }

    let vClicked = false;

    function getDocumentFullHeight() {
        var vBody = document.body,
            vHTML = document.documentElement;

        return Math.max(vBody.scrollHeight, vBody.offsetHeight,
            vHTML.clientHeight, vHTML.scrollHeight, vHTML.offsetHeight);
    }

    $(document).on("click", function (aMouseClickEvent) {

        if (vClicked) {
            return;
        }

        let vPageX = aMouseClickEvent.pageX;
        let vPageY = aMouseClickEvent.pageY;
        let vPageWidth = window.screen.width;
        let vPageHeight = window.screen.height;

        if (!vPageX || !vPageY) {
            // cannot log empty click position
            return;
        }

        vClicked = true;

        $.post(gCoimf.mSiteURL + "/wp-json/coimf/v1/track-click/", {
            "mouseX": vPageX,
            "mouseY": vPageY,
            "resolutionX": vPageWidth,
            "resolutionY": vPageHeight,
            "pageLocation": window.location.pathname
        });

    });

})(jQuery, window, document, undefined);
