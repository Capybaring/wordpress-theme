<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="page-container">

  <!-- 首页轮播图 -->
  <div class="home-slider swiper-container">
    <div class="swiper-wrapper">
      <?php
      // 这里我们使用占位图片，稍后可以替换为实际后台数据
      $placeholder_images = [
        'https://picsum.photos/800/600?random=5', // 16:9 比例
        'https://picsum.photos/800/600?random=6',
        'https://picsum.photos/800/600?random=7',
      ];

      foreach ($placeholder_images as $image_url):
        ?>
        <div class="swiper-slide">
          <img src="<?php echo esc_url($image_url); ?>" alt="Slide Image">
        </div>
      <?php endforeach; ?>
    </div>
  </div>



  <!-- 分类列表 -->
  <!-- 分类列表，每个分类一个小轮播图 -->
  <?php
  $terms = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => true
  ]);

  if (!empty($terms) && !is_wp_error($terms)):
    foreach ($terms as $term):
      // 获取分类的缩略图 ID
      $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
      // 获取缩略图的 URL
      $term_img_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
      ?>

      <!-- 每个分类的轮播图盒子 -->
      <section class="category-section">
        <h2 class="category-title"><?php echo esc_html($term->name); ?></h2>
        <hr class="category-divider">

        <div class="pure-css-scroll-container">
          <?php
          $products = wc_get_products([
            'status' => 'publish',
            'limit' => -1,
            'category' => [$term->slug],
          ]);

          foreach ($products as $product):
            $product_img = wp_get_attachment_image_url($product->get_image_id(), 'medium');
            ?>
            <div class="scroll-item">
              <img src="<?php echo esc_url($product_img); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
            </div>
          <?php endforeach; ?>
        </div>
      </section>

    <?php endforeach;
  endif;
  ?>

</main>

<?php
// 公共底部导航
get_template_part('template-parts/bottom-nav');

get_footer();
?>

<!-- 在页面底部添加Swiper的JS和CSS引用 -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">

<script>
  const swiper = new Swiper('.swiper-container', {
    loop: true,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false, // 手动滑动后继续自动
    },
  });

  document.addEventListener('DOMContentLoaded', function () {
    const options = {
      root: null,
      // 调整触发边距：-10% 表示元素进入屏幕 10% 深度时触发伸长
      // 离开屏幕时立即还原
      rootMargin: '0px 0px -10% 0px',
      threshold: 0
    };

    const dividerObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-active');
          // 如果你希望每次滑上去再滑下来都能重复动画，就注释掉下面这一行
          // observer.unobserve(entry.target);
        } else {
          // 离开视野：还原到 10%
          entry.target.classList.remove('is-active');
        }
      });
    }, options);

    // 监听所有分割线
    document.querySelectorAll('.category-divider').forEach(el => {
      dividerObserver.observe(el);
    });
  });
</script>

<!-- 自定义样式 -->
<style>
  /* 大轮播图的样式 */
  .home-slider {
    width: 100%;
    aspect-ratio: 16 / 9;
    position: relative;
    overflow: hidden;
  }

  .swiper-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .category-section {
    padding-left: 2px;
    /* 标题、线、图片全部以此为准 */
    padding-right: 0;
    /* 右边不设置，方便图片滑出屏幕 */
    margin: 20px 0;
    overflow: hidden;
  }

  .category-title {
    font-size: 14px;
    /* 字体更小一些 */
    /* 使字体更细 */
    margin-bottom: 5px;
    margin-left: 5px;
  }

  .category-divider {
    width: 10%;
    /* 初始状态 */
    height: 1px;
    background-color: #000;
    margin-left: 0;
    margin-bottom: 15px;
    border: none;
    display: block;
    will-change: width;
    /* 增加过渡：缓动效果让它看起来更高级 */
    transition: width 1.2s cubic-bezier(0.4, 1, 0.2, 1);
    /* 告诉浏览器准备好宽度变化，防止抖动 */
  }

  /* 判定类：当 JS 检测到滚动到位时，加上这个类 */
  .category-divider.is-active {
    width: 25%;
  }


  /* 滚动容器 */
  .pure-css-scroll-container {
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    gap: 12px;
    /* 统一的图片间距 */
    padding: 10px 0;
    /* 只有上下有 padding，左右必须为 0 */
    /* 解决部分浏览器默认起始偏移问题 */
    justify-content: flex-start;
  }

  /* 隐藏滚动条（让界面更清爽） */
  .pure-css-scroll-container::-webkit-scrollbar {
    display: none;
  }

  /* 每一个图片项 */
  /* 第四步：图片项大小（不一定要露一半，保持一致即可） */
  .scroll-item {
    flex: 0 0 160px;
    /* 直接给固定宽度，这样最稳定 */
    width: 160px;
    aspect-ratio: 1 / 1;
  }

  .scroll-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    display: block;
  }

  /* 移除原来的 @media 限制，确保无论屏多宽都是一行两个 */
</style>