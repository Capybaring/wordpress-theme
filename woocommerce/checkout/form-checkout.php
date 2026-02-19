<?php
if (!defined('ABSPATH'))
    exit;

if (!isset($checkout)) {
    $checkout = WC()->checkout();
}
get_header();
?>

<main class="page-container checkout-page">
    <div class="header">
        <div class="header-title">确认订单</div>
    </div>

    <div class="woocommerce">
        <form name="checkout" method="post" class="checkout woocommerce-checkout"
            action="<?php echo esc_url(wc_get_checkout_url()); ?>">

            <div class="checkout-section" id="customer_details">
                <h3 class="section-title">收货信息</h3>

                <div class="static-checkout-info">
                    <?php
                    // 获取原始代号
                    $country_code = $checkout->get_value('billing_country') ?: 'CN';
                    $state_code = $checkout->get_value('billing_state');
                    $clean_state = str_replace('CN', '', $state_code);
                    $city_name = $checkout->get_value('billing_city');

                    // 翻译代号为中文名
                    $countries = WC()->countries->get_countries();
                    $country_name = isset($countries[$country_code]) ? $countries[$country_code] : $country_code;

                    $states = WC()->countries->get_states($country_code);
                    $state_name = (isset($states[$state_code])) ? $states[$state_code] : $state_code;
                    ?>

                    <div class="info-row">
                        <span class="label">联系人：</span>
                        <span class="value"><?php echo $checkout->get_value('billing_first_name'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">公司：</span>
                        <span class="value"><?php echo $checkout->get_value('billing_company'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">地区：</span>
                        <span
                            class="value"><?php echo esc_html($country_name . ' ' . $state_name . ' ' . $city_name); ?></span>
                    </div>

                    <div class="info-row address-edit">
                        <label for="billing_address_1" class="label">详细地址：</label>
                        <textarea name="billing_address_1" id="billing_address_1" class="input-text"
                            rows="2"><?php echo $checkout->get_value('billing_address_1'); ?></textarea>
                    </div>
                </div>

                <input type="hidden" name="billing_first_name"
                    value="<?php echo esc_attr($checkout->get_value('billing_first_name')); ?>">
                <input type="hidden" name="billing_last_name"
                    value="<?php echo esc_attr($checkout->get_value('billing_last_name') ?: '用户'); ?>">
                <input type="hidden" name="billing_phone"
                    value="<?php echo esc_attr($checkout->get_value('billing_phone')); ?>">
                <input type="hidden" name="billing_company"
                    value="<?php echo esc_attr($checkout->get_value('billing_company')); ?>">
                <input type="hidden" name="billing_country" value="<?php echo esc_attr($country_code); ?>">
                <input type="hidden" name="billing_state" value="<?php echo esc_attr($clean_code); ?>">
                <input type="hidden" name="billing_city" value="<?php echo esc_attr($city_name); ?>">
                <input type="hidden" name="billing_email"
                    value="<?php echo esc_attr($checkout->get_value('billing_email')); ?>">
                <input type="hidden" name="billing_postcode" value="000000">
            </div>

            <div class="checkout-section">
                <h3 class="section-title">订单明细</h3>
                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>
            </div>

            <div class="checkout-footer">
                <div class="total">实付: <?php wc_cart_totals_order_total_html(); ?></div>
                <button type="submit" class="place-order-btn" name="woocommerce_checkout_place_order"
                    id="place_order">立即下单</button>
                <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
            </div>
        </form>
    </div>
</main>

<style>
    .static-checkout-info {
        font-size: 14px;
        color: #333;
        line-height: 2;
    }

    .info-row {
        display: flex;
        border-bottom: 1px solid #f9f9f9;
        padding: 8px 0;
    }

    .info-row .label {
        color: #888;
        width: 80px;
        flex-shrink: 0;
    }

    .address-edit {
        flex-direction: column;
        border-bottom: none;
    }

    .address-edit textarea {
        margin-top: 8px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
    }

    /* 隐藏所有多余的 WooCommerce 结算提示和标题 */
    .woocommerce-billing-fields h3,
    #order_review_heading,
    .woocommerce-additional-fields {
        display: none !important;
    }

    .checkout-page {
        padding-bottom: 100px;
        background: #f8fafc;
    }

    .checkout-section {
        background: #fff;
        margin: 12px;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .section-title {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 12px;
        font-weight: 600;
    }

    /* 结算按钮 */
    .checkout-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        z-index: 1000;
    }

    .place-order-btn {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 15px;
    }

    .order-total-display .amount {
        color: #2563eb;
        font-weight: 800;
        font-size: 18px;
    }

    /* 修正输入框样式 */
    .form-row input {
        width: 100% !important;
        border: 1px solid #e2e8f0 !important;
        padding: 10px !important;
        border-radius: 8px !important;
    }
</style>

<?php get_footer(); ?>