<?php
/**
 * View Order - Minimalist Pro Edition
 */

defined('ABSPATH') || exit;

$notes = $order->get_customer_order_notes();
?>

<div class="order-status-card title">
	<div class="status-icon">
		<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
			stroke-linecap="round" stroke-linejoin="round">
			<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
			<polyline points="22 4 12 14.01 9 11.01"></polyline>
		</svg>
	</div>
	<div class="status-content">
		<p class="status-main-text">
			<?php echo wc_get_order_status_name($order->get_status()); ?>
		</p>
		<p class="status-sub-text">
			<?php
			printf(
				/* translators: 1: order number 2: order date */
				esc_html__('订单号 #%1$s · 下单于 %2$s', 'woocommerce'),
				'<span class="highlight">' . $order->get_order_number() . '</span>',
				'<span class="highlight">' . wc_format_datetime($order->get_date_created()) . '</span>'
			);
			?>
		</p>
	</div>
</div>

<?php if ($notes): ?>
	<div class="order-section">
		<h2 class="section-title"><?php esc_html_e('Order updates', 'woocommerce'); ?></h2>
		<div class="woocommerce-OrderUpdates-container">
			<?php foreach ($notes as $note): ?>
				<div class="update-node">
					<div class="node-dot"></div>
					<div class="node-content">
						<span class="node-time"><?php echo date_i18n('Y-m-d H:i', strtotime($note->comment_date)); ?></span>
						<div class="node-text">
							<?php echo wpautop(wptexturize($note->comment_content)); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>

<div class="order-details-wrapper">
	<?php do_action('woocommerce_view_order', $order_id); ?>
</div>

<style>
	/* 基础重置 */
	.order-status-card {
		display: flex;
		align-items: center;
		background: #fff !important;
		padding: 20px !important;
		margin: 0 0 25px 0 !important;
		border-radius: 12px !important;
		border-left: 5px solid #007aff !important;
		box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
	}

	.status-icon {
		background: #eef6ff;
		color: #007aff;
		width: 48px;
		height: 48px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 15px;
		flex-shrink: 0;
	}

	.status-main-text {
		font-size: 18px !important;
		font-weight: 700 !important;
		color: #1d1d1f !important;
		margin: 0 !important;
	}

	.status-sub-text {
		font-size: 13px !important;
		color: #86868b !important;
		margin: 4px 0 0 0 !important;
	}

	.status-sub-text .highlight {
		color: #333;
		font-weight: 500;
	}

	/* 订单日志美化 - 变成时间轴风格 */
	.order-section {
		margin-top: 30px;
	}

	.section-title {
		font-size: 16px;
		font-weight: 600;
		margin-bottom: 15px;
		padding-left: 5px;
	}

	.woocommerce-OrderUpdates-container {
		background: #fff;
		padding: 20px;
		border-radius: 12px;
		border: 1px solid #f2f2f2;
	}

	.update-node {
		position: relative;
		padding-left: 20px;
		padding-bottom: 15px;
		border-left: 1px solid #e5e5e5;
	}

	.update-node:last-child {
		border-left-color: transparent;
	}

	.node-dot {
		position: absolute;
		left: -5px;
		top: 5px;
		width: 9px;
		height: 9px;
		background: #007aff;
		border-radius: 50%;
		border: 2px solid #fff;
	}

	.node-time {
		font-size: 12px;
		color: #999;
		display: block;
		margin-bottom: 3px;
	}

	.node-text p {
		font-size: 14px;
		color: #444;
		margin: 0;
	}

	/* 适配 WooCommerce 原生详情表格 */
	.order-details-wrapper .shop_table {
		width: 100% !important;
		border-collapse: separate !important;
		border-spacing: 0 !important;
		border: none !important;
		margin-top: 20px;
	}

	.order-details-wrapper .shop_table thead {
		display: none;
	}

	/* 极简掉表头 */

	.order-details-wrapper .shop_table td {
		padding: 15px 0 !important;
		border-bottom: 1px solid #f5f5f7 !important;
		font-size: 14px;
	}

	.order-details-wrapper .shop_table tr:last-child td {
		border-bottom: none !important;
	}

	/* 优化底部总价区域 */
	.order-details-wrapper .shop_table tfoot th {
		text-align: left;
		font-weight: normal;
		color: #666;
	}

	.order-details-wrapper .shop_table tfoot td {
		text-align: right;
		font-weight: 600;
		color: #1d1d1f;
	}

	/* 针对地址栏的美化 */
	.woocommerce-customer-details {
		display: grid;
		grid-template-columns: 1fr;
		gap: 20px;
		margin-top: 30px;
	}

	.woocommerce-column--billing-address {
		background: #fbfbfd;
		padding: 20px;
		border-radius: 12px;
	}

	address {
		font-style: normal;
		line-height: 1.8;
		color: #555;
		font-size: 13px;
	}

	/* 1. 彻底抹除商品名称的默认链接样式 */
	.order-details-wrapper .shop_table td a {
		color: #1d1d1f !important;
		/* 强制使用苹果黑 */
		text-decoration: none !important;
		/* 去掉下划线 */
		font-weight: 600 !important;
		transition: color 0.2s ease;
		display: inline-block;
	}

	/* 2. 鼠标悬停或触摸时的交互感 */
	.order-details-wrapper .shop_table td a:hover {
		color: #007aff !important;
		/* 悬停时变回品牌蓝，表示可点 */
		text-decoration: none !important;
	}

	/* 3. 针对商品数量 (× 1) 的样式优化 */
	.order-details-wrapper .product-quantity {
		color: #86868b !important;
		font-weight: normal !important;
		margin-left: 5px;
		font-size: 13px;
	}

	/* 4. 如果表格里有商品属性（如：颜色：红色），也一并美化 */
	.wc-item-meta {
		margin-top: 5px !important;
		padding: 0 !important;
		list-style: none !important;
		font-size: 12px !important;
		color: #86868b !important;
	}

	.wc-item-meta li {
		display: inline-block;
		margin-right: 10px;
	}

	.wc-item-meta li strong {
		font-weight: normal !important;
	}

	/* 5. 整体表格行高微调，避免拥挤 */
	.order-details-wrapper .shop_table tr {
		transition: background-color 0.2s ease;
	}

	/* 增加点击反馈，让用户知道这一行是有结构的 */
	.order-details-wrapper .shop_table tbody tr:active {
		background-color: #f5f5f7;
	}
</style>