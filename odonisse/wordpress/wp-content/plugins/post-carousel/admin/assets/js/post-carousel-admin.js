; (function ($) {
  'use strict'

  /**
   * JavaScript code for admin dashboard.
   *
   */

  $(function () {
    /* Preloader */
    $("#sp_pcp_view_options .spf-metabox").css("visibility", "hidden");

    var PCP_layout_type = $(
      '#spf-section-sp_pcp_layouts_1 .spf-field-layout_preset .spf-siblings .spf--sibling'
    )
    var PCP_get_layout_value = $(
      '#spf-section-sp_pcp_layouts_1 .spf-field-layout_preset .spf-siblings .spf--sibling.spf--active'
    )
      .find('input')
      .val()

    // Carousel Layout.
    if (PCP_get_layout_value !== 'carousel_layout') {
      $(
        '#sp_pcp_view_options .spf-nav ul li.menu-item_sp_pcp_view_options_3'
      ).hide()
    } else {
      $(
        '#sp_pcp_view_options .spf-nav ul li.menu-item_sp_pcp_view_options_3'
      ).show()
    }

    /**
     * Show/Hide tabs on changing of layout.
     */
    $(PCP_layout_type).on('change', 'input', function (event) {
      //event.stopPropagation()
      var PCP_get_layout_value = $(this).val();

      // Carousel Layout.
      if (PCP_get_layout_value !== 'carousel_layout') {
        $(
          '#sp_pcp_view_options .spf-nav ul li.menu-item_sp_pcp_view_options_3'
        ).hide()
      } else {
        $(
          '#sp_pcp_view_options .spf-nav ul li.menu-item_sp_pcp_view_options_3'
        ).show()
      }
    })

    /* Preloader js */
    $("#sp_pcp_view_options .spf-metabox").css({ "backgroundImage": "none", "visibility": "visible", "minHeight": "auto" });
    $("#sp_pcp_view_options .spf-nav-metabox li").css("opacity", 1);

      /* Copy to clipboard */
  $('.pcp-shortcode-selectable').click(function (e) {
    e.preventDefault();
    pcp_copyToClipboard($(this));
    pcp_SelectText($(this));
    $(this).focus().select();
    $('.pcp-after-copy-text').animate({
      opacity: 1,
      bottom: 25
    }, 300);
    setTimeout(function () {
      jQuery(".pcp-after-copy-text").animate({
        opacity: 0,
      }, 200);
      jQuery(".pcp-after-copy-text").animate({
        bottom: 0
      }, 0);
    }, 2000);
  });
  $('.sp_pcp_input').click(function (e) {
    e.preventDefault();
    /* Get the text field */
    var copyText = $(this);
    /* Select the text field */
    copyText.select();
    document.execCommand("copy");
    $('.pcp-after-copy-text').animate({
      opacity: 1,
      bottom: 25
    }, 300);
    setTimeout(function () {
      jQuery(".pcp-after-copy-text").animate({
        opacity: 0,
      }, 200);
      jQuery(".pcp-after-copy-text").animate({
        bottom: 0
      }, 0);
    }, 2000);
  });
   function pcp_copyToClipboard(element) {
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val($(element).text()).select();
      document.execCommand("copy");
      $temp.remove();
    }
    function pcp_SelectText(element) {
      var r = document.createRange();
      var w = element.get(0);
      r.selectNodeContents(w);
      var sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(r);
    }

  })
})(jQuery)
