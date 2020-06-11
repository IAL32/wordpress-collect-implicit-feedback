<?php
$vTrackPageSelector = get_option( "coimf-track-page-selector" );
$vTrackUserClicks   = get_option( "coimf-track-user-clicks" );
// FIXME: this has to be a dynamic array, with its dedicated API endpoint in
// admin area
$vTrackSlug         = get_option( "coimf-track-slug" );
$vTrackMinPageReadTime = get_option( "coimf-track-min-read-time-seconds" );
$vTrackMaxPageReadTime = get_option( "coimf-track-max-read-time-seconds" );
// update_option( "coimf-track-page-selector", ".post .entry-content" );
// update_option( "coimf-track-user-clicks", 1 );
?>

<form method="POST" action="<?php echo admin_url( "options.php" ); ?>">
    <?php settings_fields( "coimf-settings-group" ); ?>
    <div class="wrap">
        <fieldset>
            <label for="coimf-track-page-selector">
                <input name="coimf-track-page-selector" type="text" id="coimf-track-page-selector" value="<?php echo ( $vTrackPageSelector ); ?>" />
                <?php _e("Page Track Selector", "coimf"); ?>
            </label>
        </fieldset>
        <fieldset>
            <label for="coimf-track-user-clicks">
                <input name="coimf-track-user-clicks" type="checkbox" id="coimf-track-user-clicks" value="1" <?php checked( 1, $vTrackUserClicks ); ?> />
                <?php _e("Track User Clicks", "coimf"); ?>
            </label>
        </fieldset>
        <fieldset>
            <label for="coimf-track-slug">
                <input name="coimf-track-slug" type="text" id="coimf-track-slug" value="<?php echo ( $vTrackSlug ); ?>" />
                <?php _e("Track User Clicks", "coimf"); ?>
            </label>
        </fieldset>
        <fieldset>
            <label for="coimf-track-min-read-time-seconds">
                <input name="coimf-track-min-read-time-seconds" type="number" id="coimf-track-min-read-time-seconds" value="<?php echo ( $vTrackMinPageReadTime ); ?>" />
                <?php _e("Min Page Read Seconds", "coimf"); ?>
            </label>
        </fieldset>
        <fieldset>
            <label for="coimf-track-max-read-time-seconds">
                <input name="coimf-track-max-read-time-seconds" type="number" id="coimf-track-max-read-time-seconds" value="<?php echo ( $vTrackMaxPageReadTime ); ?>" />
                <?php _e("Max Page Read Seconds", "coimf"); ?>
            </label>
        </fieldset>
        <?php submit_button(); 
        ?>
    </div>
</form>
