<?php
defined('ABSPATH') || exit;
do_action('woocommerce_before_edit_account_form');

// 获取当前用户的账单信息
$user_id = get_current_user_id();
$billing_phone = get_user_meta($user_id, 'billing_phone', true);
$billing_address = get_user_meta($user_id, 'billing_address_1', true);
?>

<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

	<?php do_action('woocommerce_edit_account_form_start'); ?>

	<div class="form-row-static">
		<label>账户名称</label>
		<div class="static-value"><?php echo esc_html($user->display_name); ?></div>
		<input type="hidden" name="account_display_name" value="<?php echo esc_attr($user->display_name); ?>" />
		<small style="color: #999; font-size: 12px;">注：账户名称暂不支持自主修改</small>
	</div>


	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="billing_phone">联系电话&nbsp;<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone"
			id="billing_phone" value="<?php echo esc_attr($billing_phone); ?>" placeholder="请输入联系电话" />
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="billing_address_1">收货地址&nbsp;<span class="required">*</span></label>
		<textarea name="billing_address_1" id="billing_address_1"
			class="woocommerce-Input woocommerce-Input--text input-text" rows="3"
			placeholder="请输入详细收货地址"><?php echo esc_textarea($billing_address); ?></textarea>
	</p>

	<div class="clear"></div>

	<?php do_action('woocommerce_edit_account_form'); ?>

	<p style="margin-top: 20px;">
		<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
		<button type="submit" class="woocommerce-Button button" name="save_account_details" value="保存更改">保存更改</button>
		<input type="hidden" name="action" value="save_account_details" />
	</p>

	<?php do_action('woocommerce_edit_account_form_end'); ?>
</form>

<style>
	.form-row-static {
		margin-bottom: 20px;
		padding-bottom: 10px;
		border-bottom: 1px solid #f0f0f0;
	}

	.form-row-static label {
		display: block;
		margin-bottom: 5px;
		font-weight: bold;
		color: #666;
	}

	.static-value {
		font-size: 16px;
		color: #333;
		padding: 5px 0;
	}

	/* 适配你之前的按钮样式 */
	.woocommerce-Button {
		width: 100%;
		height: 44px;
		background: var(--primary, #007aff);
		color: #fff;
		border: none;
		border-radius: 8px;
		font-weight: bold;
	}
</style>

<?php do_action('woocommerce_after_edit_account_form'); ?>