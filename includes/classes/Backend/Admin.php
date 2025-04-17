<?php

namespace PolyPlugins\Maintenance_Mode_Made_Easy\Backend;

use PolyPlugins\Maintenance_Mode_Made_Easy\Utils;

class Admin {

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
    add_action('admin_bar_menu', array($this, 'add_admin_bar'), 100);
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'settings_init'));
  }

  /**
   * Add a way to toggle maintenance mode via admin bar
   *
   * @param  mixed $wp_admin_bar
   * @return void
   */
  public function add_admin_bar($wp_admin_bar)
  {
    if (!Utils::can_bypass_maintenance()) {
      return;
    }

    $is_maintenance_enabled = Utils::is_maintenance_enabled();
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
      'title'  => __('Settings', 'maintenance-mode-made-easy'),
      'href'   => admin_url($settings_page),
      'parent' => 'maintenance_mode',
    );

    $wp_admin_bar->add_node($settings_submenu_args);
  }

	/**
	 * Add admin menu to backend
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page('options-general.php', __('Maintenance Mode Made Easy', 'maintenance-mode-made-easy'), __('Maintenance Mode', 'maintenance-mode-made-easy'), 'manage_options', 'maintenance-mode-made-easy', array($this, 'options_page'));
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
			__('Enabled?', 'maintenance-mode-made-easy'), // Setting Label
			array($this, 'enabled_render'), // Setting callback
			'maintenance_mode_general_polyplugins', // Setting page
			'maintenance_mode_general_section_polyplugins' // Setting section
		);

		add_settings_field(
			'temporary_header',
		  __('Send 503 Header?', 'maintenance-mode-made-easy'),
			array($this, 'temporary_header_render'),
			'maintenance_mode_general_polyplugins',
			'maintenance_mode_general_section_polyplugins'
		);

		add_settings_field(
			'retry_header',
		  __('Retry After Header', 'maintenance-mode-made-easy'),
			array($this, 'retry_header_render'),
			'maintenance_mode_general_polyplugins',
			'maintenance_mode_general_section_polyplugins'
		);

		add_settings_field(
			'template',
		  __('Template', 'maintenance-mode-made-easy'),
			array($this, 'template_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'heading',
		  __('Heading', 'maintenance-mode-made-easy'),
			array($this, 'heading_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'content',
		  __('Content', 'maintenance-mode-made-easy'),
			array($this, 'content_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'color',
		  __('Color', 'maintenance-mode-made-easy'),
			array($this, 'color_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'background_color',
		  __('Background Color', 'maintenance-mode-made-easy'),
			array($this, 'background_color_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'background_color_opacity',
		  __('Background Color Opacity', 'maintenance-mode-made-easy'),
			array($this, 'background_color_opacity_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'background_image',
		  __('Background Image', 'maintenance-mode-made-easy'),
			array($this, 'background_image_render'),
			'maintenance_mode_design_polyplugins',
			'maintenance_mode_design_section_polyplugins'
		);

		add_settings_field(
			'analytics',
		  __('Analytics', 'maintenance-mode-made-easy'),
			array($this, 'analytics_render'),
			'maintenance_mode_analytics_polyplugins',
			'maintenance_mode_analytics_section_polyplugins'
		);

		add_settings_field(
			'ga_tracking_id',
		  __('Google Analytics Tracking ID', 'maintenance-mode-made-easy'),
			array($this, 'ga_tracking_id_render'),
			'maintenance_mode_analytics_polyplugins',
			'maintenance_mode_analytics_section_polyplugins'
		);

		add_settings_field(
			'matomo_url',
		  __('Matomo URL', 'maintenance-mode-made-easy'),
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
      __('Facebook URL', 'maintenance-mode-made-easy'),
      array($this, 'facebook_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'instagram',
      __('Instagram URL', 'maintenance-mode-made-easy'),
      array($this, 'instagram_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'x',
      __('X URL', 'maintenance-mode-made-easy'),
      array($this, 'x_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'linkedin',
      __('LinkedIn URL', 'maintenance-mode-made-easy'),
      array($this, 'linkedin_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'youtube',
      __('YouTube URL', 'maintenance-mode-made-easy'),
      array($this, 'youtube_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'tiktok',
      __('TikTok URL', 'maintenance-mode-made-easy'),
      array($this, 'tiktok_render'),
      'maintenance_mode_social_polyplugins',
      'maintenance_mode_social_section_polyplugins'
    );

    add_settings_field(
      'email',
      __('Email Address', 'maintenance-mode-made-easy'),
      array($this, 'email_render'),
      'maintenance_mode_contact_polyplugins',
      'maintenance_mode_contact_section_polyplugins'
    );

    add_settings_field(
      'phone',
      __('Phone URL', 'maintenance-mode-made-easy'),
      array($this, 'phone_render'),
      'maintenance_mode_contact_polyplugins',
      'maintenance_mode_contact_section_polyplugins'
    );

    add_settings_field(
      'bypass_roles',
      __('Bypass Roles', 'maintenance-mode-made-easy'),
      array($this, 'bypass_roles_render'),
      'maintenance_mode_general_polyplugins',
      'maintenance_mode_general_section_polyplugins'
    );
	}

  /**
	 * Render Enabled Field
	 *
	 * @return void
	 */
	public function enabled_render() {
		$option = Utils::get_option('enabled'); // Get enabled option value
    ?>
    <div class="form-check form-switch">
      <input type="checkbox" name="maintenance_mode_settings_polyplugins[enabled]" class="form-check-input" id="enabled" role="switch" <?php checked(1, $option, true); ?> /> <?php _e('Yes', 'maintenance-mode-made-easy'); ?>
    </div>
		<?php
	}

  /**
	 * Render Temporary Header Field
	 *
	 * @return void
	 */
	public function temporary_header_render() {
		$option = Utils::get_option('temporary_header');
    ?>
    <div class="form-check form-switch">
      <input type="checkbox" name="maintenance_mode_settings_polyplugins[temporary_header]" class="form-check-input" id="temporary_header" role="switch" <?php checked(1, $option, true); ?> /> <?php _e('Yes', 'maintenance-mode-made-easy'); ?>
    </div>
    <p><strong><?php _e('Provide 503 header to show search engines the site is temporarily down?', 'maintenance-mode-made-easy'); ?></strong></p>
	  <?php
	}

  /**
	 * Render Retry Header Field
	 *
	 * @return void
	 */
	public function retry_header_render() {
		$option = Utils::get_option('retry_header');
    ?>
    <input type='number' name='maintenance_mode_settings_polyplugins[retry_header]' step="60" value='<?php echo esc_html($option); ?>'>
		<p><strong><?php _e('Provide a Retry After header in seconds.', 'maintenance-mode-made-easy'); ?></strong></p>
		<p><strong><?php _e('If you believe your site will be in maintenance longer than 3600 seconds (1 hour) then adjust that here.', 'maintenance-mode-made-easy'); ?></strong></p>
	  <?php
	}

  /** 
	 * Render Bypass Roles Field
	 *
	 * @return void
	 */
	public function bypass_roles_render() {
    $selected_roles = Utils::get_option('bypass_roles');
    $roles = wp_roles()->get_names();
    ?>
    <div class="bypass-roles-wrapper">
    <select id="bypass_roles" 
            name="maintenance_mode_settings_polyplugins[bypass_roles][]" 
            multiple="multiple" 
            class="regular-text select2-hidden-accessible" 
            data-placeholder="<?php _e('Select roles that can bypass maintenance mode after logging in', 'maintenance-mode-made-easy'); ?>">
      <?php foreach ($roles as $role_key => $role_name) : 
        if ($role_key == 'administrator') continue;
        ?>
        <option value="<?php echo esc_attr($role_key); ?>" 
          <?php echo in_array($role_key, $selected_roles) ? 'selected' : ''; ?>>
          <?php echo esc_html($role_name); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <p><strong><?php _e('Aministrator is always bypassed', 'maintenance-mode-made-easy'); ?></strong></p>
    <?php
  }

	/**
	 * Render Template Field
	 *
	 * @return void
	 */
	public function template_render() {
		$option    = Utils::get_option('template');
    $templates = $this->get_templates();
	  ?>
    <select name="maintenance_mode_settings_polyplugins[template]" id="template">
      <?php foreach($templates as $theme_key => $theme_name) : ?>
			  <option value="<?php echo esc_attr($theme_key); ?>"<?php echo $option == $theme_key ? ' selected' : ''; ?>><?php echo esc_html($theme_name); ?></option>
      <?php endforeach; ?>
      
      <option value="custom"<?php echo $option == 'custom' ? ' selected' : ''; ?>>Custom</option>
    </select>
    <p><strong><?php _e('Selecting custom will allow you to build your own custom template. Simply create a maintenance.php file in', 'maintenance-mode-made-easy'); ?> /wp-content/<?php echo esc_html(get_template()); ?>/</strong></p>
    <p><strong><?php _e('Using your own custom template will not use any of the below settings.', 'maintenance-mode-made-easy'); ?></strong></p>
	  <?php
	}

	/**
	 * Render Heading Field
	 *
	 * @return void
	 */
	public function heading_render() {
		$option = Utils::get_option('heading');
	  ?>
		<input type='text' name='maintenance_mode_settings_polyplugins[heading]' placeholder="<?php _e('Enter the heading for the maintenance page', 'maintenance-mode-made-easy'); ?>" value='<?php echo esc_html($option); ?>'>
	  <?php
	}

	/**
	 * Render Content Field
	 *
	 * @return void
	 */
	public function content_render() {
		$option = Utils::get_option('content');
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
		$option = Utils::get_option('color');
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
		$option = Utils::get_option('background_color');
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
		$option = Utils::get_option('background_color_opacity');
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
    $option = Utils::get_option('background_image');
    ?>
    <div id="background-image-uploader">
      <!-- Button to open the media uploader -->
      <button type="button" class="button upload-background-image-button"><?php _e('Select Image', 'maintenance-mode-made-easy'); ?></button>
      <button type="button" class="button remove-background-image-button" style="display: none;"><?php _e('Remove', 'maintenance-mode-made-easy'); ?></button>
      
      <!-- Preview of the selected image -->
      <div class="background-image-preview" style="margin-top: 10px;">
        <?php if (!empty($option)) : ?>
          <img src="<?php echo esc_url($option); ?>" alt="<?php _e('Background Image Preview', 'maintenance-mode-made-easy'); ?>" style="max-width: 200px; height: auto;">
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
		$option    = Utils::get_option('analytics');
    $analytics = array(
      'disabled' => __('Disabled', 'maintenance-mode-made-easy'),
      'google'   => __('Google Analytics', 'maintenance-mode-made-easy'),
      'matomo'   => __('Matomo', 'maintenance-mode-made-easy'),
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
		$option = Utils::get_option('ga_tracking_id');
	  ?>
		<input type='text' name='maintenance_mode_settings_polyplugins[ga_tracking_id]' id="ga_tracking_id" placeholder="G-XXXXXXXXXX" value='<?php echo esc_html($option); ?>'<?php echo !class_exists('COMPLIANZ') ? ' disabled' : ''; ?>>
    <p><strong><?php _e('Before enabling, make sure you are in compliance with data protection regulations.', 'maintenance-mode-made-easy'); ?></strong></p>
    <?php
	}

	/**
	 * Matomo URL Field
	 *
	 * @return void
	 */
	public function matomo_url_render() {
		$option = Utils::get_option('matomo_url');
	  ?>
		<input type='text' name='maintenance_mode_settings_polyplugins[matomo_url]' id="matomo_url" placeholder="https://xxxxxxx.matomo.cloud/" value='<?php echo esc_html($option); ?>'<?php echo !class_exists('COMPLIANZ') ? ' disabled' : ''; ?>>
    <p><strong><?php _e('Before enabling, make sure you are in compliance with data protection regulations.', 'maintenance-mode-made-easy'); ?></strong></p>
    <?php
	}

	/**
	 * GDPR Bypass Field
	 *
	 * @return void
	 */
	public function gdpr_bypass_render() {
		$option = Utils::get_option('gdpr_bypass');
	  ?>
	  <?php if (!class_exists('COMPLIANZ')) { ?>
      <div class="gdpr-bypass-container" style="background-color: #D63638; padding: 20px; color: #fff;">
        <strong><?php _e('For analytics tracking, we currently check for user consent using', 'maintenance-mode-made-easy'); ?> <a href="https://wordpress.org/plugins/complianz-gdpr/" style="color: #fff;" target="_blank"><?php _e('Complianz', 'maintenance-mode-made-easy'); ?></a>, <?php _e('which is not currently installed.', 'maintenance-mode-made-easy'); ?>
        <br><br><?php _e('If you use another consent plugin, please', 'maintenance-mode-made-easy'); ?> <a href="https://www.polyplugins.com/contact/" style="color: #fff;" target="_blank"><?php _e('let us know', 'maintenance-mode-made-easy'); ?></a> <?php _e("and we'll look into integrating it.", 'maintenance-mode-made-easy'); ?></strong>
        <br><div style="margin-top: 20px; text-align: center;"><input type="checkbox" name="maintenance_mode_settings_polyplugins[gdpr_bypass]" id="gdpr_bypass" <?php checked(1, $option, true); ?> /> <?php _e('Bypass at your own risk', 'maintenance-mode-made-easy'); ?></div>
      </div>
    <?php
    }
	}

  /**
   * Render Facebook URL Field
   */
  public function facebook_render() {
    $options = Utils::get_option('socials');
    $option  = isset($options['facebook']) ? $options['facebook'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][facebook]' placeholder="https://facebook.com/your-page" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render Instagram URL Field
   */
  public function instagram_render() {
    $options = Utils::get_option('socials');
    $option  = isset($options['instagram']) ? $options['instagram'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][instagram]' placeholder="https://instagram.com/your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render X URL Field
   */
  public function x_render() {
    $options = Utils::get_option('socials');
    $option  = isset($options['x']) ? $options['x'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][x]' placeholder="https://x.com/your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render LinkedIn URL Field
   */
  public function linkedin_render() {
    $options = Utils::get_option('socials');
    $option  = isset($options['linkedin']) ? $options['linkedin'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][linkedin]' placeholder="https://linkedin.com/in/your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render YouTube URL Field
   */
  public function youtube_render() {
    $options = Utils::get_option('socials');
    $option  = isset($options['youtube']) ? $options['youtube'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][youtube]' placeholder="https://youtube.com/@your-channel" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render TikTok URL Field
   */
  public function tiktok_render() {
    $options = Utils::get_option('socials');
    $option  = isset($options['tiktok']) ? $options['tiktok'] : '';
    ?>
    <input type='url' name='maintenance_mode_settings_polyplugins[socials][tiktok]' placeholder="https://tiktok.com/@your-profile" value='<?php echo esc_url($option); ?>'>
    <?php
  }

  /**
   * Render Email Field
   */
  public function email_render() {
    $options = Utils::get_option('contact');
    $option  = isset($options['email']) ? $options['email'] : '';
    ?>
    <input type='email' name='maintenance_mode_settings_polyplugins[contact][email]' placeholder="your@email.com" value='<?php echo esc_html($option); ?>'>
    <?php
  }

   /**
   * Render Phone Field
   */
  public function phone_render() {
    $options = Utils::get_option('contact');
    $option  = isset($options['phone']) ? $options['phone'] : '';
    ?>
    <input type='tel' name='maintenance_mode_settings_polyplugins[contact][phone]' placeholder="1234567890" value='<?php echo esc_html($option); ?>'>
    <?php
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
              <h1><?php _e('Maintenance Mode Made Easy Settings', 'maintenance-mode-made-easy'); ?></h1>
            </div>
            <div class="col-3"></div>
          </div>
          <div class="row">
            <div class="nav-links col-12 col-md-6 col-xl-3">
              <ul>
                <li>
                  <a href="javascript:void(0);" class="active" data-section="general">
                    <i class="bi bi-gear-fill"></i>
                    <?php _e('General', 'maintenance-mode-made-easy'); ?>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="design">
                    <i class="bi bi-palette-fill"></i>
                    <?php _e('Design', 'maintenance-mode-made-easy'); ?>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="analytics">
                    <i class="bi bi-pie-chart-fill"></i>
                    <?php _e('Analytics', 'maintenance-mode-made-easy'); ?>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="social">
                    <i class="bi bi-share-fill"></i>
                    <?php _e('Social', 'maintenance-mode-made-easy'); ?>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0);" data-section="contact">
                    <i class="bi bi-person-lines-fill"></i>
                    <?php _e('Contact', 'maintenance-mode-made-easy'); ?>
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
                <p><a href="tel:+14232818591" class="button button-primary" style="text-decoration: none; color: #fff; font-weight: 700; text-transform: uppercase; background-color: #333; border-color: #333;" target="_blank">Call Us</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.polyplugins.com/contact/" class="button button-primary" style="text-decoration: none; color: #fff; font-weight: 700; text-transform: uppercase; background-color: #333; border-color: #333;" target="_blank">Email Us</a></p>
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

    if (isset($input['bypass_roles']) && is_array($input['bypass_roles'])) {
      $roles = array_map('sanitize_text_field', $input['bypass_roles']);
      if (!in_array('administrator', $roles)) {
        $roles[] = 'administrator';
      }
      $sanitary_values['bypass_roles'] = $roles;
    } else {
      $sanitary_values['bypass_roles'] = array('administrator');
    }

    return $sanitary_values;
  }

  /**
   * Dynamically fetch template slugs and names from template directory
   *
   * @return array  $templates The available templates
   */
  private function get_templates() {
    $template_path = plugin_dir_path($this->plugin) . 'templates/';
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

}