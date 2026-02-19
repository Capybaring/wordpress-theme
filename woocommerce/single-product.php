<?php
/**
 * 广州海陆通 - 商品详情页 (single-product.php)
 * 逻辑：顶部导航 + 商品展示 + 核心参数 + 底部纯操作栏
 */

defined('ABSPATH') || exit;

get_header();

$product = wc_get_product(get_the_ID());

if (!$product) {
    echo '<div style="padding:50px; text-align:center;">商品不存在</div>';
    get_footer();
    return;
}
?>

<main class="page-container detail-page">

    <div class="header">
        <div class="back-btn" onclick="history.back()">‹</div>
        <div class="header-title">商品详情</div>
    </div>

    <div class="media-slider">
        <?php
        // 获取自定义字段中的视频地址
        $video_url = get_post_meta(get_the_ID(), '视频链接', true);
        ?>

        <?php if ($video_url): ?>
            <div class="media-item video-item">
                <video src="<?php echo esc_url($video_url); ?>"
                    poster="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>" controls playsinline muted
                    loop style="width:100%; height:100%; object-fit:contain; background:#000;">
                </video>
                <div class="slider-hint">右滑查看更多图片
                </div>
            </div>
        <?php endif; ?>

        <div class="media-item">
            <?php echo $product->get_image('large'); ?>
            <?php if ($video_url): ?>
                <div class="media-tag">1 / <?php echo (count($product->get_gallery_image_ids()) + 1); ?></div>
            <?php endif; ?>
        </div>

        <?php
        $attachment_ids = $product->get_gallery_image_ids();
        $total_pics = count($attachment_ids) + 1; // 相册 + 主图
        if ($attachment_ids):
            foreach ($attachment_ids as $key => $attachment_id): ?>
                <div class="media-item">
                    <?php echo wp_get_attachment_image($attachment_id, 'large'); ?>
                    <div class="media-tag"><?php echo ($key + 2) . ' / ' . $total_pics; ?></div>
                </div>
            <?php endforeach;
        endif; ?>
    </div>

    <div class="content-box">
        <div class="price-tag">
            <?php echo $product->get_price() ? '¥ ' . $product->get_price() : '价格电询'; ?>
        </div>
        <div class="p-title"><?php echo esc_html($product->get_name()); ?></div>
    </div>

    <div class="param-grid-v2">
        <span class="sn-tag">货号：
            <?php echo $product->get_sku() ? esc_html($product->get_sku()) : '暂无信息'; ?>
        </span>

        <div class="grid">
            <div class="item">
                <span class="label">型号</span>
                <span class="value">
                    <?php
                    $model = get_post_meta(get_the_ID(), '型号', true);
                    echo $model ? esc_html($model) : '暂无信息';
                    ?>
                </span>
            </div>

            <div class="item">
                <span class="label">规格</span>
                <span class="value">
                    <?php
                    $spec = get_post_meta(get_the_ID(), '规格', true);
                    echo $spec ? esc_html($spec) : '暂无信息';
                    ?>
                </span>
            </div>

            <div class="item">
                <span class="label">重量</span>
                <span class="value">
                    <?php
                    echo $product->get_weight() ? esc_html($product->get_weight()) . ' ' . get_option('woocommerce_weight_unit') : '暂无信息';
                    ?>
                </span>
            </div>

            <div class="item">
                <span class="label">尺寸</span>
                <span class="value">
                    <?php
                    if ($product->has_dimensions()) {
                        $dimensions = array_filter($product->get_dimensions(false));
                        echo implode('×', $dimensions) . ' ' . get_option('woocommerce_dimension_unit');
                    } else {
                        echo '暂无信息';
                    }
                    ?>
                </span>
            </div>

            <div class="item full-width-item">
                <span class="label">材质</span>
                <span class="value">
                    <?php
                    $material = get_post_meta(get_the_ID(), '材质', true);
                    echo $material ? esc_html($material) : '暂无信息';
                    ?>
                </span>
            </div>
        </div>

        <div class="cycle-box">
            <?php
            $cycle = get_post_meta(get_the_ID(), '交货周期', true);
            ?>
            📅 预计交货周期：
            <?php echo $cycle ? esc_html($cycle) : '暂无信息'; ?>
        </div>
    </div>
    <div class="fixed-footer">
        <div class="action-layer">
            <div class="qty-box">
                <div class="qty-btn" onclick="changeQty(-1)">-</div>
                <input type="number" class="qty-num" id="qty" value="1" readonly>
                <div class="qty-btn" onclick="changeQty(1)">+</div>
            </div>
            <button class="add-order-btn" onclick="addOrder()">加入订单</button>
        </div>
    </div>

</main>

<div id="toast">已成功加入订单</div>

<script>
    function changeQty(n) {
        let el = document.getElementById('qty');
        let val = parseInt(el.value) + n;
        if (val >= 1) el.value = val;
    }

    function addOrder() {
        if (typeof jQuery === 'undefined') return;
        const $ = jQuery;
        const product_id = <?php echo $product->get_id(); ?>;
        const qty = $('#qty').val();

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
                if (response.success) {
                    const toast = document.getElementById('toast');
                    toast.style.display = 'block';
                    setTimeout(() => {
                        toast.style.display = 'none';
                        $('.add-order-btn').text('加入订单').prop('disabled', false);
                    }, 2000);
                } else {
                    alert('添加失败，请重试');
                    $('.add-order-btn').text('加入订单').prop('disabled', false);
                }
            }
        });
    }
