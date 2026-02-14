<?php
defined( 'ABSPATH' ) || exit;

get_header();

// 强制、安全获取 WC_Product
$product = wc_get_product( get_the_ID() );

if ( ! $product ) {
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
                <?php
                echo $product->get_image(
                    'large',
                    [ 'class' => 'media-content' ]
                );
                ?>
            </div>

            <!-- 相册 -->
            <?php foreach ( $product->get_gallery_image_ids() as $image_id ) : ?>
                <div class="media-item">
                    <?php
                    echo wp_get_attachment_image(
                        $image_id,
                        'large',
                        false,
                        [ 'class' => 'media-content' ]
                    );
                    ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

    <!-- 基础信息 -->
    <div class="content-box">
        <div class="price-tag">
            <?php echo $product->get_price_html(); ?>
        </div>

        <div class="p-title">
            <?php echo esc_html( $product->get_name() ); ?>
        </div>

        <?php if ( $product->get_sku() ) : ?>
            <div class="p-model">
                型号：<?php echo esc_html( $product->get_sku() ); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 参数区 -->
    <?php
    $attributes = $product->get_attributes();
    if ( ! empty( $attributes ) ) :
    ?>
    <div class="param-grid">

        <?php foreach ( $attributes as $attribute ) : ?>

            <?php
            if ( $attribute->is_taxonomy() ) {
                $values = wc_get_product_terms(
                    $product->get_id(),
                    $attribute->get_name(),
                    [ 'fields' => 'names' ]
                );
                $value = implode( ' / ', $values );
            } else {
                $value = implode( ' / ', $attribute->get_options() );
            }
            ?>

            <?php if ( $value ) : ?>
                <div class="grid-row">
                    <div class="grid-label">
                        <?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?>
                    </div>
                    <div class="grid-val">
                        <?php echo esc_html( $value ); ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>

    </div>
    <?php endif; ?>

    <!-- 固定底部 -->
    <form class="fixed-footer" method="post">
    <div class="qty-box">
        <div class="qty-btn" onclick="var q = this.parentNode.querySelector('input'); q.value = Math.max(1, parseInt(q.value)-1);">-</div>
        <input type="number" name="quantity" class="qty-num" value="1" min="1" />
        <div class="qty-btn" onclick="var q = this.parentNode.querySelector('input'); q.value = parseInt(q.value)+1;">+</div>
    </div>
    <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="add-order-btn">
        加入订单
    </button>
</form>


</main>

<?php get_footer(); ?>
