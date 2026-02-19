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
    if (is_checkout() && !is_order_received_page()) {
        // 注意这里：如果你把文件放在 woocommerce/checkout/form-checkout.php
        $custom_checkout = get_template_directory() . '/woocommerce/checkout/form-checkout.php';
        if (file_exists($custom_checkout))
            return $custom_checkout;
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
// 1. 降低密码强度要求（0=最简单，1=弱，2=中等）
add_filter('woocommerce_min_password_strength', function () {
    return 0;
});

// 2. 修改提示文字，告诉用户 6 位就行
add_filter('password_hint', function () {
    return '提示：建议密码长度至少为 6 位。';
});

/**
 * 对应 JS 中的 update_cart_quantity
 */
add_action('wp_ajax_update_cart_quantity', 'ajax_update_cart_quantity');
add_action('wp_ajax_nopriv_update_cart_quantity', 'ajax_update_cart_quantity');

/**
 * 对应 JS 中的 remove_cart_item
 */
add_action('wp_ajax_remove_cart_item', 'ajax_remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'ajax_remove_cart_item');
function ajax_update_cart_quantity()
{
    $cart_item_key = $_POST['cart_item_key'];
    $new_qty = intval($_POST['new_qty']);

    if (WC()->cart->set_quantity($cart_item_key, $new_qty)) {
        // 获取更新后的总价和小计
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        $subtotal = $cart_item['line_total'];

        wp_send_json_success(array(
            'total' => WC()->cart->get_cart_total(),
            'subtotal' => number_format($subtotal, 2)
        ));
    } else {
        wp_send_json_error();
    }
    wp_die();
}

function ajax_remove_cart_item()
{
    $cart_item_key = $_POST['cart_item_key'];
    if (WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_success(array(
            'total' => WC()->cart->get_cart_total(),
            'cart_empty' => WC()->cart->is_empty()
        ));
    } else {
        wp_send_json_error();
    }
    wp_die();
}

add_action('template_redirect', 'myshop_restrict_access');

function myshop_restrict_access()
{
    // 如果用户未登录，且当前访问的不是登录页面
    if (!is_user_logged_in() && !is_page('login')) {
        // 这里的 'login' 是你稍后创建的登录页面的别名 (slug)
        wp_redirect(site_url('/login/'));
        exit;
    }
}

// 登录失败时跳转回自定义登录页并带上错误参数
add_action('wp_login_failed', 'custom_login_failed');
function custom_login_failed()
{
    $referrer = $_SERVER['HTTP_REFERER'];
    if (!empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
        // 如果是从我们的自定义 login 页面提交的，则跳回该页面
        wp_redirect(site_url('/login/?login=failed'));
        exit;
    }
}

// 开启 PHP Session 支持
add_action('init', 'register_my_session');
function register_my_session()
{
    if (!session_id()) {
        session_start();
    }
}

function check_nav_activation()
{
    // 1. 如果是登录页面，强制关闭导航标识
    if ($GLOBALS['pagenow'] === 'page-login.php') {
        $_SESSION['hlt_nav_activated'] = false;
        return;
    }
    // 判断是否在：WooCommerce产品归档页、产品分类页、或者产品标签页
    if (is_post_type_archive('product') || is_product_category() || is_product_tag()) {
        $_SESSION['hlt_nav_activated'] = true;
        if (is_product_category()) {
            $queried_object = get_queried_object();
            $_SESSION['last_category_url'] = get_term_link($queried_object);
        }
    }
}// 当进入分类页或产品归档页时，标记“导航已激活”
add_action('template_redirect', 'check_nav_activation');


add_action('wp_ajax_hlt_live_search', 'hlt_live_search_callback');
add_action('wp_ajax_nopriv_hlt_live_search', 'hlt_live_search_callback');

function hlt_live_search_callback()
{
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
    // 接收解码后的中文 Slug
    $product_cat = isset($_POST['product_cat']) ? sanitize_text_field($_POST['product_cat']) : '';
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 8,
        's' => $keyword,
    );

    // 关键修正：只有在 product_cat 真的有值时才合并 tax_query
    if (!empty($product_cat)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $product_cat,
                'operator' => 'IN', // 确保包含子分类
            ),
        );
    }

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            $results[] = array(
                'title' => get_the_title(),
                'url' => get_the_permalink(),
                'sku' => $product ? $product->get_sku() : ''
            );
        }
        wp_reset_postdata();
        wp_send_json_success($results);
    } else {
        wp_send_json_error('No products');
    }
    wp_die();
}

