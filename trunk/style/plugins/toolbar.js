Owl(window).onLoad(function(){
    var $ = Owl;
    return true;
    $(window)
    .addEvent('resize scroll',function( event ){
        var _side = $("#side").k(0),width;
        var scrollTop  = Math.max(document.documentElement.scrollTop,
            document.body.scrollTop);
        var scrollLeft = Math.max(document.documentElement.scrollLeft,
            document.body.scrollLeft);

        if( !_side ) return;

        if( !_side.figgy ){
            _side.figgy = _side.cloneNode(true);

            $(_side.figgy)
            .css({
                display:'none',
                visibility: 'hidden'
            })
            .afterTo(_side);
        }

        var side = _side.figgy;

        if( _side.top === undefined || event.type == 'resize'){
            _side.top = $(_side).top();
            _side.left = $(_side).left();
        }

        if( scrollTop > _side.top ){
            if( $(_side).css('position') != 'fixed' || event.type == 'resize' ){
                $(_side).css({
                    position:'fixed',
                    top: '0px',
                    zIndex: 10,
                    width: $(_side).width()+'px'
                });
                $(side)
                .css('display:block');
            }

            $(_side).css({
                left: ( _side.left - scrollLeft )+'px'
            });
        }else{
            if( $(_side).css('position') == 'fixed' ){
                $(_side).css({
                    position:'static'
                });
                $(side)
                .css('display:none');
            }
        }

        //off
        return true;
        var _menu = $("#content .x-list-title").k(0);
        if( !_menu ) return;
        if( !_menu.figgy ){
            _menu.figgy = _menu.cloneNode(true);
            $(_menu.figgy)
            .css({
                display:'none',
                visibility: 'hidden'
            })
            .afterTo(_menu);
        }

        var menu = _menu.figgy;

        if( _menu.top === undefined || event.type == 'resize' ){
            _menu.top = $(_menu).top();
            _menu.left = $(_menu).left();
        }

        if(  scrollTop > _menu.top ){

            if( $(_menu).css('position') != 'fixed' || event.type == 'resize' ){
                $(_menu).css('position:static');

                width = $(_menu).width()
                - parseInt($(_menu).css('padding-left')||0)
                - parseInt($(_menu).css('padding-right')||0)
                - parseInt($(_menu).css('border-left-width')||0)
                - parseInt($(_menu).css('border-right-width')||0);

                $(menu)
                .css('display:block');

                $(_menu).css({
                    position: 'fixed',
                    width: width+'px',
                    top:'0px',
                    zIndex:10,
                    boxShadow:'0px 1px 1px #ccc',
                    WebkitBoxShadow:'0px 1px 1px #ccc',
                    MozShadow:'0px 1px 1px #ccc',
                    OShadow:'0px 1px 1px #ccc'
                });
            }

            $(_menu).css({
                left: ( _menu.left - scrollLeft ) +'px'
            });

        }else{
            if( $(_menu).css('position') == 'fixed' ){
                $(_menu).css({
                    position:'static',
                    width:'auto',
                    boxShadow:'0px 1px 0px #ccc',
                    WebkitBoxShadow:'0px 0px 0px #ccc',
                    MozShadow:'0px 0px 0px #ccc',
                    OShadow:'0px 0px 0px #ccc'
                });
                $(menu)
                .css('display:none');
            }
        }
    });
});