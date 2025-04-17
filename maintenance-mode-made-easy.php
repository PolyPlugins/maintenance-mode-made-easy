<?php

/**
 * Plugin Name: Maintenance Mode Made Easy
 * Description: A lightweight plugin to display a maintenance mode message for visitors.
 * Version: 1.0.6
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Poly Plugins
 * Author URI: https://www.polyplugins.com
 * Plugin URI: https://www.polyplugins.com/contact/
 * Text Domain: maintenance-mode-made-easy
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace PolyPlugins\Maintenance_Mode_Made_Easy;

if (!defined('ABSPATH')) exit;

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

register_activation_hook(__FILE__, array(__NAMESPACE__ . '\Maintenance_Mode_Made_Easy', 'activation'));
register_deactivation_hook(__FILE__, array(__NAMESPACE__ . '\Maintenance_Mode_Made_Easy', 'deactivation'));

class Maintenance_Mode_Made_Easy
{
  
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
  public function __construct() {
    $this->plugin         = __FILE__;
    $this->version        = $this->get_plugin_version();
    $this->plugin_dir_url = untrailingslashit(plugin_dir_url($this->plugin));
  }
  
  /**
   * Init
   *
   * @return void
   */
  public function init() {
    $this->load_dependencies();
  }
  
  /**
   * Load dependencies
   *
   * @return void
   */
  public function load_dependencies() {
    $dependency_loader = new Dependency_Loader($this->plugin, $this->version, $this->plugin_dir_url);
    $dependency_loader->init();
  }
 
  /**
   * Activation
   *
   * @return void
   */
  public static function activation()
  {
    // Set default options on activation
    $default_options = array(
      'enabled'                  => false,
      'temporary_header'         => true,
      'retry_header'             => '3600',
      'bypass_roles'             => array('administrator'),
      'excluded_urls'            => array(),
      'template'                 => 'default',
      'heading'                  => __('Maintenance Mode', 'maintenance-mode-made-easy'),
      'content'                  => __('We are currently performing maintenance, please try again later.', 'maintenance-mode-made-easy'),
      'color'                    => '#ffffff',
      'background_color'         => '#000000',
      'background_color_opacity' => '80',
      'background_image'         => '',
      'analytics'                => 'disabled',
      'ga_tracking_id'           => '',
      'matomo_url'               => '',
      'gdpr_bypass'              => false,
      'socials'                  => array(
        'facebook'  => '',
        'instagram' => '',
        'x'         => '',
        'linkedin'  => '',
        'youtube'   => '',
        'tiktok'    => '',
      ),
      'contact' => array(
        'email' => '',
        'phone' => '',
      ),
    );

    add_option('maintenance_mode_settings_polyplugins', $default_options);
  }
    
  /**
   * Deactivation
   *
   * @return void
   */
  public static function deactivation() {
    // Handle deactivation
  }

  /**
   * Get the plugin version
   *
   * @return string $version The plugin version
   */
  private function get_plugin_version() {
    $plugin_data = get_file_data($this->plugin, array('Version' => 'Version'), false);
    $version     = $plugin_data['Version'];

    return $version;
  }

}

$maintenance_mode_made_easy = new Maintenance_Mode_Made_Easy();
$maintenance_mode_made_easy->init();
