<?php
$vTrackPageSelector = get_option( "coimf_track_page_selector" );
$vTrackUserClicks   = get_option( "coimf_track_user_clicks" );

// update_option( "coimf_track_page_selector", ".post .entry-content" );
// update_option( "coimf_track_user_clicks", 1 );
?>

<form method="POST" action="<?php echo admin_url( "options.php" ); ?>">
<?php settings_fields( "coimf-settings-group" ); ?>
<div class="wrap">
    <table>
        <tbody>
            <tr valign="top">
                    <?php // do_settings_fields( "coimf-settings-group", "" ); ?>

                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e( "Settings" ); ?></th>
                            <td>
                                <fieldset>
                                    <label for="coimf-track-page-selector">
                                        <input name="coimf-track-page-selector" type="text" id="coimf-track-page-selector" value="<?php echo ( $vTrackPageSelector ); ?>" />
                                        <?php _e( 'Page Track Selector', 'coimf' ); ?>
                                    </label>
                                </fieldset>
                                <fieldset>
                                    <label for="coimf-track-user-clicks">
                                        <input name="coimf-track-user-clicks" type="checkbox" id="coimf-track-user-clicks" <?php echo checked ( $vTrackUserClicks ); ?> />
                                        <?php _e( 'Track User Clicks', 'coimf' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                </tr>
            </tbody>
        </table>
    </div>
    <?php // submit_button(); ?>
</form>