<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="page-container">
  <section class="category-grid-section">
    <div class="category-grid-container">
      <?php
      // 1. 获取所有产品分类
      $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false, // 隐藏没有商品的分类，如果你想显示空分类请改为 false
      ]);

      if (!empty($terms) && !is_wp_error($terms)):
        foreach ($terms as $term):
          // 排除默认的“未分类”
          if ($term->slug === 'uncategorized')
            continue;

          // 2. 获取分类配置的缩略图 ID
          $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
          // 3. 获取图片 URL（优先使用分类图，没有则用占位图）
          $term_img_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : wc_placeholder_img_src();
          ?>

          <div class="category-grid-item">
            <a href="<?php echo esc_url(get_term_link($term)); ?>">
              <div class="category-img-box">
                <img src="<?php echo esc_url($term_img_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
              </div>
              <div class="category-name-label">
                <?php echo esc_html($term->name); ?>
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
// 公共底部导航
get_template_part('template-parts/bottom-nav');
get_footer();
?>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const options = {
      root: null,
      rootMargin: '0px 0px -10% 0px',
      threshold: 0
    };

    const dividerObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-active');
        } else {
          entry.target.classList.remove('is-active');
        }
      });
    }, options);

    document.querySelectorAll('.category-divider').forEach(el => {
      dividerObserver.observe(el);
    });
  });
</script>

<style>
  /* 容器基础设置 */
  .page-container {
    background-color: #fff;
    min-height: 100vh;
  }

  .category-grid-section {
    padding: 15px 12px;
    margin-bottom: 80px;
    /* 避开底部导航栏高度 */
  }



  /* 核心：两列网格布局 */
  .category-grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    /* 强制分成两列 */
    gap: 15px;
    /* 图片之间的间距 */
  }

  /* 单个分类卡片 */
  .category-grid-item a {
    text-decoration: none;
    display: block;
  }

  .category-img-box {
    width: 100%;
    aspect-ratio: 1 / 1;
    /* 强制图片容器为正方形 */
    border-radius: 10px;
    overflow: hidden;
    background-color: #f5f5f5;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  }

  .category-img-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* 保证图片铺满不拉伸 */
    display: block;
    transition: transform 0.3s ease;
  }

  /* 分类名称文字 */
  .category-name-label {
    margin-top: 10px;
    font-size: 13px;
    color: #444;
    text-align: center;
    font-weight: 500;
  }

  /* 点击反馈效果 */
  .category-grid-item:active .category-img-box img {
    transform: scale(0.95);
  }
</style>