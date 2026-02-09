<?php
/**
 * ENCOLAR ESTILOS
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('grupo7-portal', get_stylesheet_directory_uri() . '/portal.css', array('parent-style'), '1.0');
});

/**
 * DISEÑO DE LOGIN (ESTILO EXACTO)
 */
function cip_custom_login_design() {
    ?>
    <style type="text/css">
        html, body.login {
            height: 100% !important;
            background: radial-gradient(circle, #f8fafc 0%, #ffffff 100%) !important;
            margin: 0 !important; padding: 0 !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
        }
        #login { position: relative !important; padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: 400px !important; display: flex !important; flex-direction: column !important; justify-content: center !important; }
        #login h1 a, .login h1 a { display: none !important; }
        #login h1::before { content: 'C.I.P SUPPORT'; display: block; color: #0f172a; font-family: 'Segoe UI', Roboto, sans-serif; font-size: 2.2rem; font-weight: 800; text-align: center; margin-bottom: 20px; }
        #loginform { background: white !important; padding: 30px !important; border-radius: 12px !important; border: 1px solid #cbd5e1 !important; box-shadow: 0 10px 15px rgba(0,0,0,0.05) !important; margin: 0 !important; }
        .login label { color: #475569 !important; font-weight: 600 !important; }
        .login input[type="text"], .login input[type="password"] { background: #ffffff !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; padding: 10px !important; margin-top: 5px !important; box-shadow: none !important; }
        .login .button-primary { background: #4f46e5 !important; border: none !important; border-radius: 8px !important; height: 45px !important; line-height: 45px !important; width: 100% !important; font-weight: 600 !important; text-transform: uppercase; letter-spacing: 1px; box-shadow: none !important; text-shadow: none !important; transition: all 0.3s ease !important; margin-top: 20px !important; }
        .login .button-primary:hover { background: #3730a3 !important; transform: scale(1.02); }
        .login input[type="text"]:focus, .login input[type="password"]:focus { border-color: #4f46e5 !important; box-shadow: 0 0 0 1px #4f46e5 !important; outline: none !important; }
        .login #nav, .login #backtoblog { text-align: center !important; padding: 10px 0 0 0 !important; }
        .language-switcher { display: none !important; }
        .login .message, .login .notice { display: none !important; }
        #login_error { margin-bottom: 15px !important; margin-left: 0 !important; border-radius: 8px !important; }
        .login .wp-pwd .button.wp-hide-pw { margin-top: 12px !important; color: #4f46e5 !important; opacity: 0.7; right: 15px !important; position: absolute !important; }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'cip_custom_login_design');

/**
 * 1. BLOQUEO ÚNICAMENTE PARA USUARIOS VIDEO 1-4 EN MEETINGHUB
 */
add_action('admin_init', function() {
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    $user = wp_get_current_user();
    if (!$user->exists()) return;

    if (preg_match('/Usuario Video [1-4]/', $user->user_login)) {
        if (isset($_GET['page']) && $_GET['page'] === 'meetinghub') {
            wp_die('Acceso denegado: No tiene permisos para acceder al panel de gestión.');
        }
    }
});

/**
 * 2. AUTORIZAR REDIRECCIÓN EXTERNA (Para que Jitsi funcione en redirect_to)
 */
add_filter('allowed_redirect_hosts', function($hosts) {
    $hosts[] = 'meet.evolix.org';
    return $hosts;
});

/**
 * 3. BARRA DE ADMINISTRACIÓN
 */
add_action('after_setup_theme', function() {
    $user = wp_get_current_user();
    if ($user->exists() && $user->user_login !== 'Admin Video') {
        show_admin_bar(false);
    }
});

add_filter('login_headerurl', function() { return home_url(); });
add_filter('login_headertext', function() { return 'C.I.P Support'; });
add_action('init', function () { wp_deregister_script( 'heartbeat' ); }, 10 );