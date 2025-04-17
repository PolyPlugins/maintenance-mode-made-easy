<?php

/**
 * Plugin Name: Maintenance Mode Made Easy
 * Description: A lightweight plugin to display a maintenance mode message for visitors.
 * Version: 1.0.2
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Poly Plugins
 * Author URI: https://www.polyplugins.com
 * Plugin URI: https://www.polyplugins.com/contact/
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace PolyPlugins;

if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, array(__NAMESPACE__ . '\Maintenance_Mode_Made_Easy', 'activation'));
register_deactivation_hook(__FILE__, array(__NAMESPACE__ . '\Maintenance_Mode_Made_Easy', 'deactivation'));

class Maintenance_Mode_Made_Easy
{
  
  /**
	 * Full path and filename of plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin    Full path and filename of plugin.
	 */
  private $plugin;
  
  /**
	 * Plugin directory
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string  $plugin_dir The plugin directory
	 */
  private $plugin_dir;
  
  /**
	 * Full url without trailing slash of the plugin directory
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string  $plugin_dir_url Full url without trailing slash of the plugin directory
	 */
  private $plugin_dir_url;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    $this->plugin         = __FILE__;
    $this->plugin_dir     = untrailingslashit(dirname($this->plugin));
    $this->plugin_dir_url = untrailingslashit(plugin_dir_url($this->plugin));
  }
  
  /**
   * Init
   *
   * @return void
   */
  public function init()
  {
    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
    add_action('init', array($this, 'maintenance_mode_check'));
    add_action('admin_bar_menu', array($this, 'add_admin_bar'), 100);

    add_action('wp', array($this, 'maybe_show_maintenance_mode'), 0, 1);
    // Disable feeds
    add_action('do_feed_rdf', array($this, 'maybe_disable_feed'), 0, 1);
    add_action('do_feed_rss', array($this, 'maybe_disable_feed'), 0, 1);
    add_action('do_feed_rss2', array($this, 'maybe_disable_feed'), 0, 1);
    add_action('do_feed_atom', array($this, 'maybe_disable_feed'), 0, 1);
    add_action('woocommerce_after_checkout_validation', array($this, 'maybe_stop_woocommerce_checkout'), 10, 2);

		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'settings_init'));
  }
	
	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function admin_enqueue($hook_suffix) {
    if ($hook_suffix === 'settings_page_maintenance-mode-made-easy') {
      wp_enqueue_media();
      wp_enqueue_style('wp-color-picker');
		  wp_enqueue_script('bootstrap', plugins_url('/js/bootstrap.min.js', __FILE__), array('jquery', 'wp-color-picker'), filemtime(plugin_dir_path(dirname(__FILE__)) . dirname(plugin_basename(__FILE__))  . '/js/bootstrap.min.js'), true);
      wp_enqueue_style('bootstrap', plugins_url('/css/admin/bootstrap-wrapper.min.css', __FILE__), array(), filemtime(plugin_dir_path(dirname(__FILE__)) . dirname(plugin_basename(__FILE__))  . '/css/admin/bootstrap-wrapper.min.css'));
		  wp_enqueue_script('maintenance-mode-settings', plugins_url('/js/admin/settings.js', __FILE__), array('jquery', 'wp-color-picker'), filemtime(plugin_dir_path(dirname(__FILE__)) . dirname(plugin_basename(__FILE__))  . '/js/admin/settings.js'), true);
      wp_enqueue_style('maintenance-mode-settings', plugins_url('/css/admin/settings.css', __FILE__), array(), filemtime(plugin_dir_path(dirname(__FILE__)) . dirname(plugin_basename(__FILE__))  . '/css/admin/settings.css'));
      wp_enqueue_editor();
      wp_enqueue_media();
    }

    if ($this->is_maintenance_enabled()) {
      wp_enqueue_style('maintenance-mode-top-bar', plugins_url('/css/admin/top-bar.css', __FILE__), array(), filemtime(plugin_dir_path(dirname(__FILE__)) . dirname(plugin_basename(__FILE__))  . '/css/admin/top-bar.css'));
    }
	}
  
  /**
   * Check if maintenance mode has been enabled or disabled
   *
   * @return void
   */
  public function maintenance_mode_check()
  {
    if (isset($_GET['toggle_maintenance_mode']) && current_user_can('manage_options')) {
      $nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';

      if (wp_verify_nonce($nonce, 'toggle-maintenance-mode-nonce')) {
        $this->toggle_maintenance_mode();

        // Remove query args
        wp_safe_redirect(remove_query_arg(array('toggle_maintenance_mode', 'nonce')));
        exit();
      }
    }
  }
  
  /**
   * Add a way to toggle maintenance mode via admin bar
   *
   * @param  mixed $wp_admin_bar
   * @return void
   */
  public function add_admin_bar($wp_admin_bar)
  {
    if (!current_user_can('manage_options')) {
      return;
    }

    $is_maintenance_enabled = $this->is_maintenance_enabled();
    $settings_page          = 'options-general.php?page=maintenance-mode-made-easy';
    
    $menu_args = array(
      'id'     => 'maintenance_mode',
      'title'  => 'Maintenance Mode',
      'href'   => admin_url($settings_page),
      'meta'   => array(
        'class' => $is_maintenance_enabled ? 'active' : '',
      ),
    );

    $wp_admin_bar->add_node($menu_args);

    $toggle_submenu_args = array(
      'id'     => 'toggle_maintenance_mode',
      'title'  => $is_maintenance_enabled ? 'Disable' : 'Enable',
      'href'   => add_query_arg(
        array(
          'toggle_maintenance_mode' => '1',
          'nonce'                   => wp_create_nonce('toggle-maintenance-mode-nonce'),
        ),
        admin_url()
      ),
      'parent' => 'maintenance_mode',
    );

    $wp_admin_bar->add_node($toggle_submenu_args);

    $settings_submenu_args = array(
      'id'     => 'maintenance_mode_settings',
      'title'  => 'Settings',
      'href'   => admin_url($settings_page),
      'parent' => 'maintenance_mode',
    );

    $wp_admin_bar->add_node($settings_submenu_args);
  }
  
  /**
   * Maybe display maintenance mode if enabled
   *
   * @return void
   */
  public function maybe_show_maintenance_mode()
  {
    if (!$this->is_maintenance_enabled()) {
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
    if ($this->is_excluded_url($request_uri)) {
      return;
    }

    // Show Privacy Policy
    $current_page_id     = get_the_ID();
    $privacy_policy_id   = get_option('wp_page_for_privacy_policy');

    if ($current_page_id == $privacy_policy_id) {
      return;
    }

    if (!current_user_can('manage_options') && !is_user_logged_in()) {
      // Allow access to login and admin pages
      if (strpos($sanitized_request_uri, 'wp-login.php') !== false || is_admin()) {
        return;
      }

      $show_temporary_header = $this->get_option('temporary_header');

      if ($show_temporary_header) {
        // Set header to show temporarily unavailable and to retry in an hour
        header(wp_get_server_protocol() . ' 503 Service Unavailable');
        header('Retry-After: ' . $this->get_option('retry_header'));
      } else {
        header(wp_get_server_protocol() . ' 200 OK');
        header('Retry-After: ' . $this->get_option('retry_header'));
      }

      $this->show_maintenance_mode();

      // Stop further processing
      exit();
    }
  }
  
  /**
   * Stop customers from ordering while in maintenance mode
   *
   * @return void
   */
  public function maybe_stop_woocommerce_checkout($fields, $errors) {
    if (current_user_can('manage_options')) {
      return;
    }

    if ($this->is_maintenance_enabled()) {
      $errors->add('validation', 'Checkout is temporarily disabled due to maintenance. Please try again later.');
    }
  }

	/**
	 * Add admin menu to backend
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page('options-general.php', 'Maintenance Mode Made Easy', 'Maintenance Mode', 'manage_options', 'maintenance-mode-made-easy', array($this, 'options_page'));
	}
  
	/**
	 * Initialize Settings
	 *
	 * @return void
	 */
	public function settings_init() {
    // Register the setting page
    register_setting(
      'maintenance_mode_polyplugins', // Option group
      'maintenance_mode_settings_polyplugins', // Option name
      array($this, 'sanitize')
    );

    // Add a section to assign an ID for admin.js to target switching between tabs
    add_settings_section(
      'maintenance_mode_general_section_polyplugins',
      '',
      null,
      'maintenance_mode_general_polyplugins'
    );

    add_settings_section(
      'maintenance_mode_design_section_polyplugins',
      '',
      null,
      'maintenance_mode_design_polyplugins'
    );

    add_settings_section(
      'maintenance_mode_social_section_polyplugins',
      '',
      null,
      'maintenance_mode_social_polyplugins'
    );

    add_settings_section(
      'maintenance_mode_analytics_section_polyplugins',
      '',
      null,
      'maintenance_mode_analytics_polyplugins'
    );

    add_settings_section(
      'maintenance_mode_contact_section_polyplugins',
      '',
      null,
      'maintenance_mode_contact_polyplugins'
    );
    

    // Add a setting under general section
		add_settings_field(
			'enabled', // Setting Id
			'Enabled?', // Setting Label
			array($this, 'enabled_render'), // Setting callback
			'maintenance_mode_general_polyplugins', // Setting page
			'maintenance_mode_general_section_polyplugins' // Setting section
		);

		add_settings_field(
			'temporary_header',
		  'Send 503 Header?',
			array($this, 'temporary_header_render'),
			'maintenance_mode_general_polyplugins',
			'maintenance_mode_general_section_polyplugins'
		);

		add_settings_field(
			'retry_header',
		  'Retry After Header',
			array($this, 'retry_header_render'),
			'maintenance_mode_general_polyplugins',
			'maintenance_mode_general_section_polyplugins'
		);

		add_settings_field(
			'template',
		  'Template',
			array($this, 'template_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'heading',
		  'Heading',
			array($this, 'heading_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'content',
		  'Content',
			array($this, 'content_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'color',
		  'Color',
			array($this, 'color_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'background_color',
		  'Background Color',
			array($this, 'background_color_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'background_color_opacity',
		  'Background Color Opacity',
			array($this, 'background_color_opacity_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'background_image',
		  'Background Image',
			array($this, 'background_image_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'analytics',
		  'Analytics',
			array($this, 'analytics_render'),
			'maintenance_mode_analytics_polyplugins',
			'maintenance_mode_analytics_section_polyplugins'
		);

		add_settings_field(
			'ga_tracking_id',
		  'Google Analytics Tracking ID',
			array($this, 'ga_tracking_id_render'),
			'maintenance_mode_analytics_polyplugins',
			'maintenance_mode_analytics_section_polyplugins'
		);

		add_settings_field(
			'matomo_url',
		  'Matomo URL',
			array($this, 'matomo_url_render'),
			'maintenance_mode_analytics_polyplugins',
			'maintenance_mode_analytics_section_polyplugins'
		);

		add_settings_field(
			'gdpr_bypass',
		  '',
			array($this, 'gdpr_bypass_render'),
			'maintenance_mode_analytics_polyplugins',
			'maintenance_mode_analytics_section_polyplugins'
		);

    // Add settings fields for social media
    add_settings_field(
      'facebook',
      'Facebook URL',
      array($this, 'facebook_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'instagram',
      'Instagram URL',
      array($this, 'instagram_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'x',
      'X URL',
      array($this, 'x_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'linkedin',
      'LinkedIn URL',
      array($this, 'linkedin_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'youtube',
      'YouTube URL',
      array($this, 'youtube_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'tiktok',
      'TikTok URL',
      array($this, 'tiktok_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'email',
      'Email Address',
      array($this, 'email_render'),
      'maintenance_mode_contact_polyplugins',
      'maintenance_mode_contact_section_polyplugins'
    );

    add_settings_field(
      'phone',
      'Phone URL',
      array($this, 'phone_render'),
      'maintenance_mode_contact_polyplugins',
      'maintenance_mode_contact_section_polyplugins'
    );
	}

  /**
	 * Render Enabled Field
	 *
	 * @return void
	 */
	public function enabled_render() {
		$option = $this->get_option('enabled'); // Get enabled option value
    ?>
    <div class="form-check form-switch">
      <input type="checkbox" name="maintenance_mode_settings_polyplugins[enabled]" class="form-check-input" id="enabled" role="switch" <?php checked(1, $option, true); ?> /> Yes
    </div>
		<?php
	}

  /**
	 * Render Temporary Header Field
	 *
	 * @return void
	 */
	public function temporary_header_render() {
		$option = $this->get_option('temporary_header');
    ?>
    <div class="form-check form-switch">
      <input type="checkbox" name="maintenance_mode_settings_polyplugins[temporary_header]" class="form-check-input" id="temporary_header" role="switch" <?php checked(1, $option, true); ?> /> Yes
    </div>
    <p><strong>Provide 503 header to show search engines the site is temporarily down?</strong></p>
	  <?php
	}

  /**
	 * Render Temporary Header Field
	 *
	 * @return void
	 */
	public function retry_header_render() {
		$option = $this->get_option('retry_header');
    ?>
    <input type='number' name='maintenance_mode_settings_polyplugins[retry_header]' step="60" value='<?php echo esc_html($option); ?>'>
		<p><strong>Provide a Retry After header in seconds.</strong></p>
		<p><strong>If you believe your site will be in maintenance longer than 3600 seconds (1 hour) then adjust that here.</strong></p>
	  <?php
	}

	/**
	 * Render Template Field
	 *
	 * @return void
	 */
	public function template_render() {
		$option    = $this->get_option('template');
    $templates = $this->get_templates();
	  ?>
    <select name="maintenance_mode_settings_polyplugins[template]" id="template">
      <?php foreach($templates as $theme_key => $theme_name) : ?>
			  <option value="<?php echo esc_attr($theme_key); ?>"<?php echo $option == $theme_key ? ' selected' : ''; ?>><?php echo esc_html($theme_name); ?></option>
      <?php endforeach; ?>
      
      <option value="custom"<?php echo $option == 'custom' ? ' selected' : ''; ?>>Custom</option>
    </select>
    <p><strong>Selecting custom will allow you to build your own custom template. Simply create a maintenance.php file in /wp-content/<?php echo esc_html(get_template()); ?>/</strong></p>
    <p><strong>Using your own custom template will not use any of the below settings.</strong></p>
	  <?php
	}

	/**
	 * Render Heading Field
	 *
	 * @return void
	 */
	public function heading_render() {
		$option = $this->get_option('heading');
	  ?>
		<input type='text' name='maintenance_mode_settings_polyplugins[heading]' placeholder="Enter the heading for the maintenance page" value='<?php echo esc_html($option); ?>'>
	  <?php
	}

	/**
	 * Render Content Field
	 *
	 * @return void
	 */
	public function content_render() {
		$option = $this->get_option('content');
    $editor_settings = array(
      'textarea_name' => 'maintenance_mode_settings_polyplugins[content]',
      'textarea_rows' => 8,
      'media_buttons' => true,  // Media Buttons are visible
      'teeny'         => false, // Show the full editor
      'tinymce'       => true,  // Enables the visual editor
      'quicktags'     => true,  // Enables HTML editor
    );
    wp_editor($option, 'maintenance_mode_content', $editor_settings);
	}

	/**
	 * Render Color Field
	 *
	 * @return void
	 */
	public function color_render() {
		$option = $this->get_option('color');
	  ?>
		<input type='text' id="color" name='maintenance_mode_settings_polyplugins[color]' value='<?php echo esc_html($option); ?>'>
	  <?php
	}

	/**
	 * Render Background Field
	 *
	 * @return void
	 */
	public function background_color_render() {
		$option = $this->get_option('background_color');
	  ?>
		<input type='text' id="background_color" name='maintenance_mode_settings_polyplugins[background_color]' value='<?php echo esc_html($option); ?>'>
	  <?php
	}

	/**
	 * Render Background Field
	 *
	 * @return void
	 */
	public function background_color_opacity_render() {
		$option = $this->get_option('background_color_opacity');
	  ?>
		<input type='range' id="background_color_opacity" name='maintenance_mode_settings_polyplugins[background_color_opacity]' min="0" max="100" step="1" value='<?php echo esc_html($option); ?>'>
	  <span id="range-value-display"><?php echo esc_attr($option); ?></span>
    <?php
	}

  /**
	 * Render Background Image Field
	 *
	 * @return void
	 */
	public function background_image_render() {
    $option = $this->get_option('background_image');
    ?>
    <div id="background-image-uploader">
      <!-- Button to open the media uploader -->
      <button type="button" class="button upload-background-image-button">Select Image</button>
      <button type="button" class="button remove-background-image-button" style="display: none;">Remove</button>
      
      <!-- Preview of the selected image -->
      <div class="background-image-preview" style="margin-top: 10px;">
        <?php if (!empty($option)) : ?>
          <img src="<?php echo esc_url($option); ?>" alt="Background Image Preview" style="max-width: 200px; height: auto;">
        <?php endif; ?>
      </div>

      <input type='text' id="background_image" name='maintenance_mode_settings_polyplugins[background_image]' value='<?php echo esc_html($option); ?>' style="display: none;">
    </div>
    <?php
  }

	/**
	 * Analytics Field
	 *
	 * @return void
	 */
	public function analytics_render() {
		$option    = $this->get_option('analytics');
    $analytics = array(
      'disabled' => 'Disabled',
      'google'   => 'Google Analytics',
      'matomo'   => 'Matomo',
    );
	  ?>
    <select name="maintenance_mode_settings_polyplugins[analytics]" id="analytics">
      <?php foreach($analytics as $analytics_key => $analytics_name) : ?>
			  <option value="<?php echo esc_attr($analytics_key); ?>"<?php echo $option == $analytics_key ? ' selected' : ''; ?>><?php echo esc_html($analytics_name); ?></option>
      <?php endforeach; ?>
    </select>
	  <?php
	}

	/**
	 * Render GA Tracking ID Field
	 *
	 * @return void
	 */
	public function ga_tracking_id_render() {
		$option      = $this->get_option('ga_tracking_id');
	  ?>
		<input type='text' name='maintenance_mode_settings_polyplugins[ga_tracking_id]' id="ga_tracking_id" placeholder="G-XXXXXXXXXX" value='<?php echo esc_html($option); ?>'<?php echo !class_exists('COMPLIANZ') ? ' disabled' : ''; ?>>
    <p><strong>Before enabling, make sure you are in compliance with data protection regulations.</strong></p>
    <?php
	}

	/**
	 * Matomo URL Field
	 *
	 * @return void
	 */
	public function matomo_url_render() {
		$option      = $this->get_option('matomo_url');
	  ?>
		<input type='text' name='maintenance_mode_settings_polyplugins[matomo_url]' id="matomo_url" placeholder="https://xxxxxxx.matomo.cloud/" value='<?php echo esc_html($option); ?>'<?php echo !class_exists('COMPLIANZ') ? ' disabled' : ''; ?>>
    <p><strong>Before enabling, make sure you are in compliance with data protection regulations.</strong></p>
    <?php
	}

	/**
	 * GDPR Bypass Field
	 *
	 * @return void
	 */
	public function gdpr_bypass_render() {
		$gdpr_bypass = $this->get_option('gdpr_bypass');
	  ?>
	  <?php if (!class_exists('COMPLIANZ')) { ?>
      <div class="gdpr-bypass-container" style="background-color: #D63638; padding: 20px; color: #fff;">
        <strong>For analytics tracking, we currently check for user consent using <a href="https://wordpress.org/plugins/complianz-gdpr/" style="color: #fff;" target="_blank">Complianz</a>, which is not currently installed.
        <br><br>If you use another consent plugin, please <a href="https://www.polyplugins.com/contact/" style="color: #fff;" target="_blank">let us know</a> and we'll look into integrating it.</strong>
        <br><div style="margin-top: 20px; text-align: center;"><input type="checkbox" name="maintenance_mode_settings_polyplugins[gdpr_bypass]" id="gdpr_bypass" <?php checked(1, $gdpr_bypass, true); ?> /> Bypass at your own risk</div>
      </div>
    <?php
    }
	}
	
	/**
	 * Render options page
	 *
	 * @return void
	 */
	public function options_page() {
  ?>
    <form action='options.php' method='post'>
      <div class="bootstrap-wrapper">
        <div class="container">
          <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
              <h1>Maintenance Mode Made Easy Settings</h1>
            </div>
            <div class="col-3"></div>
          </div>
          <div class="row">
            <div class="nav-links col-12 col-md-6 col-xl-3">
              <ul>
                <li>
                  <a href="javascript:void(0);" class="active" data-section="general">
                    <div class="icon" style="--icon-url: url('<?php echo esc_url($this->plugin_dir_url . '/images/icons/gear.svg'); ?>');"></div>
                    General
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="design">
                    <div class="icon" style="--icon-url: url('<?php echo esc_url($this->plugin_dir_url . '/images/icons/paint-brush.svg'); ?>')"></div>
                    Design
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="analytics">
                    <div class="icon" style="--icon-url: url('<?php echo esc_url($this->plugin_dir_url . '/images/icons/pie-chart.svg'); ?>')"></div>
                    Analytics
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="social">
                    <div class="icon" style="--icon-url: url('<?php echo esc_url($this->plugin_dir_url . '/images/icons/share-nodes.svg'); ?>')"></div>
                    Social
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="contact">
                    <div class="icon" style="--icon-url: url('<?php echo esc_url($this->plugin_dir_url . '/images/icons/address-book.svg'); ?>')"></div>
                    Contact
                  </a>
                </li>
              </ul>
            </div>
            <div class="tabs col-12 col-md-6 col-xl-6">
              <div class="tab general">
                <?php
                do_settings_sections('maintenance_mode_general_polyplugins');
                ?>
              </div>

              <div class="tab design" style="display: none;">
                <?php
                do_settings_sections('maintenance_mode_design_polyplugins');
                ?>
              </div>

              <div class="tab social" style="display: none;">
                <?php
                do_settings_sections('maintenance_mode_social_polyplugins');
                ?>
              </div>

              <div class="tab analytics" style="display: none;">
                <?php
                do_settings_sections('maintenance_mode_analytics_polyplugins');
                ?>
              </div>

              <div class="tab contact" style="display: none;">
                <?php
                do_settings_sections('maintenance_mode_contact_polyplugins');
                ?>
              </div>
            
              <?php
              settings_fields('maintenance_mode_polyplugins');
              submit_button();
              ?>
              
            </div>

            <div class="ctas col-12 col-md-12 col-xl-3">
              <div class="cta">
                <h2 style="color: #fff;">Something Not Working?</h2>
                <p>We pride ourselves on quality, so if something isn't working or you have a suggestion, feel free to call or email us. We're based out of Tennessee in the USA.
                <p><a href="tel:+14234450216" class="button button-primary" style="text-decoration: none; color: #fff; font-weight: 700; text-transform: uppercase; background-color: #333; border-color: #333;" target="_blank">Call Us</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.polyplugins.com/contact/" class="button button-primary" style="text-decoration: none; color: #fff; font-weight: 700; text-transform: uppercase; background-color: #333; border-color: #333;" target="_blank">Email Us</a></p>
              </div>

              <div class="cta">
                <h2 style="color: #fff;">Too Busy for WordPress Maintenance? We've Got You Covered!</h2>
                <p>Focus on the important tasks that drive your business forward, and let us handle your plugin, theme, and core updates + more with ease.
                <p><a href="https://www.polyplugins.com/wordpress-development-services/" class="button button-primary" style="text-decoration: none; color: #fff; font-weight: 700; text-transform: uppercase; background-color: #333; border-color: #333;" target="_blank">Learn More</a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  <?php
  }

  /**
   * Sanitize Options
   *
   * @param  array $input Array of option inputs
   * @return array $sanitary_values Array of sanitized options
   */
  public function sanitize($input) {
		$sanitary_values = array();

    if (isset($input['enabled']) && $input['enabled']) {
      $sanitary_values['enabled'] = $input['enabled'] === 'on' ? true : false;
    } else {
      $sanitary_values['enabled'] = false;
    }

    if (isset($input['temporary_header']) && $input['temporary_header']) {
      $sanitary_values['temporary_header'] = $input['temporary_header'] === 'on' ? true : false;
    } else {
      $sanitary_values['temporary_header'] = false;
    }

    if (isset($input['retry_header']) && is_numeric($input['retry_header'])) {
			$sanitary_values['retry_header'] = sanitize_text_field($input['retry_header']);
		}

    if (isset($input['template']) && $input['template']) {
			$sanitary_values['template'] = sanitize_text_field($input['template']);
		} else {
			$sanitary_values['template'] = 'default';
		}

    if (isset($input['heading']) && $input['heading']) {
			$sanitary_values['heading'] = sanitize_text_field($input['heading']);
		}

    if (isset($input['content']) && $input['content']) {
			$sanitary_values['content'] = wp_kses_post($input['content']);
		}

    if (isset($input['color']) && $input['color']) {
			$sanitary_values['color'] = sanitize_text_field($input['color']);
		}

    if (isset($input['background_color']) && $input['background_color']) {
			$sanitary_values['background_color'] = sanitize_text_field($input['background_color']);
		}

    if (isset($input['background_color_opacity']) && is_numeric($input['background_color_opacity'])) {
			$sanitary_values['background_color_opacity'] = sanitize_text_field($input['background_color_opacity']);
		}

    if (isset($input['background_image']) && $input['background_image']) {
			$sanitary_values['background_image'] = sanitize_url($input['background_image']);
		}

    // Sanitize social media URLs
    if (isset($input['socials']['facebook'])) {
      $sanitary_values['socials']['facebook'] = sanitize_url($input['socials']['facebook']);
    }

    if (isset($input['socials']['instagram'])) {
      $sanitary_values['socials']['instagram'] = sanitize_url($input['socials']['instagram']);
    }

    if (isset($input['socials']['x'])) {
      $sanitary_values['socials']['x'] = sanitize_url($input['socials']['x']);
    }

    if (isset($input['socials']['linkedin'])) {
      $sanitary_values['socials']['linkedin'] = sanitize_url($input['socials']['linkedin']);
    }

    if (isset($input['socials']['youtube'])) {
      $sanitary_values['socials']['youtube'] = sanitize_url($input['socials']['youtube']);
    }

    if (isset($input['socials']['tiktok'])) {
      $sanitary_values['socials']['tiktok'] = sanitize_url($input['socials']['tiktok']);
    }

    if (isset($input['analytics']) && $input['analytics']) {
			$sanitary_values['analytics'] = sanitize_text_field($input['analytics']);
		} else {
			$sanitary_values['analytics'] = 'disabled';
		}

    if (isset($input['ga_tracking_id']) && $input['ga_tracking_id']) {
			$sanitary_values['ga_tracking_id'] = sanitize_text_field($input['ga_tracking_id']);
		}

    if (isset($input['matomo_url']) && $input['matomo_url']) {
			$sanitary_values['matomo_url'] = sanitize_url($input['matomo_url']);
		}

    if (isset($input['gdpr_bypass']) && $input['gdpr_bypass']) {
      $sanitary_values['gdpr_bypass'] = $input['gdpr_bypass'] === 'on' ? true : false;
    } else {
      $sanitary_values['gdpr_bypass'] = false;
		}

    if (isset($input['contact']['email'])) {
      $sanitary_values['contact']['email'] = sanitize_email($input['contact']['email']);
    } 

    if (isset($input['contact']['phone'])) {
      $sanitary_values['contact']['phone'] = sanitize_text_field($input['contact']['phone']);
    }

    return $sanitary_values;
  }
  
  /**
   * Show the maintenance mode page
   *
   * @return void
   */
  private function show_maintenance_mode() {
    $template = $this->get_option('template') ? $this->get_option('template') : 'default';

    if ($template !== 'custom') {
      include plugin_dir_path(__FILE__) . 'templates/'. $template . '/index.php';
    } else {
      $theme_template = locate_template('maintenance.php');

      if ($theme_template) {
        // Include the template from the theme if it exists
        include $theme_template;
      } else {
        // Fallback in case the template is not found in the theme
        include plugin_dir_path(__FILE__) . 'templates/default/index.php';
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
  
  /**
   * Maybe disable RSS feed if maintenance mode is enabled
   *
   * @return void
   */
  public function maybe_disable_feed()
  {
    $is_maintenance_enabled = $this->is_maintenance_enabled();

    // If user is not logged in and maintenance mode is enabled
    if (!is_user_logged_in() && !empty($is_maintenance_enabled)) {
      nocache_headers();

      echo '<?xml version="1.0" encoding="UTF-8" ?><status>Service unavailable.</status>';
      
      exit;
    }
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
      'excluded_urls'            => array(),
      'template'                 => 'default',
      'heading'                  => 'Maintenance Mode',
      'content'                  => 'We are currently performing maintenance, please try again later.',
      'color'                    => '#ffffff',
      'background_color'         => '#000000',
      'background_color_opacity' => '100',
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
  public static function deactivation()
  {
    // Handle deactivation
  }

  /**
   * Toggle maintenance mode
   *
   * @return void
   */
  private function toggle_maintenance_mode()
  {
    // If user isn't admin don't allow them to toggle maintenance mode
    if (!current_user_can('manage_options')) {
      return;
    }

    $is_maintenance_enabled = $this->is_maintenance_enabled();

    // Toggle maintenance mode
    $this->update_option('enabled', !$is_maintenance_enabled);

    // Clear various caches
    $this->clear_cache();
  }

  /**
   * Handle clearing various cache
   *
   * @return void
   */
  private function clear_cache()
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
   * Check if the url is excluded
   *
   * @param  mixed $url         The url to check if excluded
   * @return bool  $is_excluded True or false
   */
  private function is_excluded_url($url)
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
   * Get maintenance mode options
   *
   * @return array $options The maintenance mode options
   */
  private function get_options() {
    $options = get_option('maintenance_mode_settings_polyplugins');

    return $options;
  }
  
  /**
   * Get maintenance mode option from options array
   *
   * @param  string $option The option to retrieve from options
   * @return mixed  $option The retrieved option value
   */
  private function get_option($option) {
    $options = $this->get_options();
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
  private function update_option($option, $value) {
    $options          = $this->get_options();
    $options[$option] = $value;

    update_option('maintenance_mode_settings_polyplugins', $options);
  }
    
  /**
   * Get the status of maintenance mode
   *
   * @return bool $maintenance_mode_status The maintenance mode status
   */
  private function is_maintenance_enabled()
  {
    $maintenance_mode_status = $this->get_option('enabled') ? $this->get_option('enabled') : false;

    return $maintenance_mode_status;
  }
  
  /**
   * Dynamically fetch template slugs and names from template directory
   *
   * @return array  $templates The available templates
   */
  private function get_templates() {
    $template_path = $this->plugin_dir . '/templates/';
    $templates     = array();
    
    // Check if the directory exists
    if (!is_dir($template_path)) {
      return array();
    }

    // Scan the directory
    $items = scandir($template_path);

    foreach ($items as $item) {
      $item_path = $template_path . $item;

      // Only include folder names
      if (is_dir($item_path) && !in_array($item, array('.', '..'))) {
        $templates[$item] = ucwords(str_replace('-', ' ', $item));
      }
    }

    return $templates;
  }
  
  /**
   * Convert Hex color to RGBA
   *
   * @param  mixed $hex
   * @param  mixed $alpha
   * @return void
   */
  private function hex_to_rgba($hex, $alpha = null) {
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

  /**
   * Render Facebook URL Field
   */
  public function facebook_render() {
    $socials = $this->get_option('socials');
    $option  = isset($socials['facebook']) ? $socials['facebook'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][facebook]' placeholder="https://facebook.com/your-page" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render Instagram URL Field
   */
  public function instagram_render() {
    $socials = $this->get_option('socials');
    $option  = isset($socials['instagram']) ? $socials['instagram'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][instagram]' placeholder="https://instagram.com/your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render X URL Field
   */
  public function x_render() {
    $socials = $this->get_option('socials');
    $option  = isset($socials['x']) ? $socials['x'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][x]' placeholder="https://x.com/your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render LinkedIn URL Field
   */
  public function linkedin_render() {
    $socials = $this->get_option('socials');
    $option  = isset($socials['linkedin']) ? $socials['linkedin'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][linkedin]' placeholder="https://linkedin.com/in/your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render YouTube URL Field
   */
  public function youtube_render() {
    $socials = $this->get_option('socials');
    $option  = isset($socials['youtube']) ? $socials['youtube'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][youtube]' placeholder="https://youtube.com/@your-channel" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render TikTok URL Field
   */
  public function tiktok_render() {
    $socials = $this->get_option('socials');
    $option  = isset($socials['tiktok']) ? $socials['tiktok'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][tiktok]' placeholder="https://tiktok.com/@your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render Email Field
   */
  public function email_render() {
    $contact = $this->get_option('contact');
    $option  = isset($contact['email']) ? $contact['email'] : '';
    ?>
    <input type='email' name='maintenance_mode_settings_polyplugins[contact][email]' placeholder="your@email.com" value='<?php echo esc_html($option); ?>'>
    <?php
  }

   /**
   * Render Phone Field
   */
  public function phone_render() {
    $contact = $this->get_option('contact');
    $option  = isset($contact['phone']) ? $contact['phone'] : '';
    ?>
    <input type='tel' name='maintenance_mode_settings_polyplugins[contact][phone]' placeholder="1234567890" value='<?php echo esc_html($option); ?>'>
    <?php
  }
}

$maintenance_mode_made_easy = new Maintenance_Mode_Made_Easy();
$maintenance_mode_made_easy->init();
