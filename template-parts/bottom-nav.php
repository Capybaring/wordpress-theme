<?php
/**
 * 底部导航栏模板
 */

// 1. 获取当前页面状态用于高亮
$current = '';
if (is_front_page())
    $current = 'home';
elseif (is_shop() || is_product_category() || is_archive()) // 增加分类页判断
    $current = 'shop';
elseif (is_cart())
    $current = 'cart';
elseif (is_account_page())
    $current = 'account';

// 2. 方案一核心逻辑判断
$should_show_nav = false;

if (is_user_logged_in() && !is_page('login')) {
    if (!is_front_page()) {
        // 只要不是首页，就显示
        $should_show_nav = true;
    } elseif (isset($_SESSION['hlt_nav_activated']) && $_SESSION['hlt_nav_activated'] === true) {
        // 如果是首页，但 Session 标记已激活，也显示
        $should_show_nav = true;
    }
}
?>

<?php if ($should_show_nav): ?>
    <div class="fixed-footer">

        <?php if (is_product()): ?>
            <div class="action-layer">
                <div class="qty-box">
                    <div class="qty-btn"
                        onclick="var qty = jQuery('.qty-num'); var val = parseInt(qty.val()); if(val>1) qty.val(val-1);">-</div>
                    <input type="number" class="qty-num" value="1" readonly>
                    <div class="qty-btn" onclick="var qty = jQuery('.qty-num'); var val = parseInt(qty.val()); qty.val(val+1);">
                        +</div>
                </div>
                <button class="add-order-btn">加入订单</button>
            </div>
        <?php endif; ?>

        <div class="menu-layer">
            <a class="menu-item <?php echo $current === 'home' ? 'active' : ''; ?>" href="<?php echo home_url(); ?>">
                <span>首页</span>
            </a>

            <?php
            // 动态获取商品页链接：优先使用 Session 记录的分类，否则使用默认商店页
            $shop_link = (isset($_SESSION['last_category_url'])) ? $_SESSION['last_category_url'] : get_permalink(wc_get_page_id('shop'));
            ?>
            <a class="menu-item <?php echo $current === 'shop' ? 'active' : ''; ?>"
                href="<?php echo esc_url($shop_link); ?>">
                <span>商品</span>

                <a class="menu-item <?php echo $current === 'cart' ? 'active' : ''; ?>"
                    href="<?php echo wc_get_cart_url(); ?>">
                    <span>订单</span>
                </a>

                <a class="menu-item <?php echo $current === 'account' ? 'active' : ''; ?>"
                    href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>">
                    <span>我的</span>
                </a>
        </div>

    </div>
<?php endif; ?>