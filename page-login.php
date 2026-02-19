<?php
/**
 * Template Name: 广州海陆通登录页-精简版
 */

if ( is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

$login_error = isset($_GET['login']) && $_GET['login'] == 'failed';

get_header(); ?>

<style>
    /* 1. 基础重置：解决输入框超出范围的关键 */
    #custom-login-page, #custom-login-page * { 
        box-sizing: border-box; 
    }

    body, html { margin: 0; padding: 0; height: 100%; background: #0f172a !important; }
    
    /* 隐藏主题可能干扰的元素 */
    #primary, #main, .site-header, .site-footer { display: none !important; }

    :root {
        --brand: #0f172a;
        --primary: #2563eb;
        --text-sub: #64748b;
    }

    #custom-login-page {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        z-index: 99999;
    }

    .login-box {
        width: 100%; max-width: 400px; background: rgba(255, 255, 255, 0.95);
        padding: 40px 30px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        backdrop-filter: blur(10px);
    }

    .brand-logo { text-align: center; margin-bottom: 40px; }
    .brand-logo h1 { font-size: 22px; margin: 0; color: var(--brand); letter-spacing: 1px; font-weight: bold; }
    .brand-logo p { font-size: 11px; color: var(--text-sub); margin-top: 8px; text-transform: uppercase; }

    .input-field { margin-bottom: 20px; width: 100%; }
    .input-field input {
        width: 100%; /* 配合 border-box 确保不超出 */
        height: 54px; border: 1.5px solid #e2e8f0; border-radius: 12px;
        padding: 0 16px; font-size: 16px; transition: all 0.3s; background: #fff;
        display: block;
    }
    .input-field input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    
    .login-btn {
        width: 100%; height: 54px; background: var(--primary); color: #fff;
        border: none; border-radius: 12px; font-size: 17px; font-weight: 600;
        box-shadow: 0 4px 12px rgba(37,99,235,0.3); transition: 0.3s; cursor: pointer;
        margin-top: 10px;
    }
    .login-btn:hover { background: #1d4ed8; }
    .login-btn:active { transform: translateY(2px); box-shadow: none; }

    .error-msg { color: #ef4444; font-size: 13px; text-align: center; margin-top: 20px; font-weight: 500; }
</style>

<div id="custom-login-page">
    <div class="login-box">
        <div class="brand-logo">
            <h1>广州海陆通</h1>
            <p>供应链管理系统</p>
        </div>

        <form name="loginform" id="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
            <div class="input-field">
                <input type="text" name="log" id="user_login" placeholder="账号" autocomplete="off" required>
            </div>
            
            <div class="input-field">
                <input type="password" name="pwd" id="user_pass" placeholder="密码" required>
            </div>

            <input type="submit" name="wp-submit" id="wp-submit" class="login-btn" value="进入系统" />
            
            <input type="hidden" name="redirect_to" value="<?php echo home_url(); ?>" />
        </form>

        <?php if ( $login_error ) : ?>
            <div class="error-msg">账号或密码错误，请重新输入</div>
        <?php endif; ?>
    </div>
</div>

<?php wp_footer(); ?>