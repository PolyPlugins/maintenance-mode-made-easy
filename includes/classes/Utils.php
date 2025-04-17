<?php

namespace PolyPlugins\Maintenance_Mode_Made_Easy;

class Utils {
    
  /**
   * Get the status of maintenance mode
   *
   * @return bool $maintenance_mode_status The maintenance mode status
   */
  public static function is_maintenance_enabled()
  {
    $maintenance_mode_status = self::get_option('enabled') ? self::get_option('enabled') : false;

    return $maintenance_mode_status;
  }

  /**
   * Toggle maintenance mode
   *
   * @return void
   */
  public static function toggle_maintenance_mode()
  {
    // If user isn't admin don't allow them to toggle maintenance mode
    if (!self::can_bypass_maintenance()) {
      return;
    }

    $is_maintenance_enabled = self::is_maintenance_enabled();

    // Toggle maintenance mode
    self::update_option('enabled', !$is_maintenance_enabled);

    // Clear various caches
    self::clear_cache();
  }

  /**
   * Checks if the current user can bypass maintenance mode
   * 
   * @return bool True if user can bypass, false otherwise
   */
  public static function can_bypass_maintenance() {
    // If user is not logged in, they can't bypass
    if (!is_user_logged_in()) {
      return false;
    }

    $current_user_id = get_current_user_id();
    $user_data = get_userdata($current_user_id);

    if (!$user_data) {
      return false;
    }

    $current_user_roles = $user_data->roles;
    // Use the plugin's get_option method instead of WordPress get_option
    $allowed_roles = self::get_option('bypass_roles');

    // If no roles are set or it's not an array, default to administrator only
    if (!is_array($allowed_roles) || empty($allowed_roles)) {
      $allowed_roles = array('administrator');
    }

    // Check if any of the user's roles are in the allowed roles
    foreach ($current_user_roles as $role) {
      if (in_array($role, $allowed_roles)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Check if the url is excluded
   *
   * @param  mixed $url         The url to check if excluded
   * @return bool  $is_excluded True or false
   */
  public static function is_excluded_url($url)
  {
    $excluded_urls = array(
      '/wp-admin/',
      '/admin/',
      '/wp-login.php',
      '/feed/',
      '/feed/rss/',
      '/feed/rss2/',
      '/feed/rdf/',
      '/feed/atom/',
    );

    $is_excluded = in_array($url, $excluded_urls) ? true : false;

    return $is_excluded;
  }

  /**
   * Handle clearing various cache
   *
   * @return void
   */
  public static function clear_cache()
  {
    wp_cache_flush();

    if (function_exists('w3tc_flush_all')) {
      w3tc_flush_all();
    }

    if (function_exists('wp_cache_clear_cache')) {
      wp_cache_clear_cache();
    }

    if (method_exists('LiteSpeed_Cache_API', 'purge_all')) {
      LiteSpeed_Cache_API::purge_all();
    }

    if (class_exists('Endurance_Page_Cache')) {
      $epc = new Endurance_Page_Cache;
      $epc->purge_all();
    }

    if (class_exists('SG_CachePress_Supercacher') && method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
      SG_CachePress_Supercacher::purge_cache(true);
    }

    if (class_exists('SiteGround_Optimizer\Supercacher\Supercacher')) {
      SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
    }

    if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
      $GLOBALS['wp_fastest_cache']->deleteCache(true);
    }

    if (is_callable(array('Swift_Performance_Cache', 'clear_all_cache'))) {
      Swift_Performance_Cache::clear_all_cache();
    }

    if (is_callable(array('Hummingbird\WP_Hummingbird', 'flush_cache'))) {
      Hummingbird\WP_Hummingbird::flush_cache(true, false);
    }

    if (function_exists('rocket_clean_domain')) {
      rocket_clean_domain();
    }

    do_action('cache_enabler_clear_complete_cache');
  }

  /**
   * Get maintenance mode options
   *
   * @return array $options The maintenance mode options
   */
  public static function get_options() {
    $options = get_option('maintenance_mode_settings_polyplugins');

    return $options;
  }
  
  /**
   * Get maintenance mode option from options array
   *
   * @param  string $option The option to retrieve from options
   * @return mixed  $option The retrieved option value
   */
  public static function get_option($option) {
    $options = self::get_options();
    $option  = isset($options[$option]) ? $options[$option] : false;

    return $option;
  }
  
  /**
   * Update an option
   *
   * @param  string $option The option name
   * @param  mixed  $value  The option value
   * @return void
   */
  public static function update_option($option, $value) {
    $options          = self::get_options();
    $options[$option] = $value;

    update_option('maintenance_mode_settings_polyplugins', $options);
  }

  /**
   * Convert Hex color to RGBA
   *
   * @param  mixed $hex
   * @param  mixed $alpha
   * @return void
   */
  public static function hex_to_rgba($hex, $alpha = null) {
    // Remove the '#' if present
    $hex = ltrim($hex, '#');
    
    // Get the red, green, and blue values
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // If alpha is provided, return rgba format, otherwise return rgb
    if ($alpha !== null) {
      if ($alpha > 1) {
        $alpha = $alpha / 100; // Convert percentage to decimal
      }

      return "rgba($r, $g, $b, $alpha)";
    } else {
      return "rgb($r, $g, $b)";
    }
  }

}