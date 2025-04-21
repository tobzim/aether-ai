<?php
defined( 'ABSPATH' ) || exit;
?>

<div class="aether-admin-wrapper full-dark fade-in">

  <!-- Header -->
  <div class="aether-header large">
    <img src="<?php echo plugins_url( '../assets/img/aether-logo.svg', __FILE__ ); ?>"
         alt="Aether AI Connect Logo"
         class="aether-logo">
    <h1 class="aether-title"><?php esc_html_e( 'WooGenerator', 'aether-ai-connect' ); ?></h1>
    <p class="aether-subtitle">
      <?php esc_html_e( 'Erstelle ausführliche Produktbeschreibungen und prägnante Kurztexte für deine WooCommerce‑Produkte per Knopfdruck – direkt hier im Backend. Wähle die Produkte aus, klicke auf „Generate“ und lass die KI die Arbeit übernehmen!', 'aether-ai-connect' ); ?>
      <br>
      <?php esc_html_e( 'Mehr Infos & Support findest du auf der ', 'aether-ai-connect' ); ?>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=aether-info' ) ); ?>">
        <?php esc_html_e( 'Info & Support', 'aether-ai-connect' ); ?>
      </a>.
    </p>
  </div>

  <!-- Card mit Tabelle -->
  <div class="aether-card">
    <h2 class="aether-section-title"><?php esc_html_e( 'Produktliste', 'aether-ai-connect' ); ?></h2>
    <div id="woogenerator-app"></div>
  </div>

</div>
