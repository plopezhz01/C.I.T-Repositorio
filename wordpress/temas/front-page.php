<?php
/**
 * LÓGICA DE ENLACES
 */
$tickets_target = admin_url('admin.php?page=soporte-tickets');
$video_target   = admin_url('admin.php?page=meetinghub');

// Tu link de invitación externo
$link_invitation = 'https://meet.evolix.org/Videoconferencia%20Vidal';

// Generación de enlaces
$tickets_link = wp_logout_url( wp_login_url($tickets_target) . '&reauth=1' );
$video_link   = wp_logout_url( wp_login_url($video_target) . '&reauth=1' );

/** * CAMBIO CLAVE: Usamos redirect_to explícito para enlaces externos
 * Esto le dice a WordPress: "Logueate y vete FUERA de este sitio"
 */
$acceso_video = wp_logout_url( wp_login_url() . '?redirect_to=' . rawurlencode($link_invitation) . '&reauth=1' );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>C.I.P Support</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <div class="g7-viewport">

        <div class="g7-bar-header">
            C.I.P Support - Vidal
        </div>

        <main class="g7-main-content">
            <div class="g7-container">
                <header class="g7-intro">
                    <span class="g7-kicker">Acceso Rápido</span>
                    <h1>Portal del Proyecto</h1>
                    <p>Bienvenido, accede a los apartados del sistema.</p>
                </header>

                <div class="g7-grid">
                    <div class="g7-card g7-card--tickets">
                        <div class="g7-card__icon">🎟️</div>
                        <h2>C.I.P Tickets</h2>
                        <p>Gestión de tickets y soporte.</p>
                        <a class="g7-btn" href="<?php echo esc_url($tickets_link); ?>">Abrir Panel</a>
                    </div>

                    <div class="g7-card g7-card--video">
                        <div class="g7-card__icon">⚙️</div>
                        <h2>C.I.P Video</h2>
                        <p>Gestión de videoconferencias.</p>
                        <a class="g7-btn" href="<?php echo esc_url($video_link); ?>">Abrir Panel</a>
                    </div>

                    <div class="g7-card g7-card--acceso">
                        <div class="g7-card__icon">📽️</div>
                        <h2>Videoconferencia</h2>
                        <p>Acceso directo a la sala.</p>
                        <a class="g7-btn" href="<?php echo esc_url($acceso_video); ?>">Acceder Sala</a>
                    </div>
                </div>
            </div>
        </main>

        <div class="g7-bar-footer">
            C.I.P Support - Vidal funciona gracias a
            <a href="https://wordpress.org" class="g7-footer-link" target="_blank" rel="noopener">WordPress</a>
        </div>

    </div>

    <?php wp_footer(); ?>
</body>
</html>