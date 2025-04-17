<?php

namespace PolyPlugins\Maintenance_Mode_Made_Easy\Frontend;

use PolyPlugins\Maintenance_Mode_Made_Easy\Utils;

class Enqueue {

  /**
	 * Full path and filename of plugin.
	 *
	 * @var string $version Full path and filename of plugin.
	 */
  private $plugin;

	/**
	 * The version of this plugin.
	 *
	 * @var string $version The current version of this plugin.
	 */
	private $version;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct($plugin, $version) {
    $this->plugin  = $plugin;
    $this->version = $version;
  }
  
  /**
   * Init
   *
   * @return void
   */
  public function init() {
    add_action('wp_enqueue_scripts', array($this, 'enqueue'));
  }
  
  /**
   * Enqueue scripts and styles
   *
   * @return void
   */
  public function enqueue() {
    $this->enqueue_styles();
    $this->enqueue_scripts();
    $this->maybe_enqueue_top_bar();
  }
  
  /**
   * Enqueue styles
   *
   * @return void
   */
  private function enqueue_styles() {
    wp_enqueue_style('bootstrap-icons', plugins_url('/css/bootstrap-icons.min.css', $this->plugin), array(), $this->version);
  }
  
  /**
   * Enqueue scripts
   *
   * @return void
   */
  private function enqueue_scripts() {
    // Frontend scripts and styles here
  }

  /**
   * Maybe enqueue top bar styles and scripts
   *
   * @return void
   */
  private function maybe_enqueue_top_bar() {
    if (Utils::is_maintenance_enabled() && Utils::can_bypass_maintenance()) {
      wp_enqueue_style('maintenance-mode-top-bar', plugins_url('/css/backend/top-bar.css', $this->plugin), array(), $this->version);
    }
  }
  
}