<?php
defined( 'ABSPATH' ) || exit;
?>

<div class="wp-admin-wrapper full-dark fade-in">

  <!-- Header -->
  <div class="wp-header large">
    <img src="<?php echo plugins_url( '../assets/img/wp-logo.svg', __FILE__ ); ?>"
         alt="WordPress AI Connect Logo"
         class="wp-logo">
    <h1 class="wp-title"><?php esc_html_e( 'WooGenerator', 'wp-ai-connect' ); ?></h1>
    <p class="wp-subtitle">
      <?php esc_html_e( 'Erstelle ausführliche Produktbeschreibungen und prägnante Kurztexte für deine WooCommerce‑Produkte per Knopfdruck – direkt hier im Backend. Wähle die Produkte aus, klicke auf „Generate“ und lass die KI die Arbeit übernehmen!', 'wp-ai-connect' ); ?>
      <br>
      <?php esc_html_e( 'Mehr Infos & Support findest du auf der ', 'wp-ai-connect' ); ?>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-info' ) ); ?>">
        <?php esc_html_e( 'Info & Support', 'wp-ai-connect' ); ?>
      </a>.
    </p>
  </div>

  <!-- Card mit Tabelle -->
  <div class="wp-card">
    <h2 class="wp-section-title"><?php esc_html_e( 'Produktliste', 'wp-ai-connect' ); ?></h2>
    <div id="woogenerator-app"></div>
  </div>

</div>
