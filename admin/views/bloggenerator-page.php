<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wp-admin-wrapper full-dark fade-in">

  <!-- Header -->
  <div class="wp-header large">
    <img src="<?php echo plugins_url( '../assets/icons/wp.svg', __FILE__ ); ?>" alt="WordPress AI Connect Logo" class="wp-logo">
    <h1 class="wp-title"><?php esc_html_e( 'BlogGenerator', 'wp-ai-connect' ); ?></h1>
    <p class="wp-subtitle"><?php esc_html_e( 'Erzeuge auf Knopfdruck bis zu 20 SEO‐optimierte Blog‐Entwürfe zu deinem Wunsch‐Thema.', 'wp-ai-connect' ); ?></p>
  </div>

  <!-- Einstellungen -->
  <div class="wp-card generator-form">
    <h2 class="wp-section-title"><?php esc_html_e( 'Einstellungen', 'wp-ai-connect' ); ?></h2>
    <div class="generator-form-grid">
      <div class="wp-field">
        <label for="bg-topic"><?php esc_html_e( 'Thema', 'wp-ai-connect' ); ?></label>
        <input type="text" id="bg-topic" class="wp-input" placeholder="<?php esc_attr_e( 'Z. B. Nachhaltigkeit im Tourismus', 'wp-ai-connect' ); ?>">
      </div>
      <div class="wp-field">
        <label for="bg-count"><?php esc_html_e( 'Anzahl Beiträge', 'wp-ai-connect' ); ?></label>
        <select id="bg-count" class="wp-select">
          <?php for ( $i = 1; $i <= 20; $i++ ): ?>
            <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="wp-field">
        <label for="bg-category"><?php esc_html_e( 'Kategorie', 'wp-ai-connect' ); ?></label>
        <?php 
          wp_dropdown_categories([
            'show_option_none' => __('— Keine —','wp-ai-connect'),
            'taxonomy'         => 'category',
            'name'             => 'bg-category',
            'id'               => 'bg-category',
            'class'            => 'wp-select',
            'hide_empty'       => false,
          ]);
        ?>
      </div>
      <div class="wp-footer form-footer">
        <button id="bg-generate" class="button-primary"><?php esc_html_e( 'Generate', 'wp-ai-connect' ); ?></button>
      </div>
    </div>
  </div>

  <!-- Übersicht -->
  <div class="wp-card generator-overview">
    <h2 class="wp-section-title"><?php esc_html_e( 'Übersicht der Entwürfe', 'wp-ai-connect' ); ?></h2>
    <div id="bloggenerator-app"></div>
  </div>

</div>
