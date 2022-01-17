jQuery(document).ready(function ($) {
  if ($('.sp-pcp-container').length > 0) {
    $('.sp-pcp-container').each(function () {

      var pcp_container = $(this),
        pcp_container_id = pcp_container.attr('id'),
        PCP_Wrapper_ID = '#' + pcp_container_id,
       // pc_sid = $(PCP_Wrapper_ID).data('sid'), // The Shortcode ID.
        pcpCarousel = $('#' + pcp_container_id + ' .sp-pcp-carousel'),
        /* pcpfilter = $('#' + pcp_container_id + '.pcp-filter-wrapper'),
        ajaxurl = sppcp.ajaxurl,
        nonce = sppcp.nonce, */
        pcpCarouselData = pcpCarousel.data('carousel');
      if (pcpCarousel.length > 0) {
         // Carousel Swiper for Standard mode.
          var pcpSwiper = new Swiper('#' + pcp_container_id + ' .sp-pcp-carousel', {
            speed: pcpCarouselData.speed,
            slidesPerView: pcpCarouselData.slidesPerView.mobile,
            spaceBetween: pcpCarouselData.spaceBetween,
            loop: pcpCarouselData.loop,
            loopFillGroupWithBlank: true,
            autoHeight: pcpCarouselData.autoHeight,
            simulateTouch: pcpCarouselData.simulateTouch,
            allowTouchMove: pcpCarouselData.allowTouchMove,
            centeredSlides: pcpCarouselData.center_mode,
            mousewheel: pcpCarouselData.slider_mouse_wheel,
            lazy: pcpCarouselData.lazy,
            //grabCursor: true,
            pagination:
              pcpCarouselData.pagination == true
                ? {
                  el: '.swiper-pagination',
                  clickable: true,
                  dynamicBullets: pcpCarouselData.dynamicBullets,
                  renderBullet: function (index, className) {
                    if (pcpCarouselData.bullet_types == 'number') {
                      return (
                        '<span class="' +
                        className +
                        '">' +
                        (index + 1) +
                        '</span>'
                      )
                    } else {
                      return '<span class="' + className + '"></span>'
                    }
                  }
                }
                : false,
            autoplay: {
              delay: pcpCarouselData.autoplay_speed
            },
            navigation:
              pcpCarouselData.navigation == true
                ? {
                  nextEl: '.pcp-button-next',
                  prevEl: '.pcp-button-prev'
                }
                : false,
            breakpoints: {
              576: {
                slidesPerView: pcpCarouselData.slidesPerView.mobile_landscape,
                navigation:
                  pcpCarouselData.navigation_mobile == true
                    ? {
                      nextEl: '.pcp-button-next',
                      prevEl: '.pcp-button-prev'
                    }
                    : false,
                pagination:
                  pcpCarouselData.pagination_mobile == true
                    ? {
                      el: '.swiper-pagination',
                      clickable: true,
                      dynamicBullets: pcpCarouselData.dynamicBullets,
                      renderBullet: function (index, className) {
                        if (pcpCarouselData.bullet_types == 'number') {
                          return (
                            '<span class="' +
                            className +
                            '">' +
                            (index + 1) +
                            '</span>'
                          )
                        } else {
                          return '<span class="' + className + '"></span>'
                        }
                      }
                    }
                    : false
              },
              768: {
                slidesPerView: pcpCarouselData.slidesPerView.tablet,
              },
              992: {
                slidesPerView: pcpCarouselData.slidesPerView.desktop,
              },
              1200: {
                slidesPerView: pcpCarouselData.slidesPerView.lg_desktop,
              },
            },
            fadeEffect: {
              crossFade: true
            },
            ally: {
              enabled: pcpCarouselData.enabled,
              prevSlideMessage: pcpCarouselData.prevSlideMessage,
              nextSlideMessage: pcpCarouselData.nextSlideMessage,
              firstSlideMessage: pcpCarouselData.firstSlideMessage,
              lastSlideMessage: pcpCarouselData.lastSlideMessage,
              paginationBulletMessage: pcpCarouselData.paginationBulletMessage
            },
            keyboard: {
              enabled: true
            }
          })
          if (pcpCarouselData.autoplay === false) {
            pcpSwiper.autoplay.stop()
          }
          if (pcpCarouselData.stop_onHover && pcpCarouselData.autoplay) {
            $(pcpCarousel).hover(
              function () {
                pcpSwiper.autoplay.stop()
              },
              function () {
                pcpSwiper.autoplay.start()
              }
            )
          }
          $(window).resize(function () {
            pcpSwiper.update()
          })
          $(window).trigger("resize")
        }

      /**
       * Grid masonry.
       */
      if ($(PCP_Wrapper_ID).hasClass('pcp-masonry')) {
        var $post_wrapper = $('.sp-pcp-row', PCP_Wrapper_ID)
        $post_wrapper.imagesLoaded(function () {
          $post_wrapper.masonry()
        })
      }
      /* Preloader js */
      $(document).ready(function() {
        $(".pcp-preloader", PCP_Wrapper_ID).css({ "backgroundImage": "none", "visibility": "hidden" });
      });
    })
  }
})
