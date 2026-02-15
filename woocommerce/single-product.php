<?php
defined('ABSPATH') || exit;

get_header();

// 安全获取 WC_Product
$product = wc_get_product(get_the_ID());

if (!$product) {
    echo '<p style="padding:20px;">商品不存在</p>';
    get_footer();
    return;
}
?>

<main class="page-container">

    <!-- 顶部栏 -->
    <div class="header">
        <div class="back-btn" onclick="history.back()">‹</div>
        <div class="header-title">商品详情</div>
    </div>

    <!-- 图片区域 -->
    <div class="media-box">
        <div class="media-swiper">

            <!-- 主图 -->
            <div class="media-item">
                <?php echo $product->get_image('large', ['class' => 'media-content']); ?>
            </div>

            <!-- 相册 -->
            <?php foreach ($product->get_gallery_image_ids() as $image_id): ?>
                <div class="media-item">
                    <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 'media-content']); ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

    <!-- 基础信息 -->
    <div class="content-box">
        <div class="price-tag"><?php echo $product->get_price_html(); ?></div>
        <div class="p-title"><?php echo esc_html($product->get_name()); ?></div>
        <?php if ($product->get_sku()): ?>
            <div class="p-model">型号：<?php echo esc_html($product->get_sku()); ?></div>
        <?php endif; ?>
    </div>

    <!-- 参数区 -->
    <?php
    $attributes = $product->get_attributes();
    if (!empty($attributes)):
        ?>
        <div class="param-grid">
            <?php foreach ($attributes as $attribute):
                if ($attribute->is_taxonomy()) {
                    $values = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                    $value = implode(' / ', $values);
                } else {
                    $value = implode(' / ', $attribute->get_options());
                }
                ?>
                <?php if ($value): ?>
                    <div class="grid-row">
                        <div class="grid-label"><?php echo esc_html(wc_attribute_label($attribute->get_name())); ?></div>
                        <div class="grid-val"><?php echo esc_html($value); ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- 底部固定操作 + 导航 -->
    <div class="fixed-footer">

        <!-- 操作层：数量 + 加入订单 -->
        <div class="action-layer">
            <div class="qty-box">
                <div class="qty-btn" onclick="changeQty(-1)">-</div>
                <input type="number" class="qty-num" value="1" min="1">
                <div class="qty-btn" onclick="changeQty(1)">+</div>
            </div>
            <button type="button" class="add-order-btn" onclick="addOrder()">加入订单</button>
        </div>
    </div>


</main>

<div id="toast">已成功加入订单</div>

<script>
    // 数量增减逻辑
    function changeQty(n) {
        let el = document.querySelector('.qty-num');
        let currentVal = parseInt(el.value) || 1;
        el.value = Math.max(1, currentVal + n);
    }

    // 加入订单逻辑
    function addOrder() {
        alert("点击成功！正在尝试加入订单...");
        // 关键点 1：确保 jQuery 已加载，否则提示
        if (typeof jQuery === 'undefined') {
            alert('系统加载中，请稍后重试...');
            return;
        }

        const $ = jQuery; // 关键点 2：在函数内部重新定义 $，防止冲突
        const product_id = <?php echo $product->get_id(); ?>;
        const qty = document.querySelector('.qty-num').value;

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'handle_add_to_order',
                product_id: product_id,
                quantity: qty
            },
            beforeSend: function () {
                $('.add-order-btn').text('处理中...').prop('disabled', true);
            },
            success: function (response) {
                console.log(response); // 在控制台查看返回
                if (response.success) {
                    const toast = document.getElementById('toast');
                    toast.style.display = 'block';
                    // 动态更新提示文字
                    toast.textContent = response.data.message || '已成功加入订单';

                    setTimeout(() => {
                        toast.style.display = 'none';
                        $('.add-order-btn').text('加入订单').prop('disabled', false);
                    }, 2000);
                } else {
                    alert('添加失败：' + (response.data ? response.data.message : '未知错误'));
                    $('.add-order-btn').text('加入订单').prop('disabled', false);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('网络请求失败，请检查设置');
                $('.add-order-btn').text('加入订单').prop('disabled', false);
            }
        });
    }
</script>

<?php get_footer(); ?>