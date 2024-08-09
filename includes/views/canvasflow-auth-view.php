<?php
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    echo (
        "<div>
    <h1>Canvasflow Auth</h1>
    <p>Auth Plugin installed successfully</p>
<div>"
    );
} else {
    echo (
        "<div>
    <h1>Canvasflow Auth</h1>
    <p>Please install dependencies: WooCommerce</p>
<div>"
    );
}?>

