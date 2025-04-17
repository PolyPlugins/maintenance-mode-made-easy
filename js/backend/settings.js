jQuery(document).ready(function ($) {

  initTabs();
  initSelect2();
  initColorPicker();
  initRangeSlider();
  initMediaUploader();
  initAnalytics();
  initBypass();

  function initTabs() {
    $(".nav-links li a").on("click", function() {
      let selected = $(this).data('section');

      $(".nav-links li a").each(function() {
        let section = $(this).data('section');

        $(this).removeClass("active");

        if (selected == section) {
          $(this).addClass("active");
        }
      });

      $(".tabs .tab").each(function() {
        let tab = $(this);

        $(this).hide();

        if (tab.hasClass(selected)) {
          tab.show();
        }
      });
    });
  }

  function initSelect2() {
    $('#bypass_roles').select2({
      width: '100%',
      dropdownAutoWidth: true,
      placeholder: $('#bypass_roles').data('placeholder'),
      allowClear: true,
      closeOnSelect: true,
      dropdownCssClass: 'wp-core-ui',
      language: {
        noResults: function() {
          return 'No roles Found';
        }
      }
    });
  }

  function initColorPicker() {
    $('#color').wpColorPicker();
    $('#background_color').wpColorPicker();
  }

  function initRangeSlider() {
    var backgroundColorOpacity = $('#background_color_opacity').val();
    $('#range-value-display').text(backgroundColorOpacity);

    $('#background_color_opacity').on('input', function() {
      $('#range-value-display').text($(this).val());
    });
  }

  function initMediaUploader() {
    var mediaUploader;

    // If there's already an image URL, show the "Remove" button on page load
    if ($('#background_image').val() !== '') {
      $('.remove-background-image-button').show();
    }

    // Open the media uploader when the "Select Image" button is clicked
    $('.upload-background-image-button').click(function (e) {
      e.preventDefault();

      // If the media uploader already exists, open it
      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      // Create the media uploader
      mediaUploader = wp.media({
        title: 'Select a Background Image',
        button: {
          text: 'Use This Image'
        },
        multiple: false
      });

      // When an image is selected, run this callback
      mediaUploader.on('select', function () {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        // Set the image URL in the hidden input field
        $('#background_image').val(attachment.url);

        // Display the image preview
        $('.background-image-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;">');

        // Show the "Remove" button
        $('.remove-background-image-button').show();
      });

      // Open the media uploader
      mediaUploader.open();
    });

    $('.remove-background-image-button').click(function () {
      // Clear the image URL input field
      $('#background_image').val('');

      // Remove the image preview
      $('.background-image-preview').html('');

      // Hide the "Remove" button
      $(this).hide();
    });
  }

  function initAnalytics() {
    var analytics = $('#analytics').val();

    if (analytics === 'disabled') {
      $("#ga_tracking_id").parent().parent().hide();
      $("#matomo_url").parent().parent().hide();
      $(".gdpr-bypass-container").parent().parent().hide();
    }

    if (analytics === 'google') {
      $("#matomo_url").parent().parent().hide();
      $(".gdpr-bypass-container").parent().parent().show();
    }

    if (analytics === 'matomo') {
      $("#ga_tracking_id").parent().parent().hide();
      $(".gdpr-bypass-container").parent().parent().show();
    }

    $('#analytics').change(function() {
      analytics = $(this).val();

      if (analytics === 'disabled') {
        $("#ga_tracking_id").parent().parent().hide();
        $("#matomo_url").parent().parent().hide();
        $(".gdpr-bypass-container").parent().parent().hide();
      }

      if (analytics === 'google') {
        $("#ga_tracking_id").parent().parent().show();
        $("#matomo_url").parent().parent().hide();
        $(".gdpr-bypass-container").parent().parent().show();
      }

      if (analytics === 'matomo') {
        $("#matomo_url").parent().parent().show();
        $("#ga_tracking_id").parent().parent().hide();
        $(".gdpr-bypass-container").parent().parent().show();
      }
    });
  }

  function initBypass() {
    var bypass = $('#gdpr_bypass');

    $(bypass).on('change', function() {
      if($(this).prop('checked')) {
        $('#ga_tracking_id').removeAttr('disabled')
        $('#matomo_url').removeAttr('disabled')
      } else {
        $('#ga_tracking_id').attr('disabled', true)
        $('#matomo_url').attr('disabled', true)
      }
    });

    if (bypass.prop('checked')) {
      $('#ga_tracking_id').removeAttr('disabled')
      $('#matomo_url').removeAttr('disabled')
    }
  }

});