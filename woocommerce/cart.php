<?php
defined('ABSPATH') || exit;
get_header();
?>

<main class="page-container">

    <!-- 顶部导航 -->
    <div class="header">
        <div class="header-title">购物车</div>
    </div>

    <!-- 购物车列表 -->
    <div class="cart-page">
        <?php if (WC()->cart->get_cart_contents_count() > 0): ?>

            <div class="cart-grid">
                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                    $product = $cart_item['data'];
                    $quantity = $cart_item['quantity'];
                    // 获取商品链接
                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                    ?>
                    <div class="cart-item">
                        <div class="item-img">
                            <?php if ($product_permalink): ?>
                                <a href="<?php echo esc_url($product_permalink); ?>">
                                    <?php echo $product->get_image('thumbnail'); ?>
                                </a>
                            <?php else: ?>
                                <?php echo $product->get_image('thumbnail'); ?>
                            <?php endif; ?>
                        </div>

                        <div class="item-info">
                            <div class="item-name">
                                <?php if ($product_permalink): ?>
                                    <a href="<?php echo esc_url($product_permalink); ?>"
                                        style="text-decoration: none; color: inherit;">
                                        <?php echo esc_html($product->get_name()); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($product->get_name()); ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($product->get_sku()): ?>
                                <div class="item-sku">型号:
                                    <?php echo esc_html($product->get_sku()); ?>
                                </div>
                            <?php endif; ?>

                            <div class="item-qty">
                                <button class="qty-btn" onclick="updateQty('<?php echo $cart_item_key; ?>', -1)">-</button>
                                <input type="number" value="<?php echo $quantity; ?>" readonly />
                                <button class="qty-btn" onclick="updateQty('<?php echo $cart_item_key; ?>', 1)">+</button>
                            </div>
                        </div>

                        <div class="item-subtotal">
                            <div class="item-subtotal-price">
                                ¥
                                <?php echo number_format($product->get_price() * $quantity, 2); ?>
                            </div>
                            <button class="remove-btn" onclick="removeItem('<?php echo $cart_item_key; ?>')">删除</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p style="padding:20px; text-align:center;">购物车为空</p>
        <?php endif; ?>
    </div>

    <!-- 底部结算栏 -->
    <div class="cart-footer">
        <div class="total-price">
            总计: <?php echo WC()->cart->get_cart_total(); ?>
        </div>
        <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">去结算</a>
    </div>

</main>

<?php get_template_part('template-parts/bottom-nav'); ?>

<script>
    /**
     * 更新商品数量
     */
    function updateQty(cartKey, delta) {
        const item = jQuery(`button[onclick*="${cartKey}"]`).closest('.cart-item');
        const input = item.find('input');
        const subtotalElem = item.find('.item-subtotal'); // 找到显示小计的元素
        let currentVal = parseInt(input.val());
        let newVal = currentVal + delta;

        if (newVal < 1) return;

        // 预设 UI 变化（让用户感觉很快）
        input.val(newVal);
        item.css('opacity', '0.6');

        jQuery.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'update_cart_quantity',
                cart_item_key: cartKey,
                new_qty: newVal
            },
            success: function (response) {
                item.css('opacity', '1');
                if (response.success) {
                    if (response.data.total) {
                        jQuery('.total-price').html('总计: ' + response.data.total);
                    }

                    // 【关键点】：构造和我们 HTML 结构完全一致的代码
                    if (response.data.subtotal) {
                        const removeBtnHtml = subtotalElem.find('.remove-btn').prop('outerHTML');

                        // 重新填入带有 item-subtotal-price 的结构，这样 CSS 才能继续生效
                        subtotalElem.html(
                            '<div class="item-subtotal-price">¥ ' + response.data.subtotal + '</div>' +
                            removeBtnHtml
                        );
                    }
                } else {
                    alert('库存不足或更新失败');
                    location.reload();
                }
            }
        });
    }

    /**
     * 移除商品
     */
    function removeItem(cartKey) {
        if (!confirm('确定要删除吗？')) return;
        const item = jQuery(`button[onclick*="${cartKey}"]`).closest('.cart-item');

        item.fadeOut(300, function () {
            jQuery.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'remove_cart_item',
                    cart_item_key: cartKey
                },
                success: function (response) {
                    if (response.success) {
                        if (response.data.cart_empty) {
                            location.reload(); // 购物车空了就刷新显示空状态
                        } else {
                            jQuery('.total-price').html('总计: ' + response.data.total);
                            item.remove();
                        }
                    }
                }
            });
        });
    }
