<div class="canvasflow-auth-wrap">
    <h1>Canvasflow Auth</h1>
    <div class="plugins">
        <h2>Required Plugins</h2>
        
        <?php if($is_active['woocommerce']) {?>
            <p>✅ <b>WooCommerce</b> activated</p>
        <?php } else { ?>
            <p>❌ <b>WooCommerce</b> activation is required</p>
        <?php } ?>

        <?php if($is_active['woocommerce-subscriptions']) {?>
            <p>✅ <b>WooCommerce Subscription</b> activated</p>
        <?php } else { ?>
            <p>❌ <b>WooCommerce Subscription</b> activation is required</p>
        <?php } ?>
    </div>
    <br/>
    <div class="user-role">
        <h3>User Role</h3>
        <small>Description</small>
        <form method="post" action="options.php">
            <?php
                settings_fields( $setting_group ); // settings group name
                do_settings_sections( $plugin_name ); // just a page slug
                submit_button(); // "Save Changes" button
            ?>
        </form>
    </div>
</div>