</script>

<style>
    :root {
        --primary: #007aff;
        --sub: #888;
    }

    body {
        background: #f2f5f8;
        margin: 0;
        padding-bottom: 80px;
    }

    /* 底部预留空间减少 */

    .header {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: #fff;
        height: 50px;
        display: flex;
        align-items: center;
        padding: 0 15px;
        border-bottom: 1px solid #eee;
        
    }

    .back-btn {
        font-size: 24px;
        font-weight: bold;
        width: 30px;
        cursor: pointer;
    }

    .header-title {
        flex: 1;
        text-align: center;
        font-weight: 800;
    }

    /* --- 多媒体区优化 --- */
    .media-slider {
        width: 100%;
        height: 350px;
        background: #fff;
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        /* 隐藏 IE, Edge 和 Firefox 的滚动条 */
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* 隐藏 Chrome, Safari 和 Opera 的滚动条 */
    .media-slider::-webkit-scrollbar {
        display: none;
    }

    .media-item {
        min-width: 100%;
        height: 100%;
        scroll-snap-align: start;
        position: relative;
    }

    .media-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    /* 轮播图提示文字样式 */
    .slider-hint {
        position: absolute;
        bottom: 45px;
        /* 距离底部高度 */
        right: 2px;
        /* 距离右侧距离 */
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 11px;
        padding: 4px 12px;
        border-radius: 20px;
        pointer-events: none;
        /* 确保不影响手指滑动操作 */
        backdrop-filter: blur(4px);
        /* 毛玻璃效果 */
        z-index: 10;
    }

    /* 之前写的 media-tag 是数字标签 (如 1/5)，
   为了视觉不冲突，我们可以把数字标签稍微往左移一点，或者直接隐藏它。
   如果你想保留数字标签，请将它的 bottom 改高一点，如下： */
    .media-tag {
        position: absolute;
        bottom: 45px;
        /* 移高一点，不和提示文字重叠 */
        right: 15px;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
    }



    /* 视频容器背景设为黑色，防止比例不一致漏白 */
    .video-item {
        background: #000;
    }

    /* 视频播放器控制条稍微上移，避免被 media-tag 遮挡 */
    video::-webkit-media-controls-panel {
        background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.5));
    }

    /* 调整视频标签位置 */
    .video-item .media-tag {
        background: var(--primary);
        /* 视频标签用蓝色区分，更醒目 */
        right: auto;
        left: 15px;
    }

    /* 内容盒 */
    .content-box {
        background: #fff;
        padding: 20px 16px;
        margin-bottom: 10px;
    }

    .price-tag {
        color: var(--primary);
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .p-title {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 12px;
        color: #333;
    }

    .p-model {
        font-size: 13px;
        color: var(--sub);
        background: #f0f2f5;
        padding: 4px 10px;
        border-radius: 4px;
        display: inline-block;
    }

    /* 参数区 */
    /* 参数区 V2 模版样式 */
    .param-grid-v2 {
        background: #fff;
        padding: 18px;
        margin-bottom: 10px;
    }

    .sn-tag {
        font-size: 12px;
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
        letter-spacing: 0.5px;
    }

    .grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        /* 2列平铺 */
        gap: 10px;
    }

    .item {
        background: #f8f9fa;
        padding: 8px 10px;
        border-radius: 8px;
    }

    .label {
        color: #888;
        font-size: 11px;
        display: block;
        margin-bottom: 2px;
    }

    .value {
        font-size: 13px;
        font-weight: 600;
        color: #444;
        line-height: 1.4;
    }

    /* 材质独占一行 */
    .full-width-item {
        grid-column: span 2;
        background: #fff4e6;
        border: 1px dashed #ffd8a8;
    }

    /* 交货周期专属样式 */
    .cycle-box {
        background: #e7f5ff;
        padding: 12px;
        border-radius: 10px;
        font-size: 13px;
        color: #1971c2;
        margin-top: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    /* 底部固定操作栏 - 纯单层 */
    .fixed-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 2000;
        background: #fff;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        padding-bottom: env(safe-area-inset-bottom);
        /* 适配全面屏底部 */
    }

    .action-layer {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        gap: 15px;
    }

    .qty-box {
        display: flex;
        align-items: center;
        background: #f1f3f5;
        border-radius: 8px;
        height: 44px;
        padding: 0 5px;

    }

    .qty-btn {
        width: 35px;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        color: var(--primary);
        cursor: pointer;
        user-select: none;
    }

    /* 移除 Chrome, Safari, Edge, Opera 的上下箭头 */
    .qty-num::-webkit-outer-spin-button,
    .qty-num::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* 移除 Firefox 的上下箭头 */
    .qty-num {
        -moz-appearance: textfield;
    }

    /* 补充样式：确保输入框居中且文字清晰 */
    .qty-num {
        width: 40px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: bold;
        font-size: 16px;
        outline: none;
        color: #333;
        -webkit-user-select: none;
        /* 防止长按弹出选择框 */
    }

    .add-order-btn {
        flex: 1;
        height: 44px;
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
    }

    #toast {
        position: fixed;
        top: 45%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 12px 25px;
        border-radius: 12px;
        display: none;
        z-index: 3000;
    }
</style>

<?php get_footer(); ?>