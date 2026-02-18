<?php
$current = '';
if (is_front_page())
    $current = 'home';
elseif (is_shop())
    $current = 'shop';
elseif (is_cart())
    $current = 'cart';
elseif (is_account_page())
    $current = 'account';
?>

<div class="fixed-footer">

    <!-- 操作层（仅商品详情页显示） -->
    <?php if (is_product()): ?>
        <div class="action-layer">
            <div class="qty-box">
                <div class="qty-btn">-</div>
                <input type="number" class="qty-num" value="1" readonly>
                <div class="qty-btn">+</div>
            </div>
            <button class="add-order-btn">加入订单</button>
        </div>
    <?php endif; ?>

    <!-- 菜单层 -->
    <div class="menu-layer">
        <a class="menu-item <?php echo $current === 'home' ? 'active' : ''; ?>" href="/">
            </span><span>首页</span>
        </a>

        <a class="menu-item <?php echo $current === 'shop' ? 'active' : ''; ?>" href="/shop">
            </span><span>商品</span>
        </a>

        <a class="menu-item <?php echo $current === 'cart' ? 'active' : ''; ?>" href="/cart">
            </span><span>订单</span>
        </a>

        <a class="menu-item <?php echo $current === 'account' ? 'active' : ''; ?>" href="/my-account">
            </span><span>我的</span>
        </a>
    </div>

</div>