<?php
$vTrackPageSelector = get_option( "coimf-track-page-selector" );
$vTrackUserClicks   = get_option( "coimf-track-user-clicks" );
// FIXME: this has to be a dynamic array, with its dedicated API endpoint in
// admin area
$vTrackSlug         = get_option( "coimf-track-slug" );

// update_option( "coimf-track-page-selector", ".post .entry-content" );
// update_option( "coimf-track-user-clicks", 1 );
?>

<form method="POST" action="<?php echo admin_url( "options.php" ); ?>">
    <?php settings_fields( "coimf-settings-group" ); ?>
    <div class="wrap">
        <fieldset>
            <label for="coimf-track-page-selector">
                <input name="coimf-track-page-selector" type="text" id="coimf-track-page-selector" value="<?php echo ( $vTrackPageSelector ); ?>" />
                <?php _e('Page Track Selector', 'coimf'); ?>
            </label>
        </fieldset>
        <fieldset>
            <label for="coimf-track-user-clicks">
                <input name="coimf-track-user-clicks" type="checkbox" id="coimf-track-user-clicks" value="1" <?php checked( 1, $vTrackUserClicks ); ?> />
                <?php _e('Track User Clicks', 'coimf'); ?>
            </label>
        </fieldset>
        <fieldset>
            <label for="coimf-track-slug">
                <input name="coimf-track-slug" type="text" id="coimf-track-slug" value="<?php echo ( $vTrackSlug ); ?>" />
                <?php _e('Track User Clicks', 'coimf'); ?>
            </label>
        </fieldset>
        <?php submit_button(); 
        ?>
    </div>
</form>
