<?php
$vTrackPageSelector = get_option( "coimf_track_page_selector" );
?>

<div class="wrap">
    <table>
        <tr valign="top">
            <form method="POST" action="<?php echo admin_url( "options.php" ); ?>">
                <?php settings_fields( "coimf-settings-group" ); ?>
                <?php do_settings_fields( "coimf-settings-group", "" ); ?>

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
                        </td>
                    </tr>
                </table>
            </form>
        </tr>
    </table>
</div>
