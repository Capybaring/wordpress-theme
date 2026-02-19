<?php
defined('ABSPATH') || exit;
get_header();
?>

<main class="page-container shop-page">

    <div class="search-header-container">
        <div class="search-box-wrapper">
            <form role="search" method="get" class="search-form" id="searchForm"
                action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" id="product-search-input" class="search-field" placeholder="搜索你想要的产品"
                    value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
                <input type="hidden" name="post_type" value="product" />

                <?php if (is_product_category()): ?>
                    <input type="hidden" name="product_cat" id="current-cat-slug"
                        value="<?php echo esc_attr(get_queried_object()->slug); ?>" />
                <?php endif; ?>
            </form>
            <span id="search-clear-input" class="search-input-clear" style="display:none;">✕</span>

            <div id="search-results-dropdown" class="search-autocomplete-list" style="display:none;"></div>
        </div>

        <div id="search-history-container" class="search-history-row" style="display: none;">
            <div class="history-list" id="history-list"></div>
            <span class="history-close-btn" id="clear-history-btn">✕</span>
        </div>
    </div>

    <div class="product-grid">
        <?php if (have_posts()):
            while (have_posts()):
                the_post();
                $product = wc_get_product(get_the_ID());
                if (!$product)
                    continue; ?>
                <a href="<?php the_permalink(); ?>" class="product-card">
                    <div class="card-img">
                        <?php echo has_post_thumbnail() ? the_post_thumbnail('medium') : wc_placeholder_img('medium'); ?>
                    </div>
                    <div class="card-body">
                        <div class="card-title"><?php the_title(); ?></div>
                        <?php if ($sku = $product->get_sku() ?: '暂无信息'): ?>
                            <div class="card-sku">货号：<?php echo esc_html($sku); ?></div><?php endif; ?>
                        <div class="card-footer">
                            <div class="card-price"><?php echo $product->get_price_html(); ?></div>
                            <div class="quick-add">详情</div>
                        </div>
                    </div>
                </a>
            <?php endwhile; else: ?>
            <div class="no-results">
                <p>未找到匹配的商品</p>
            </div>
        <?php endif; ?>
    </div>

    <?php get_template_part('template-parts/bottom-nav'); ?>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('product-search-input');
        const inputClearBtn = document.getElementById('search-clear-input');
        const dropdown = document.getElementById('search-results-dropdown');
        const historyContainer = document.getElementById('search-history-container');
        const historyList = document.getElementById('history-list');
        const clearHistoryBtn = document.getElementById('clear-history-btn');
        const searchForm = document.getElementById('searchForm');
        const currentCatSlug = decodeURIComponent('<?php echo is_product_category() ? get_queried_object()->slug : ""; ?>');

        let debounceTimer;

        // --- 1. 纯粹的历史记录显示逻辑 ---
        function showHistory() {
            const history = JSON.parse(localStorage.getItem('search_history') || '[]');
            if (history.length > 0) {
                historyList.innerHTML = history.map(word => `<span class="history-tag">${word}</span>`).join('');
                historyContainer.style.display = 'flex';
            } else {
                historyContainer.style.display = 'none';
            }
        }

        // --- 2. 搜索提交保存逻辑 ---
        function saveToLocalStorage(val) {
            if (!val) return;
            let history = JSON.parse(localStorage.getItem('search_history') || '[]');
            history = [val, ...history.filter(i => i !== val)].slice(0, 8);
            localStorage.setItem('search_history', JSON.stringify(history));
        }

        // --- 3. 核心监听：输入时干掉历史，显示搜索 ---
        searchInput.addEventListener('input', function () {
            const val = this.value.trim();

            // A. 处理清空按钮
            inputClearBtn.style.display = (val.length > 0) ? 'block' : 'none';

            // B. 只要有输入，立刻隐藏历史记录，防止干扰
            if (val.length > 0) {
                historyContainer.style.display = 'none';
            } else {
                dropdown.style.display = 'none';
                showHistory(); // 没字了，显示历史
                return;
            }

            // C. 实时搜索请求
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (val.length < 1) return;

                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'hlt_live_search',
                        keyword: val,
                        product_cat: currentCatSlug
                    },
                    success: function (response) {
                        if (val === searchInput.value.trim()) { // 再次确认用户没删字
                            if (response.success && response.data.length > 0) {
                                let html = response.data.map(item => `
                                <div class="autocomplete-item" onclick="clickSaveAndGo('${item.url}', '${item.title}')">
                                    <span class="item-name">${item.title}</span>
                                    ${item.sku ? `<span class="item-sku">${item.sku}</span>` : ''}
                                </div>
                            `).join('');
                                dropdown.innerHTML = html;
                                dropdown.style.display = 'block';
                            } else {
                                dropdown.innerHTML = '<div class="autocomplete-no-res">未找到对应产品</div>';
                                dropdown.style.display = 'block';
                            }
                        }
                    }
                });
            }, 300);
        });
        // 定义点击保存并跳转的函数
        window.clickSaveAndGo = function (url, title) {
            // 1. 执行保存逻辑
            let history = JSON.parse(localStorage.getItem('search_history') || '[]');
            // 过滤重复并置顶
            history = [title, ...history.filter(i => i !== title)].slice(0, 8);
            localStorage.setItem('search_history', JSON.stringify(history));

            // 2. 执行跳转
            window.location.href = url;
        };

        // --- 4. 各种点击事件 ---

        // 点击搜索提交
        searchForm.addEventListener('submit', function () {
            saveToLocalStorage(searchInput.value.trim());
        });

        // 点击历史标签
        historyList.addEventListener('click', function (e) {
            if (e.target.classList.contains('history-tag')) {
                const word = e.target.innerText;
                searchInput.value = word;
                saveToLocalStorage(word);
                searchForm.submit();
            }
        });

        // 清空输入框按钮
        inputClearBtn.addEventListener('click', function () {
            searchInput.value = '';
            inputClearBtn.style.display = 'none';
            dropdown.style.display = 'none';
            showHistory();
            searchInput.focus();
        });

        // 清空历史按钮
        clearHistoryBtn.addEventListener('click', function () {
            localStorage.removeItem('search_history');
            historyContainer.style.display = 'none';
        });

        // 点击外部隐藏下拉
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && e.target !== searchInput) {
                dropdown.style.display = 'none';
            }
        });

        // 初始化：页面加载完成显示历史
        showHistory();
    });
