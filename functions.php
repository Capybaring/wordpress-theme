
<?php

// 主题基础设置
add_action('after_setup_theme', function () {
    // 告诉 WooCommerce：这个主题是兼容的
    add_theme_support('woocommerce');
});
function myshop_enqueue_styles()
{
    wp_enqueue_script('jquery');
    wp_enqueue_style('theme-style', get_stylesheet_uri());
    wp_enqueue_style('single-product-style', get_template_directory_uri() . '/woocommerce/css/single-product.css', [], '1.0');
    wp_enqueue_style('archive-product-style', get_template_directory_uri() . '/woocommerce/css/archive-product.css', [], '1.0');
    wp_enqueue_style('cart-style', get_template_directory_uri() . '/woocommerce/css/cart.css');
    wp_enqueue_style('bottom-nav-style', get_template_directory_uri() . '/css/bottom-nav.css', [], '1.0');
}
add_action('wp_enqueue_scripts', 'myshop_enqueue_styles');

add_filter('template_include', function ($template) {
    if (is_cart()) {
        $custom = get_template_directory() . '/woocommerce/cart.php';
        if (file_exists($custom))
            return $custom;
    }
    return $template;
});

// 处理 AJAX 添加到购物车
add_action('wp_ajax_add_to_cart', 'add_to_cart_callback');
add_action('wp_ajax_nopriv_add_to_cart', 'add_to_cart_callback');

function add_to_cart_callback()
{
    // 获取商品 ID 和数量
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // 验证商品是否存在
    if ($product_id > 0) {
        // 将商品添加到购物车
        $added = WC()->cart->add_to_cart($product_id, $quantity);

        // 返回响应
        if ($added) {
            wp_send_json_success(['message' => '商品已成功加入购物车']);
        } else {
            wp_send_json_error(['message' => '添加商品到购物车失败']);
        }
    } else {
        wp_send_json_error(['message' => '无效的商品']);
    }

    wp_die(); // 结束 AJAX 请求
}


// 注册 AJAX 钩子
add_action('wp_ajax_handle_add_to_order', 'my_custom_add_to_cart_handler');
add_action('wp_ajax_nopriv_handle_add_to_order', 'my_custom_add_to_cart_handler');

function my_custom_add_to_cart_handler()
{
    // 1. 确保 WooCommerce 环境存在
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce 购物车未初始化'));
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error(array('message' => '找不到该商品'));
    }

    // 2. 检查商品是否可购买
    if (!$product->is_purchasable()) {
        wp_send_json_error(array('message' => '该商品目前不可购买'));
    }

    // 3. 检查库存
    if (!$product->is_in_stock()) {
        wp_send_json_error(array('message' => '商品库存不足 (Out of Stock)'));
    }

    if ($product->managing_stock() && !$product->backorders_allowed()) {
        if ($product->get_stock_quantity() < $quantity) {
            wp_send_json_error(array('message' => '库存不足，剩余：' . $product->get_stock_quantity()));
        }
    }

    // 4. 执行添加并捕获错误
    // 注意：我们先绕过验证尝试添加，或者看看验证到底返回了什么
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

    if ($passed_validation) {
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
        if ($cart_item_key) {
            wp_send_json_success(array('message' => '已成功加入订单'));
        } else {
            wp_send_json_error(array('message' => 'WC 核心添加失败，可能是因为商品已在购物车且限购'));
        }
    } else {
        wp_send_json_error(array('message' => '加入购物车验证未通过'));
    }

    wp_die();
}

/**
 * 移除下单时的邮政编码必填限制
 */
add_filter('woocommerce_default_address_fields', 'custom_remove_checkout_postcode_required');

function custom_remove_checkout_postcode_required($fields)
{
    // 1. 取消必填验证
    $fields['postcode']['required'] = false;

    // 2. 移除邮编格式的后端验证逻辑（防止某些国家强行校验格式）
    unset($fields['postcode']['validate']);

    return $fields;
}

// 1. 移除账户页面的下载菜单项
add_filter('woocommerce_account_menu_items', function ($items) {
    unset($items['downloads']);
    return $items;
}, 99);

// 2. 彻底禁用下载端点路由（防止系统后台扫描）
add_filter('woocommerce_get_query_vars', function ($vars) {
    unset($vars['downloads']);
    return $vars;
}, 99);

/**
 * 精简“我的账户”页面菜单项
 */
add_filter('woocommerce_account_menu_items', function ($items) {
    // 1. 创建全新的菜单列表（按你要求的顺序）
    $new_items = array(
        'edit-account' => '账户详情',
        'orders' => '我的订单',
        'edit-address' => '个人地址',
        'customer-logout' => '退出登录',
    );

    return $new_items;
}, 999);

/**
 * 修正：当用户访问主地址时，默认跳转到“账户详情”或“订单”
 * 否则默认会显示你那个想删掉的“仪表盘文字”
 */
// add_action('template_redirect', function () {
//     if (is_account_page() && !is_wc_endpoint_url() && is_user_logged_in()) {
//         // 默认进入个人中心时，直接重定向到订单页面
//         wp_safe_redirect(wc_get_endpoint_url('orders'));
//         exit;
//     }
// });

/**
 * 强行修改下单后的跳转地址
 * 不管它想去哪个 order-received 页面，一律抓回个人中心
 */
add_filter('woocommerce_get_checkout_order_received_url', 'custom_redirect_to_my_account', 10, 2);
function custom_redirect_to_my_account($return_url, $order)
{
    // 强行指定跳转到“我的账户”页面（即显示你那4个按钮的页面）
    return wc_get_page_permalink('myaccount');
}

