
<?php

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    echo (
        "<div>

    <p>Auth Plugin installed successfully</p>
<div>"
    );
} else {
    echo (
        "<div>
    <p>Please install dependencies: WooCommerce, WooCommerce Subscriptions</p>
<div>"
    );
}
 
?>