</script>

<style>
    .shop-page {
        background: #f8fafc;
        min-height: 100vh;
        padding-bottom: 100px;
    }

    /* 搜索头部 */
    .search-header-container {
        padding: 15px 12px 12px;
        background: #fff;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }

    .search-box-wrapper {
        display: flex;
        align-items: center;
        background: #f1f5f9;
        border-radius: 12px;
        padding: 0 12px;
        height: 46px;
        position: relative;
    }

    .search-icon {
        font-size: 14px;
        margin-right: 8px;
        opacity: 0.4;
    }

    .search-form {
        flex: 1;
        display: flex;
        align-items: center;
    }

    .search-field {
        width: 100%;
        background: transparent;
        border: none !important;
        outline: none !important;
        font-size: 14px;
        color: #1e293b;
        box-shadow: none !important;
        padding: 0 !important;
    }

    /* 搜索框内叉叉 */
    .search-input-clear {
        padding: 5px;
        font-size: 14px;
        color: #94a3b8;
        cursor: pointer;
    }

    /* 历史记录行 */
    .search-history-row {
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .history-list {
        flex: 1;
        display: flex;
        gap: 8px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .history-list::-webkit-scrollbar {
        display: none;
    }

    .history-tag {
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        color: #64748b;
        cursor: pointer;
        flex-shrink: 0;
    }

    /* 修改：历史记录右侧的关闭叉叉 */
    .history-close-btn {
        font-size: 14px;
        color: #cbd5e1;
        cursor: pointer;
        padding: 4px;
        transition: color 0.2s;
    }

    .history-close-btn:hover {
        color: #64748b;
    }

    /* 商品网格保持之前的样式 */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding: 12px;
    }

    .product-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
    }

    .card-img {
        width: 100%;
        aspect-ratio: 1 / 1;
    }

    .card-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .card-body {
        padding: 12px;
        flex-grow: 1;
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
        height: 36px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .card-sku {
        font-size: 11px;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-price {
        font-size: 16px;
        font-weight: 800;
        color: #2563eb;
    }

    .quick-add {
        padding: 4px 12px;
        background: #f1f5f9;
        color: #2563eb;
        font-size: 14px;
        font-weight: 600;
        border-radius: 20px;
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
        grid-column: span 2;
        color: #94a3b8;
    }

    .back-home {
        display: inline-block;
        margin-top: 12px;
        color: #2563eb;
        text-decoration: none;
        font-size: 14px;
    }

    .search-autocomplete-list {
        position: absolute;
        top: 50px;
        /* 紧贴搜索框下方 */
        left: 0;
        right: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        z-index: 100;
        overflow: hidden;
        border: 1px solid #eee;
    }

    .autocomplete-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 15px;
        text-decoration: none;
        color: #333;
        border-bottom: 1px solid #f9f9f9;
        font-size: 14px;
        cursor: pointer;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:active {
        background: #f1f5f9;
    }

    .item-sku {
        color: #94a3b8;
        font-size: 11px;
    }

    .autocomplete-no-res {
        padding: 15px;
        color: #94a3b8;
        font-size: 13px;
        text-align: center;
    }
</style>



<?php get_footer(); ?>