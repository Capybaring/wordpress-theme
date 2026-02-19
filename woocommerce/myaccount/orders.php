<?php
/**
 * Orders - Minimalist Card Layout
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders); ?>

<?php if ($has_orders): ?>

	<div class="minimal-orders-container">
		<?php
		foreach ($customer_orders->orders as $customer_order) {
			$order = wc_get_order($customer_order);
			$item_count = $order->get_item_count() - $order->get_item_count_refunded();
			$status_slug = $order->get_status();
			$status_name = wc_get_order_status_name($status_slug);
			?>

			<div class="order-card status-<?php echo esc_attr($status_slug); ?>">
				<div class="card-header">
					<span class="order-id">#<?php echo $order->get_order_number(); ?></span>
					<span class="order-status-badge"><?php echo esc_html($status_name); ?></span>
				</div>

				<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="card-body">
					<div class="info-group">
						<time
							class="order-time"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>
						<div class="order-meta">
							<?php echo wp_kses_post(sprintf(_n('%2$s 个商品', '%2$s 个商品', $item_count, 'woocommerce'), '', $item_count)); ?>
						</div>
					</div>
					<div class="order-total-price">
						<?php echo $order->get_formatted_order_total(); ?>
					</div>
				</a>

				<?php
				$actions = wc_get_account_orders_actions($order);
				if (!empty($actions)): ?>
					<div class="card-footer">
						<?php foreach ($actions as $key => $action): ?>
							<a href="<?php echo esc_url($action['url']); ?>"
								class="action-btn <?php echo sanitize_html_class($key); ?>">
								<?php echo esc_html($action['name']); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

		<?php } ?>
	</div>

	<?php if (1 < $customer_orders->max_num_pages): ?>
		<div class="custom-pagination">
			<?php if (1 !== $current_page): ?>
				<a class="prev" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>">上一页</a>
			<?php endif; ?>
			<?php if (intval($customer_orders->max_num_pages) !== $current_page): ?>
				<a class="next" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>">下一页</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else: ?>
	<div class="empty-orders">
		<p>暂无订单记录</p>
		<a class="button"
			href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">去逛逛</a>
	</div>
<?php endif; ?>

<style>
	/* 容器设置 */
	.minimal-orders-container {
		display: flex;
		flex-direction: column;
		gap: 16px;
		background: transparent;
	}

	/* 卡片基础样式 */
	.order-card {
		background: #fff;
		border-radius: 16px;
		padding: 20px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
		border: 1px solid #f1f1f1;
		transition: transform 0.2s ease;
	}

	.order-card:active {
		transform: scale(0.98);
	}

	/* 头部：订单号与徽章 */
	.card-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 12px;
	}

	.order-id {
		font-weight: 700;
		color: #1d1d1f;
		font-size: 15px;
	}

	.order-status-badge {
		font-size: 12px;
		padding: 4px 10px;
		border-radius: 6px;
		font-weight: 600;
		background: #f5f5f7;
		color: #86868b;
	}

	/* 不同状态的颜色区分 */
	.status-processing .order-status-badge {
		background: #eef6ff;
		color: #007aff;
	}

	.status-completed .order-status-badge {
		background: #eafff5;
		color: #24b47e;
	}

	.status-cancelled .order-status-badge {
		background: #fff1f0;
		color: #ff4d4f;
	}

	/* 主体：跳转详情区域 */
	.card-body {
		display: flex;
		justify-content: space-between;
		align-items: center;
		text-decoration: none !important;
		padding: 10px 0;
	}

	.order-time {
		display: block;
		font-size: 13px;
		color: #86868b;
		margin-bottom: 4px;
	}

	.order-meta {
		font-size: 13px;
		color: #1d1d1f;
	}

	.order-total-price {
		font-size: 18px;
		font-weight: 700;
		color: #000;
	}

	/* 底部按钮 */
	.card-footer {
		margin-top: 15px;
		padding-top: 15px;
		border-top: 1px solid #f5f5f7;
		display: flex;
		gap: 10px;
		justify-content: flex-end;
	}

	.action-btn {
		text-decoration: none !important;
		font-size: 13px;
		padding: 8px 16px;
		border-radius: 8px;
		font-weight: 600;
		transition: all 0.2s;
	}

	/* “查看”按钮样式 */
	.action-btn.view {
		background: #007aff;
		color: #fff !important;
	}

	/* 其他按钮（如取消、付款）样式 */
	.action-btn:not(.view) {
		background: #f5f5f7;
		color: #1d1d1f !important;
	}

	/* 分页样式 */
	.custom-pagination {
		margin-top: 30px;
		display: flex;
		justify-content: center;
		gap: 20px;
	}

	.custom-pagination a {
		text-decoration: none;
		color: #007aff;
		font-weight: 600;
		font-size: 14px;
	}

	/* 隐藏原生表格头 */
	.woocommerce-orders-table thead {
		display: none;
	}
</style>