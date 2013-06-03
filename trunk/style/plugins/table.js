$(function() {
    return false;
    $("head")
    .append("<style> .resizing *{  cursor: col-resize;-webkit-user-select:none}</style>");
    var pressed = false;
    var start = undefined;
    var startX, startWidth;

    $(document).onMousemove(function(e) {
        if(pressed) {
            $(start).css({
                width: (startWidth+(e.pageX-startX)) +"px"
            });
        }
    });

    $(document).onMouseup(function() {
        if(pressed) {
            $('body').removeClass("resizing");
            pressed = false;
        }
    });

    APP_CALLBACK['table_resize'] = function(){
        $("table th").onMousedown(function(e) {
            if( Math.abs(this.offsetWidth - e.offsetX) > 5 ){
                return;
            }

            start = $(this);
            pressed = true;
            startX = e.pageX;
            startWidth = $(this).width()
            - parseInt($(this).css("padding-left"))||0;
            - parseInt($(this).css("padding-right"))||0;
            - parseInt($(this).css("border-right-width"))||0
            - parseInt($(this).css("border-left-width"))||0;


            $('body').addClass("resizing");
            e.preventDefault();
        })
        .onMouseover(function(e) {
            if( Math.abs(this.offsetWidth - e.offsetX) > 5 ){
                return;
            }
            $('body').addClass("resizing");
            e.preventDefault();
        })
        .onMouseout(function(e) {
            //$('body').removeClass("resizing");
            e.preventDefault();
        });
    };
});