<?php defined( 'ABSPATH' ) || exit; ?>

<div class="aether-admin-wrapper full-dark fade-in">

  <!-- Header -->
  <div class="aether-header large">
    <img src="<?php echo plugins_url( '../assets/icons/aether.svg', __FILE__ ); ?>" alt="Aether AI Connect Logo" class="aether-logo">
    <h1 class="aether-title"><?php esc_html_e( 'BlogGenerator', 'aether-ai-connect' ); ?></h1>
    <p class="aether-subtitle"><?php esc_html_e( 'Erzeuge auf Knopfdruck bis zu 20 SEO‐optimierte Blog‐Entwürfe zu deinem Wunsch‐Thema.', 'aether-ai-connect' ); ?></p>
  </div>

  <!-- Einstellungen -->
  <div class="aether-card generator-form">
    <h2 class="aether-section-title"><?php esc_html_e( 'Einstellungen', 'aether-ai-connect' ); ?></h2>
    <div class="generator-form-grid">
      <div class="aether-field">
        <label for="bg-topic"><?php esc_html_e( 'Thema', 'aether-ai-connect' ); ?></label>
        <input type="text" id="bg-topic" class="aether-input" placeholder="<?php esc_attr_e( 'Z. B. Nachhaltigkeit im Tourismus', 'aether-ai-connect' ); ?>">
      </div>
      <div class="aether-field">
        <label for="bg-count"><?php esc_html_e( 'Anzahl Beiträge', 'aether-ai-connect' ); ?></label>
        <select id="bg-count" class="aether-select">
          <?php for ( $i = 1; $i <= 20; $i++ ): ?>
            <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="aether-field">
        <label for="bg-category"><?php esc_html_e( 'Kategorie', 'aether-ai-connect' ); ?></label>
        <?php 
          wp_dropdown_categories([
            'show_option_none' => __('— Keine —','aether-ai-connect'),
            'taxonomy'         => 'category',
            'name'             => 'bg-category',
            'id'               => 'bg-category',
            'class'            => 'aether-select',
            'hide_empty'       => false,
          ]);
        ?>
      </div>
      <div class="aether-footer form-footer">
        <button id="bg-generate" class="button-primary"><?php esc_html_e( 'Generate', 'aether-ai-connect' ); ?></button>
      </div>
    </div>
  </div>

  <!-- Übersicht -->
  <div class="aether-card generator-overview">
    <h2 class="aether-section-title"><?php esc_html_e( 'Übersicht der Entwürfe', 'aether-ai-connect' ); ?></h2>
    <div id="bloggenerator-app"></div>
  </div>

</div>
