<?php
if (!defined('ABSPATH')) die;
$color                    = $this->get_option('color');
$background_color         = $this->get_option('background_color');
$background_color_opacity = $this->get_option('background_color_opacity');
$background_color_rgba    = $this->hex_to_rgba($background_color, $background_color_opacity);
$background_image         = $this->get_option('background_image');
$statistics_consent       = isset($_COOKIE['cmplz_statistics']) ? sanitize_text_field(wp_unslash($_COOKIE['cmplz_statistics'])) : '';
$analytics                = $this->get_option('analytics');
$ga_tracking_id           = $this->get_option('ga_tracking_id');
$matomo_url               = $this->get_option('matomo_url');
$gdpr_bypass              = $this->get_option('gdpr_bypass');
?>
<?php if ($statistics_consent === 'allow' || $gdpr_bypass) : ?>
  <?php if ($analytics === 'google') : ?>
    <?php if (!empty($ga_tracking_id)) : ?>
      <!-- Google tag (gtag.js) -->
      <script async src='https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html($ga_tracking_id); ?>'></script>
      <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', '<?php echo esc_js($ga_tracking_id); ?>');
      </script>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($analytics === 'matomo') : ?>
    <?php if (!empty($matomo_url)) : ?>
      <!-- Matomo -->
      <script type="text/javascript">
        var _paq = window._paq = window._paq || [];
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
          var u="<?php echo esc_js($matomo_url); ?>";
          _paq.push(['setTrackerUrl', u+'matomo.php']);
          _paq.push(['setSiteId', {$IDSITE}]);
          var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
          g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
        })();
      </script>
      <!-- End Matomo Code -->
    <?php endif; ?>
  <?php endif; ?>
<?php endif; ?>

<style>
  body {
    color: <?php echo $color ? esc_html($color) : '#ffffff'; ?>;
    background-color: <?php echo esc_html($background_color_rgba); ?>;
    background-image: linear-gradient(<?php echo esc_html($background_color_rgba); ?>, <?php echo esc_html($background_color_rgba); ?>), url('<?php echo $background_image ? esc_url($background_image) : ''; ?>');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
  }
</style>