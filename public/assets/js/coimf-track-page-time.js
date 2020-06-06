(function ($, window, document) {
    'use strict';

    $(document).ready(function() {
        let vStartTime = new Date();
        let vSent = false;

        $(document).on("scroll", function() {
            let vPostElement = $( gCoimf.mSettings.mPageTrackSelector );
    
            if ( !vPostElement.length ) {
                throw "Coimf-Track-Page: Selector element does not match any element";
            }

            // 
            if (!vSent && $(this).scrollTop() >= vPostElement.position().top + vPostElement.prop("scrollHeight") - window.screen.height / 2) {
                let vPageTime = Math.floor((new Date() - vStartTime) / 1000);
    
                // FIXME: make this a setting
                if (vPageTime < 2 ) {
                    return;
                }
    
                vSent = true;
    
                $.ajax({
                    type: "POST",
                    url: gCoimf.mSiteURL + "/wp-json/coimf/v1/track-page-time/",
                    data: {
                        "pageTime": vPageTime,
                        "pageLocation": window.location.pathname,
                    },
                });
            }
        });
    });

})(jQuery, window, document, undefined);
