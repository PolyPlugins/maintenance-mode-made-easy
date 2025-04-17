<?php

use PolyPlugins\Maintenance_Mode_Made_Easy\Utils;

if (!defined('ABSPATH')) die;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html(get_bloginfo('name')); ?></title>
    <meta name="description" content="<?php echo esc_html(get_bloginfo('description')); ?>" />
    <link href="<?php echo esc_url($this->plugin_dir_url . '/css/frontend/maintenance.css'); ?>" rel="stylesheet">
    <link href="<?php echo esc_url($this->plugin_dir_url . '/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo esc_url($this->plugin_dir_url . '/css/bootstrap-icons.min.css'); ?>" rel="stylesheet">

    <?php include plugin_dir_path($this->plugin) . "/template-parts/head.php"; ?>

    <style>
      /* Dynamic color styles - these need to stay here because they use PHP variables */
      <?php
      $color = Utils::get_option('color');
      ?>
      /* Apply text color to links */
      .social-icons a,
      .footer-content a {
        color: <?php echo esc_html($color); ?>;
      }
    </style>
  </head>

  <body>
    <div class="container d-flex align-items-center justify-content-center vh-100">
      <div class="row text-center">
        <h1><?php echo esc_html(Utils::get_option('heading')); ?></h1>
        <div class="content"><?php echo wp_kses_post(Utils::get_option('content')); ?></div>
          <?php
          // Get Contact
          $contact = Utils::get_option('contact');

          // Display contact icons
          if ($contact) {
            ?>
            <div class="contact-icons">
              <?php if (!empty($contact['email'])): ?> 
                <a href="mailto:<?php echo esc_html($contact['email']); ?>" target="_blank" rel="noopener noreferrer" title="<?php _e('Email Us', 'maintenance-mode-made-easy'); ?>">
                  <i class="bi bi-envelope-open-fill"></i>
                </a>
              <?php endif; ?>
              <?php if (!empty($contact['phone'])): ?>
                <a href="tel:<?php echo esc_attr($contact['phone']); ?>" target="_blank" rel="noopener noreferrer" title="<?php _e('Call Us', 'maintenance-mode-made-easy'); ?>">
                  <i class="bi bi-telephone-fill"></i>
                </a>
              <?php endif; ?>  
            </div>
          <?php
          }
          ?>
      </div>
    </div>

    <div class="footer-content">
      <?php

      // Get socials
      $socials = Utils::get_option('socials');

      // Display social icons if any are set
      if ($socials) {
      ?>
        <div class="social-icons">
          <?php foreach ($socials as $platform => $url) : ?>
            <?php if ($url) : ?>
              <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr(ucwords(str_replace('-', ' ', $platform))); ?>">
                <i class="bi bi-<?php echo $platform === 'x' ? 'twitter-x' : esc_attr($platform); ?>"></i>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php 
      }

      // Privacy Policy
      $privacy_policy_id = get_option('wp_page_for_privacy_policy');
      $privacy_policy_page = get_post($privacy_policy_id);
      if ($privacy_policy_page && $privacy_policy_page->post_status === 'publish') :
        $privacy_policy_url = get_permalink($privacy_policy_id);
        ?>
        <div class="privacy-text">
          <a href="<?php echo esc_url($privacy_policy_url); ?>" target="_blank"><?php _e('Privacy Policy', 'maintenance-mode-made-easy'); ?></a>
        </div>
      <?php endif; ?>
    </div>

    <script src="<?php echo esc_html($this->plugin_dir_url . '/js/bootstrap.min.js'); ?>"></script>
  </body>
</html>
