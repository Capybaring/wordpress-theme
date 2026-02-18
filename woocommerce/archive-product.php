<?php
defined('ABSPATH') || exit;
get_header();
?>

<main class="page-container shop-page">

    <div class="header">
        <div class="header-title">商品列表</div>
    </div>

    <div class="product-grid">
        <?php if (have_posts()): ?>
            <?php while (have_posts()):
                the_post();
                $product = wc_get_product(get_the_ID());
                ?>
                <a href="<?php the_permalink(); ?>" class="product-card">
                    <div class="card-img">
                        <?php echo $product ? $product->get_image('medium') : ''; ?>
                    </div>
                    <div class="card-body">
                        <div class="card-title"><?php the_title(); ?></div>
                        <?php if ($product && $product->get_sku()): ?>
                            <div class="card-sku">型号：<?php echo esc_html($product->get_sku()); ?></div>
                        <?php endif; ?>
                        <?php if ($product): ?>
                            <div class="card-price"><?php echo $product->get_price_html(); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="padding:20px;">暂无商品</p>
        <?php endif; ?>
    </div>

    <!-- 底部导航 -->
    <div class="fixed-footer">
        <div class="fixed-footer">
            <?php get_template_part('template-parts/bottom-nav'); ?>
        </div>
    </div>

</main>

<?php get_footer(); ?>