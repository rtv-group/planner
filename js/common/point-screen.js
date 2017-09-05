var MAX_INTERVAL_COUNTER = 3000;

$(document).ready(function(e){
  $(".points-list").on("click", '.show-point-screen', function(e){
      var this$ = $(this),
        pointId = this$.data("id"),
        pointIp = this$.data("ip"),
        intervalCounter = 100;

    if((this$.data('clicked') == 'false') ||
        (this$.data('clicked') == undefined)){
      this$.data('clicked', 'true');
      var curScreenBox$ = $("<div></div>")
        .addClass('ScreenShotBox ScreenShotBoxBgLoading')
        .click(function() {
            $(this).remove();
            this$.data('clicked', 'false');
        })
        .css({
          'top': e.pageY - 120,
          'left': e.pageX - 360
        })
        .append(
          $("<img/>")
            .addClass('ScreenShotImg')
        )
        .appendTo('body')
        .fadeIn();

      $.ajax({
            url: 'http://' + window.location.host + "/point/ajaxGetPointScreen",
            type: "POST",
            data: {
              pointId: pointId,
              pointIp: pointIp
            },
            dataType: "json",
      }).done(function(answ){
        console.log(answ);
        if ((answ != null) && (answ[0] == 'ok')){
          curScreenBox$.find('.ScreenShotImg').attr('src', answ[1])
        } else if((answ != null) && (answ[0] == 'pending')) {
            var interval = setInterval(function() {
                $.ajax({
                    url: 'http://' + window.location.host + "/point/getScreenshotUrl?pointId=" + pointId,
                    dataType: "json"
                }).done(function(resp) {
                    if (resp.url) {
                        clearInterval(interval);
                        curScreenBox$.find('.ScreenShotImg').attr('src', resp.url);
                    }

                    if (intervalCounter > MAX_INTERVAL_COUNTER) {
                        clearInterval(interval);
                        curScreenBox$
                          .removeClass('ScreenShotBoxBgLoading')
                          .addClass('ScreenShotBoxBgUnavaliable');
                    }
                    intervalCounter*=2;
                }).fail(function(answ) {
                    if (intervalCounter > MAX_INTERVAL_COUNTER) {
                        clearInterval(interval);
                        curScreenBox$
                          .removeClass('ScreenShotBoxBgLoading')
                          .addClass('ScreenShotBoxBgUnavaliable');
                    }
                    intervalCounter*=2;
                });
            }, intervalCounter);
        }
        else {
          curScreenBox$
            .removeClass('ScreenShotBoxBgLoading')
            .addClass('ScreenShotBoxBgUnavaliable');
        }
      }).fail(function(msg){
        curScreenBox$
          .removeClass('ScreenShotBoxBgLoading')
          .addClass('ScreenShotBoxBgUnavaliable');
      });
    } else {
      this$.data('clicked', 'false');
      $(".ScreenShotBox").remove();
    }

  });
});
