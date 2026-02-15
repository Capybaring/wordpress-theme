<?php
defined('ABSPATH') || exit;
$endpoint = WC()->query->get_current_endpoint();

if (empty($endpoint)): ?>
	<div class="account-main-menu">
		<div class="user-info-card">
			<h2><?php echo wp_get_current_user()->display_name; ?></h2>
		</div>

		<div class="menu-list-group">
			<a href="<?php echo wc_get_endpoint_url('edit-account'); ?>" class="menu-list-item">
				<span class="item-left"><span class="icon">👤</span> 账户详情</span>
				<span class="item-arrow">›</span>
			</a>
			<a href="<?php echo wc_get_endpoint_url('orders'); ?>" class="menu-list-item">
				<span class="item-left"><span class="icon">📦</span> 我的订单</span>
				<span class="item-arrow">›</span>
			</a>
			<a href="<?php echo wc_get_endpoint_url('edit-address'); ?>" class="menu-list-item">
				<span class="item-left"><span class="icon">📍</span> 个人地址</span>
				<span class="item-arrow">›</span>
			</a>
			<a href="<?php echo wc_logout_url(); ?>" class="menu-list-item logout">
				<span class="item-left"><span class="icon">🚪</span> 退出登录</span>
				<span class="item-arrow">›</span>
			</a>
		</div>
	</div>
<?php else: ?>
	<div class="sub-page-wrapper woocommerce">
		<div class="sub-header">
			<a href="<?php echo wc_get_page_permalink('myaccount'); ?>" class="back-link">‹</a>
			<span class="header-title">
				<?php
				if ($endpoint == 'orders')
					echo '我的订单';
				elseif ($endpoint == 'view-order') // 增加这一行，识别查看详情页
					echo '订单详情';
				elseif ($endpoint == 'edit-account')
					echo '账户详情';
				elseif ($endpoint == 'edit-address')
					echo '地址管理';
				?>
			</span>
		</div>
		<div class="sub-content-body">
			<?php do_action('woocommerce_account_content'); ?>
		</div>
	</div>
<?php endif; ?>
<?php
// 公共底部导航
get_template_part('template-parts/bottom-nav');
?>