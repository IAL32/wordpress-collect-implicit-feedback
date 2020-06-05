<?php

?>

<div class="wrap">

    <h2><?php _e( "Actions", "coimf" ) ?></h2>

    <form method="GET">
        <!-- <input type="hidden" name="page" value="<?php echo $_REQUEST["page"]; ?>" /> -->
        <?php // $this->mActionsTable->search_box( __( 'Find', "coimf" ), 'coimf-action-find' ); ?>
        <?php $this->mActionsTable->display(); ?>

    </form>

</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
