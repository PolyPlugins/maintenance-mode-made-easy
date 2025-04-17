<?php

namespace PolyPlugins\Maintenance_Mode_Made_Easy\Frontend;

use PolyPlugins\Maintenance_Mode_Made_Easy\Utils;

class UI {

  /**
	 * Full path and filename of plugin.
	 *
	 * @var string $version Full path and filename of plugin.
	 */
  private $plugin;

	/**
	 * The version of this plugin.
	 *
	 * @var   string $version The current version of this plugin.
	 */
	private $version;

  /**
   * The URL to the plugin directory.
   *
   * @var string $plugin_dir_url URL to the plugin directory.
   */
	private $plugin_dir_url;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct($plugin, $version, $plugin_dir_url) {
    $this->plugin         = $plugin;
    $this->version        = $version;
    $this->plugin_dir_url = $plugin_dir_url;
  }
  
  /**
   * Init
   *
   * @return void
   */
  public function init() {
    add_action('init', array($this, 'maintenance_mode_check'));
    add_action('wp', array($this, 'maybe_show_maintenance_mode'), 0, 1);

    // Disable feeds
    add_action('do_feed_rdf', array($this, 'maybe_disable_feed'), 0, 1);
    add_action('do_feed_rss', array($this, 'maybe_disable_feed'), 0, 1);
    add_action('do_feed_rss2', array($this, 'maybe_disable_feed'), 0, 1);
    add_action('do_feed_atom', array($this, 'maybe_disable_feed'), 0, 1);
    
    add_action('woocommerce_after_checkout_validation', array($this, 'maybe_stop_woocommerce_checkout'), 10, 2);
  }
  
  /**
   * Check if maintenance mode has been enabled or disabled
   *
   * @return void
   */
  public function maintenance_mode_check()
  {
    if (isset($_GET['toggle_maintenance_mode']) && Utils::can_bypass_maintenance()) {
      $nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';

      if (wp_verify_nonce($nonce, 'toggle-maintenance-mode-nonce')) {
        Utils::toggle_maintenance_mode();

        // Remove query args
        wp_safe_redirect(remove_query_arg(array('toggle_maintenance_mode', 'nonce')));
        exit();
      }
    }
  }

  /**
   * Maybe display maintenance mode if enabled
   *
   * @return void
   */
  public function maybe_show_maintenance_mode()
  {
    if (!Utils::is_maintenance_enabled()) {
      return;
    }

    if (defined('DOING_CRON') && DOING_CRON) {
      return;
    }

    if (defined('WP_CLI') && WP_CLI) {
      return;
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {
      return;
    }

    $sanitized_request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_url(wp_unslash($_SERVER['REQUEST_URI'])) : '';

    $request_uri = trailingslashit(strtolower(@wp_parse_url($sanitized_request_uri, PHP_URL_PATH)));

    // Some URLs always need to be accessible
    if (Utils::is_excluded_url($request_uri)) {
      return;
    }

    // Show Privacy Policy
    $current_page_id     = get_the_ID();
    $privacy_policy_id   = get_option('wp_page_for_privacy_policy');

    if ($current_page_id == $privacy_policy_id) {
      return;
    }

    if (!Utils::can_bypass_maintenance()) {
      // Allow access to login and admin pages
      if (strpos($sanitized_request_uri, 'wp-login.php') !== false || is_admin()) {
        return;
      }

      $show_temporary_header = Utils::get_option('temporary_header');

      if ($show_temporary_header) {
        // Set header to show temporarily unavailable and to retry in an hour
        header(wp_get_server_protocol() . ' 503 Service Unavailable');
        header('Retry-After: ' . Utils::get_option('retry_header'));
      } else {
        header(wp_get_server_protocol() . ' 200 OK');
        header('Retry-After: ' . Utils::get_option('retry_header'));
      }

      $this->show_maintenance_mode();

      // Stop further processing
      exit();
    }
  }
  
  /**
   * Maybe disable RSS feed if maintenance mode is enabled
   *
   * @return void
   */
  public function maybe_disable_feed()
  {
    $is_maintenance_enabled = Utils::is_maintenance_enabled();

    // If user is not logged in and maintenance mode is enabled
    if (!is_user_logged_in() && !empty($is_maintenance_enabled)) {
      nocache_headers();

      echo '<?xml version="1.0" encoding="UTF-8" ?><status>Service unavailable.</status>';
      
      exit;
    }
  }

  /**
   * Stop customers from ordering while in maintenance mode
   *
   * @return void
   */
  public function maybe_stop_woocommerce_checkout($fields, $errors) {
    if (Utils::can_bypass_maintenance()) {
      return;
    }

    if (Utils::is_maintenance_enabled()) {
      $errors->add('validation', 'Checkout is temporarily disabled due to maintenance. Please try again later.');
    }
  }

  /**
   * Show the maintenance mode page
   *
   * @return void
   */
  private function show_maintenance_mode() {
    $template = Utils::get_option('template') ? Utils::get_option('template') : 'default';

    if ($template !== 'custom') {
      include plugin_dir_path($this->plugin) . 'templates/'. $template . '/index.php';
    } else {
      $theme_template = locate_template('maintenance.php');

      if ($theme_template) {
        // Include the template from the theme if it exists
        include $theme_template;
      } else {
        // Fallback in case the template is not found in the theme
        include plugin_dir_path($this->plugin) . 'templates/default/index.php';
      }
    }

    // Privacy Policy link is now handled in the template file
    ?>
    <!-- <div style="position: fixed; bottom: 20px; width: 100%;">
      <div style="text-align: center;">
        <a href="https://wordpress.org/plugins/maintenance-mode-made-easy/" rel="nofollow" target="_blank">Maintenance Mode Made Easy</a> built by <a href="https://www.polyplugins.com" rel="nofollow" target="_blank">Poly Plugins</a>.
      </div>
    </div> -->
    <?php
  }
  
}