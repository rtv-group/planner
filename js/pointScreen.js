$(document).ready(function(e){
  $(".point-list").on("click", '.show-point-screen', function(e){
      var this$ = $(this),
        pointId = this$.data("id"),
        pointIp = this$.data("ip");

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
        if((answ != null) && (answ[0] == 'ok')){
          curScreenBox$.find('.ScreenShotImg').attr('src', answ[1])
        } else {
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
