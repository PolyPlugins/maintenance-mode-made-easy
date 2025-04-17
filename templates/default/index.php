<?php if (!defined('ABSPATH')) die; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html(get_bloginfo('name')); ?></title>
    <meta name="description" content="<?php echo esc_html(get_bloginfo('description')); ?>" />
    <link href="<?php echo esc_html($this->plugin_dir_url . '/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <?php include $this->plugin_dir . "/template-parts/head.php"; ?>
  </head>

  <body>
    <div class="container d-flex align-items-center justify-content-center vh-100">
      <div class="row text-center">
        <h1><?php echo esc_html($this->get_option('heading')); ?></h1>
        <p class="content"><?php echo esc_html($this->get_option('content')); ?></p>
      </div>
    </div>

    <script src="<?php echo esc_html($this->plugin_dir_url . '/js/bootstrap.min.js'); ?>"></script>
  </body>
</html>