/**
 * 保存账户详情表单中的额外字段（电话和地址）
 */
add_action('woocommerce_save_account_details', 'hlt_save_extra_account_details', 12, 1);
function hlt_save_extra_account_details($user_id)
{
    // 保存电话
    if (isset($_POST['billing_phone'])) {
        update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }

    // 保存地址
    if (isset($_POST['billing_address_1'])) {
        update_user_meta($user_id, 'billing_address_1', sanitize_textarea_field($_POST['billing_address_1']));
    }
}

/**
 * 如果需要，可以移除姓名的必填限制（可选）
 */
add_filter('woocommerce_save_account_details_required_fields', 'hlt_remove_required_fields');
function hlt_remove_required_fields($fields)
{
    unset($fields['account_first_name']);
    unset($fields['account_last_name']);
    return $fields;
}

/**
 * 极简结算页：移除所有不必要的字段
 */
add_filter('woocommerce_checkout_fields', 'hlt_simplify_checkout_fields');
function hlt_simplify_checkout_fields($fields)
{
    // 我们只保留 billing_address_1 (地址) 和 billing_first_name (联系人)
    // 隐藏以下字段
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_email']);
    unset($fields['billing']['billing_phone']); // 如果你希望用户在这里再次确认电话，可以不删这一行

    // 隐藏订单备注
    unset($fields['order']['order_comments']);

    // 调整剩余字段的宽度为 100%
    if (isset($fields['billing']['billing_first_name']))
        $fields['billing']['billing_first_name']['class'] = array('form-row-wide');
    if (isset($fields['billing']['billing_address_1']))
        $fields['billing']['billing_address_1']['class'] = array('form-row-wide');

    return $fields;
}

/**
 * 结算页自动从用户 Meta 中读取并填充地址
 */
add_filter('woocommerce_checkout_get_value', function ($value, $input) {
    $user_id = get_current_user_id();
    if (!$user_id)
        return $value;

    switch ($input) {
        case 'billing_first_name':
            return $value ?: get_user_meta($user_id, 'first_name', true) ?: wp_get_current_user()->display_name;
        case 'billing_address_1':
            return $value ?: get_user_meta($user_id, 'billing_address_1', true);
        case 'billing_phone':
            return $value ?: get_user_meta($user_id, 'billing_phone', true);
    }
    return $value;
}, 10, 2);

/**
 * 移除结算页面的隐私政策文字
 */
