<?php
defined('ABSPATH') || exit;
get_header();
?>

<main class="page-container">

    <!-- 顶部导航 -->
    <div class="header">
        <div class="back-btn" onclick="history.back()">‹</div>
        <div class="header-title">购物车</div>
    </div>

    <!-- 购物车列表 -->
    <div class="cart-page">
        <?php if (WC()->cart->get_cart_contents_count() > 0): ?>

            <div class="cart-grid">
                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                    $product = $cart_item['data'];
                    $quantity = $cart_item['quantity'];
                    ?>
                    <div class="cart-item">
                        <div class="item-img">
                            <?php echo $product->get_image('thumbnail'); ?>
                        </div>
                        <div class="item-info">
                            <div class="item-name"><?php echo esc_html($product->get_name()); ?></div>
                            <?php if ($product->get_sku()): ?>
                                <div class="item-sku">型号: <?php echo esc_html($product->get_sku()); ?></div>
                            <?php endif; ?>
                            <div class="item-price">¥ <?php echo $product->get_price(); ?></div>

                            <div class="item-qty">
                                <button class="qty-btn" onclick="updateQty('<?php echo $cart_item_key; ?>', -1)">-</button>
                                <input type="number" value="<?php echo $quantity; ?>" readonly />
                                <button class="qty-btn" onclick="updateQty('<?php echo $cart_item_key; ?>', 1)">+</button>
                            </div>
                        </div>
                        <div class="item-subtotal">
                            ¥ <?php echo $product->get_price() * $quantity; ?>
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
            总计: ¥ <?php echo WC()->cart->get_cart_total(); ?>
        </div>
        <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">去结算</a>
    </div>

</main>

<?php get_template_part('template-parts/bottom-nav'); ?>

<script>
    // 例如，更新购物车图标上的数量
    // 获取并更新购物车内容
    function updateCart() {
        jQuery.ajax({
            url: '<?php echo WC()->cart->get_cart_url(); ?>', // 获取购物车的 URL
            method: 'GET',
            success: function (response) {
                // 获取购物车数量
                const cartCount = jQuery('.cart-count'); // 假设页面中有 .cart-count 元素显示数量
                cartCount.text(response.cart_item_count);

                // 获取购物车的总金额
                const totalPrice = jQuery('.total-price'); // 假设页面有 .total-price 显示总金额
                totalPrice.text('总计: ¥ ' + response.cart_total);
            }
        });
    }



    function removeItem(cartKey) {
        // 这里可以写AJAX请求移除商品
        console.log('Remove:', cartKey);
    }
</script>

<?php get_footer(); ?>