<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="page-container">
  <section class="category-grid-section">
    <div class="category-grid-container">
      <?php
      $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
      ]);

      if (!empty($terms) && !is_wp_error($terms)):
        foreach ($terms as $term):
          if ($term->slug === 'uncategorized') continue;

          $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
          $term_img_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : wc_placeholder_img_src();
          ?>

          <div class="category-grid-item">
            <a href="<?php echo esc_url(get_term_link($term)); ?>">
              <div class="category-img-box">
                <img src="<?php echo esc_url($term_img_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
                <div class="category-overlay">
                    <span class="category-name-inner"><?php echo esc_html($term->name); ?></span>
                </div>
              </div>
            </a>
          </div>

        <?php endforeach;
      else: ?>
        <p style="padding: 20px; color: #999;">暂无分类信息</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php
get_template_part('template-parts/bottom-nav');
get_footer();
?>

<style>
  /* 全局背景设为纯净白，或者极浅灰 */
  .page-container {
    background-color: #ffffff;
    min-height: 100vh;
    padding-top: 10px; /* 给顶部留一点点呼吸感 */
  }

  .category-grid-section {
    padding: 12px;
  }

  .category-grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .category-grid-item a { text-decoration: none; }

  /* 图片容器 */
  .category-img-box {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 12px;
    overflow: hidden;
    background-color: #f1f5f9;
  }

  .category-img-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* 遮罩层：浅灰色微透明，让文字更容易阅读 */
  .category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /* background: rgba(0, 0, 0, 0.15); 使用极淡的黑色遮罩，能更好地衬托白色文字 */
    background: rgba(72, 72, 72, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* 文字样式：完全透明背景，大字体 */
  .category-name-inner {
    color: #ffffff; /*既然遮罩变深了一点，用白色文字会非常有高级感 */
    /* 如果你要保持黑色文字，请确保遮罩是浅色的 */
    /* color: #000000; */
    font-size: 32px; /* 加大字体 */
    font-weight: 900; /* 特粗体 */
    text-align: center;
    padding: 0 10px;
    letter-spacing: 2px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2); /* 给文字加一点点阴影，防止被背景吃掉 */
    background: transparent; /* 盒子完全透明 */
  }

  /* 交互效果 */
  .category-grid-item:active .category-img-box img {
    transform: scale(1.08);
  }
</style>