add_action('wp_get_current_user', function () {
    remove_action('woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20);
});

// 或者尝试这个更直接的过滤
add_filter('woocommerce_get_privacy_policy_text', '__return_empty_string');

/**
 * 1. 彻底移除结算页底部的支付区域（紫框）、隐私提示和默认按钮
 */
// 尝试用更高的优先级执行移除
add_action('init', function () {
    remove_action('woocommerce_checkout_payment', 'woocommerce_checkout_payment', 20);
    remove_action('woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20);
});

// 强行把支付区域的 HTML 渲染结果变为空白
add_filter('woocommerce_cart_needs_payment', '__return_false');

/**
 * 2. 移除包裹/小计行（针对 Ajax 刷新后的残留）
 */
add_filter('woocommerce_get_order_item_totals', 'hlt_remove_checkout_totals_rows', 99, 3);
function hlt_remove_checkout_totals_rows($total_rows, $order, $tax_display)
{
    unset($total_rows['cart_subtotal']); // 移除小计
    unset($total_rows['shipping']);      // 移除运费/包裹
    return $total_rows;
}

/**
 * 3. 终极补丁：如果 Hook 没拦住，直接用 CSS 强行抹除（在后端注入）
 */
add_action('wp_head', function () {
    if (is_checkout()) {
        echo '<style>
            /* 抹除紫色支付框及其内部所有内容 */
            #payment, .woocommerce-checkout-payment, .woocommerce-privacy-policy-text { 
                display: none !important; 
            }
            /* 抹除包裹信息行和小计行 */
            tr.cart-subtotal, tr.shipping, .woocommerce-shipping-totals, .shipping-calculator-button-wrapper { 
                display: none !important; 
            }
        </style>';
    }
}, 999);

/**
 * 结算页逻辑优化：除了详细地址外，其他字段即便没传也不报错
 */
add_filter('woocommerce_checkout_fields', 'hlt_relax_checkout_requirements', 999);
function hlt_relax_checkout_requirements($fields)
{
    // 列表中的字段不再强制必填
    $to_relax = array('billing_city', 'billing_state', 'billing_postcode', 'billing_company', 'billing_phone', 'billing_email');

    foreach ($to_relax as $key) {
        if (isset($fields['billing'][$key])) {
            $fields['billing'][$key]['required'] = false;
        }
    }

    // 移除邮编必填（针对底层逻辑）
    add_filter('woocommerce_default_address_fields', function ($address_fields) {
        $address_fields['postcode']['required'] = false;
        return $address_fields;
    });

    return $fields;
}

/**
 * 终极大招：在验证前，强行给缺失的地址字段“补课”
 */
add_action('woocommerce_after_checkout_validation', 'hlt_force_pass_address_validation', 10, 2);
function hlt_force_pass_address_validation($data, $errors)
{
    // 如果发现了关于“地址”、“国家”或“省份”的报错，直接把报错删掉
    // 因为我们已经在前端展示并提交了这些数据
    $errors->remove('billing_address_1_required');
    $errors->remove('billing_city_required');
    $errors->remove('billing_state_required');
    $errors->remove('billing_country_required');
    $errors->remove('billing_phone_required');
}

/**
 * 强行跳过地址校验：只要前端传了详细地址，就允许下单
 */
add_action('woocommerce_after_checkout_validation', 'hlt_skip_address_validation_errors', 999, 2);
function hlt_skip_address_validation_errors($data, $errors)
{
    // 检查是否有关于地址的错误
    if ($errors->get_error_codes()) {
        // 彻底移除以下这些顽固的报错代码
        $errors->remove('billing_address_1_required');
        $errors->remove('billing_city_required');
        $errors->remove('billing_state_required');
        $errors->remove('billing_country_required');
        $errors->remove('billing_phone_required');
        $errors->remove('billing_email_required');
        $errors->remove('billing_postcode_required');

        // 某些版本 WC 的通用地址报错
        $errors->remove('address_fields_validation');
    }
}

/**
 * 确保详细地址这个字段本身不为空（这是我们唯一的底线）
 */
add_action('woocommerce_checkout_process', 'hlt_ensure_at_least_address_exists');
function hlt_ensure_at_least_address_exists()
{
    if (empty($_POST['billing_address_1'])) {
        wc_add_notice('请填写详细收货地址', 'error');
    }
}

/**
 * 针对“请提供一个地址才能继续”报错的专项拦截
 */
add_filter('woocommerce_add_error', 'hlt_remove_specific_address_error');
function hlt_remove_specific_address_error($error)
{
    if (strpos($error, '请提供一个地址才能继续') !== false) {
        return false; // 直接不显示这个错误
    }
    return $error;
}

/**
 * 强制告诉 WooCommerce 地址已经完整了
 */
add_filter('woocommerce_checkout_posted_data', 'hlt_fix_posted_state_data');
function hlt_fix_posted_state_data($data)
{
    // 确保 state 不带 CN 前缀，某些插件会误加前缀导致验证失败
    $data['billing_state'] = str_replace('CN', '', $data['billing_state']);
    return $data;
}

/**
 * 订单提交成功后，强制清空购物车
 */
add_action('woocommerce_checkout_order_processed', 'hlt_force_empty_cart_after_checkout', 10, 1);
function hlt_force_empty_cart_after_checkout($order_id)
{
    if (WC()->cart) {
        WC()->cart->empty_cart();
    }
}

/**
 * 移除“您使用的是临时密码”提示
 */
add_filter( 'woocommerce_temporary_password_notice', '__return_false' );

// 备选方案：如果上面的不生效，可以使用这个更底层的钩子
add_action( 'init', function() {
    if ( class_exists( 'WC_Shortcode_My_Account' ) ) {
        remove_action( 'woocommerce_before_my_account', array( 'WC_Shortcode_My_Account', 'check_temp_password_notification' ), 10 );
    }
}, 20 );