<?php
defined( 'ABSPATH' ) || exit;

get_header(); ?>

<main class="page-container">

    <!-- 顶部搜索 -->
    <div class="header-search">
        <div class="search-input-box">
            <span class="search-icon">🔍</span>
            <form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input type="text" name="s" placeholder="搜索产品名称..." value="<?php echo get_search_query(); ?>" />
                <input type="hidden" name="post_type" value="product" />
            </form>
        </div>
    </div>

    <!-- 商品滚动列表 -->
    <div class="product-scroll">
        <div class="container">
            <?php if ( woocommerce_product_loop() ) : ?>
                <?php while ( have_posts() ) : the_post(); global $product; ?>
                    <a href="<?php the_permalink(); ?>" class="card">
                        <!-- 商品图片 -->
                        <div class="media-section">
                            <?php echo $product->get_image( 'woocommerce_single' ); ?>
                        </div>

                        <!-- 商品名称 -->
                        <div class="content-section">
                            <span class="product-title"><?php the_title(); ?></span>
                        </div>

                        <!-- 商品价格 + 加入清单 -->
                        <div class="footer-section">
                            <div class="price-box">
                                <span class="currency">¥</span>
                                <span class="price-value"><?php echo $product->get_price_html(); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else : ?>
                <p>没有找到商品</p>
            <?php endif; ?>
        </div>
    </div>

</main>

<style>
/* 移动端专用样式 */
.page-container {
    background-color: #f2f5f8;
    font-family: -apple-system, "SF Pro Text", sans-serif;
    padding: 0;
}
.header-search {
    background-color: #ffffff;
    padding: 15px 20px;
}
.search-input-box {
    background-color: #f1f3f5;
    height: 45px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    padding: 0 15px;
}
.search-input-box input {
    border: none;
    outline: none;
    flex: 1;
    background: transparent;
    font-size: 14px;
}
.product-scroll .container {
    padding: 15px;
}
.card {
    display: block;
    background: #fff;
    border-radius: 20px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    text-decoration: none;
    color: inherit;
}
.media-section img {
    width: 100%;
    display: block;
}
.content-section {
    padding: 10px 15px;
}
.product-title {
    font-weight: 700;
    font-size: 16px;
    color: #1a1a1a;
}
.footer-section {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding: 0 15px 10px;
}
.price-box {
    color: #ff4d4f;
    font-weight: 700;
    font-size: 16px;
}
</style>

<?php
get_footer();