</script>
<?php get_footer(); ?>

<style>
    /* 页面基础背景 */
    body {
        background-color: #f7f7f7;
    }

    .cart-page {
        padding: 12px;
        padding-bottom: 140px;
        /* 为底部结算和导航留出空间 */
    }

    .cart-item {
        display: flex;
        background: #fff;
        border-radius: 12px;
        margin-bottom: 12px;
        padding: 12px;
        gap: 12px;
        align-items: stretch;
        /* 让左右高度拉齐 */
    }

    .item-img img {
        width: 85px;
        height: 85px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
    }

    /* 中间信息栏 */
    .item-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-width: 0;
        /* 防止溢出 */
    }

    .item-name {
        font-weight: 700;
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 4px;
    }

    .item-sku {
        font-size: 12px;
        color: #999;
        margin-bottom: 8px;
    }

    /* 数量控制区：修复框过长和不协调问题 */
    .item-qty {
        display: flex;
        align-items: center;
        border-radius: 6px;
        width: fit-content;
        /* 宽度自适应内容 */
        overflow: hidden;
    }

    .qty-btn {
        width: 30px;
        height: 30px;
        background: #f8f9fa;
        border: none;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .item-qty input {
        width: 35px;
        /* 固定宽度，解决过长问题 */
        height: 25px;
        text-align: center;
        border: 1px solid #eee;
        border-radius: 10px;
        font-size: 13px;
        background: #fff;
        /* 移除 Chrome/Safari/Edge 的上下箭头 */
        -webkit-appearance: none;
        margin: 0;
    }

    /* 移除 Firefox 的上下箭头 */
    .item-qty input[type=number] {
        -moz-appearance: textfield;
    }

    /* 右侧价格与删除区：重新布局 */
    /* 右侧价格与删除区 */
    .item-subtotal {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        /* 水平靠右对齐 */
        justify-content: center;
        /* 核心：让所有内容先默认垂直居中 */
        flex-shrink: 0;
        padding-left: 5px;
        min-height: 85px;
        /* 确保高度与图片一致，才有空间居中 */
        font-family: Arial, sans-serif;
        font-size: 15px;
        color: #333;
        position: relative;
        /* 为删除按钮绝对定位做准备，如果高度不够的话 */
    }

    /* 价格样式 */
    .item-subtotal-price {
        flex-grow: 1;
        /* 占据剩余空间 */
        display: flex;
        align-items: center;
        /* 真正实现价格在剩余空间中垂直居中 */
        font-weight: bold;
    }

    /* 删除按钮样式 */
    .remove-btn {
        margin-top: auto;
        /* 关键：自动外边距会将按钮推向底部 */
        background: #fff0f0;
        border: none;
        color: #ff4d4f;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 4px;
        cursor: pointer;
    }

    .remove-btn:active {
        opacity: 0.6;
    }

    /* 底部结算优化 */
    .cart-footer {
        position: fixed;
        bottom: 70px;
        /* 避开底部导航 */
        left: 0;
        right: 0;
        z-index: 99;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    }

    .total-price {
        font-size: 16px;
        font-weight: bold;
    }

    .checkout-btn {
        background: #007aff;
        color: #fff;
        padding: 12px 24px;
        border-radius: 25px;
        /* 圆角按钮 */
        font-weight: bold;
        text-decoration: none;
    }
</style>