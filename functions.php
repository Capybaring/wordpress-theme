<?php
// 主题基础设置
add_action('after_setup_theme', function () {
    // 告诉 WooCommerce：这个主题是兼容的
    add_theme_support('woocommerce');
});
function my_shop_theme_enqueue_styles() {
    // 加载主题主样式
    wp_enqueue_style( 'my-shop-theme-style', get_stylesheet_uri() );

    // 如果你有单独的单品页样式文件
    wp_enqueue_style( 'single-product-style', get_template_directory_uri() . '/woocommerce/css/single-product.css', array(), '1.0' );
}
add_action( 'wp_enqueue_scripts', 'my_shop_theme_enqueue_styles' );
