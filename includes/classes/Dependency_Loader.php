<?php

namespace PolyPlugins\Maintenance_Mode_Made_Easy;

class Dependency_Loader {

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
    $this->load_frontend();
    $this->load_backend();
    $this->load_translations();
  }
  
  /**
   * Load Frontend
   *
   * @return void
   */
  public function load_frontend() {
    $frontend_loader = new Frontend_Loader($this->plugin, $this->version, $this->plugin_dir_url);
    $frontend_loader->init();
  }
  
  /**
   * Load Backend
   *
   * @return void
   */
  public function load_backend() {
    $backend_loader = new Backend_Loader($this->plugin, $this->version, $this->plugin_dir_url);
    $backend_loader->init();
  }
  
  /**
   * Load Translations
   *
   * @return void
   */
  public function load_translations() {
    $backend_loader = new Translations_Loader($this->plugin, $this->version);
    $backend_loader->init();
  }

}