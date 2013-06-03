/*
 * Make scrollbar
 * Version: 1.0
 */

function getScrollBarWidth () {
    if(window.SCROLLBAR_SIZE)
        return SCROLLBAR_SIZE;
    var inner = document.createElement('p');
    inner.style.width = "100%";
    inner.style.height = "200px";

    var outer = document.createElement('div');
    outer.style.position = "absolute";
    outer.style.top = "0px";
    outer.style.left = "0px";
    outer.style.visibility = "hidden";
    outer.style.width = "200px";
    outer.style.height = "150px";
    outer.style.overflow = "hidden";
    outer.appendChild (inner);

    document.body.appendChild (outer);
    var w1 = inner.offsetWidth;
    outer.style.overflow = 'scroll';
    var w2 = inner.offsetWidth;
    if (w1 == w2) w2 = outer.clientWidth;

    document.body.removeChild (outer);
    SCROLLBAR_SIZE = (w1 - w2);
    return SCROLLBAR_SIZE;
}

(function($){
    var defaultSettings = {
        keyStep: 10,
        wheelStep: 10
    };

    /* current elem can be scrollable with key */
    var elemKey, elemFocus, elemMove, elemWheel;

    /* function scroll vertical */
    function scrollVertical(elem,vector,type){
        if(elem._btnY){
            var btnY = elem._btnY,
            r = $(elem).find('.scrollbar-track-horizotal').height()||0,
            arrowBottom = btnY.arrowBottom,
            arrowTop = btnY.arrowTop,
            top = (type ? (parseFloat($(btnY).css('top'))||0) : elem._posY ) + vector,
            maxTop = btnY.parentNode.offsetHeight - btnY.offsetHeight - arrowTop.offsetHeight - arrowBottom.offsetHeight;
            top = Math.max(arrowTop.offsetHeight,top);
            top = Math.min(top,maxTop+arrowTop.offsetHeight);

            var scrollTop = Math.round( ((top-arrowTop.offsetHeight)/maxTop)*(btnY.H - btnY.h + r));

            $(btnY.parentNode)
            .css({
                top: scrollTop + 'px'
            });

            $(btnY)
            .css({
                top: top + 'px'
            });

            $(elem)
            .find('.scrollbar-track-horizotal')
            .each(function(){
                $(this).css({
                    top: (btnY.h + scrollTop - $.Real(this).offsetHeight ) +'px'
                });
            });
            elem.scrollTop = scrollTop;
            return true;
        }
        return false;
    }

    /* scroll horizotal*/
    function scrollHorizotal(elem,vector,type){
        if(elem._btnX){
            var btnX = elem._btnX,
            r = $(elem).find('.scrollbar-track-vertical').width()||0,
            arrowRight = btnX.arrowRight,
            arrowLeft = btnX.arrowLeft,
            left = (type ? (parseFloat($(btnX).css('left'))||0) : elem._posX ) + vector,
            maxLeft = btnX.parentNode.offsetWidth - btnX.offsetWidth - arrowLeft.offsetWidth - arrowRight.offsetWidth;

            left = Math.max(arrowLeft.offsetWidth,left);
            left = Math.min(left,maxLeft+arrowLeft.offsetWidth);
            elem = btnX.elem;
            var misc = elem._store.padRight,
            scrollLeft = Math.round( ((left-arrowLeft.offsetWidth)/maxLeft)*(btnX.W + misc - btnX.w + r));

            $(btnX.parentNode)
            .css({
                left: scrollLeft + 'px'
            });

            $(btnX)
            .css({
                left: left + 'px'
            });

            $(elem)
            .find('.scrollbar-track-vertical,.scrollbar-conner')
            .each(function(){
                $(this).css({
                    left: (btnX.w + scrollLeft - $.Real(this).offsetWidth ) +'px'
                });
            });
            elem.scrollLeft = scrollLeft;
            return false;
        }
        return true;
    }


    /* add event for document */
    function initEventDocument(){
        $(document)
        .addEvent('selectstart:scroll',function(event){
            //disable selection on ie
            if(elemMove){
                event.preventDefault();
                event.stopPropagation();
            }
        })
        .addEvent('mousemove:scroll',function(event){
            if(elemMove){
                if( elemMove._direction == 'vertical'){
                    scrollVertical(elemMove, event.pageY - elemMove._pageY);
                }else{
                    scrollHorizotal(elemMove, event.pageX - elemMove._pageX);
                }
                $('body')
                .addClass('no-select');
                event.preventDefault();
                event.stopPropagation();
            }
        })
        .addEvent('mouseup:scroll',function(){
            elemMove = null;
            $('body')
            .removeClass('no-select');
        })
        .addEvent('keydown:scroll',function( event ){
            if( elemKey ){
                var step = elemKey._scrollOpts.keyStep;
                if( event.KEY_UP || event.KEY_DOWN ){
                    scrollVertical(elemKey,(event.KEY_UP ? -1 : 1)*step,true);
                    event.preventDefault();
                }else if( event.KEY_LEFT ||event.KEY_RIGHT ){
                    scrollHorizotal(elemKey,(event.KEY_LEFT ? -1 : 1)*step,true);
                    event.preventDefault();
                }
            }
        });
    }

    /* cross focus event */
    function initFocus(elem){
        $(elem)
        .addEvent('click:scroll',function(){
            if(!elemFocus){
                elemFocus = true;
                this._btnX = $(this).find('.scrollbar-track-horizotal .scrollbar-button').k(0);
                this._btnY = $(this).find('.scrollbar-track-vertical .scrollbar-button').k(0);
                elemKey = this;
            }
        });
        $(document)
        .addEvent('click:scrollkey',function(){
            if( !elemFocus ){
                elemKey = null;
            }
            elemFocus = false;
        });
    }

    /* wheel event */
    function initWheel(elem){
        $(elem)
        .addEvent('mousewheel',function(event){
            var step = this._scrollOpts.wheelStep;
            var i = scrollVertical(this,event.delta*step,true);
            if(i)
                event.preventDefault();
        });
    }

    $.extendDOM('showScroll',function( opts ){
        opts = $.Extend(defaultSettings,opts||{});

        return this.each(function(){
            var elem = this,
            dim = $.Real(this)
            pos = $(this).css('position'),
            store = {};

            elem._scrollOpts = opts;

            var borderTop = parseFloat($(this).css('border-top-width'))||0,
            borderBottom = parseFloat($(this).css('border-bottom-width'))||0,
            borderLeft = parseFloat($(this).css('border-left-width'))||0,
            borderRight = parseFloat($(this).css('border-right-width'))||0,
            padTop = parseFloat($(this).css('padding-top'))||0,
            padBottom = parseFloat($(this).css('padding-bottom'))||0,
            padLeft = parseFloat($(this).css('padding-left'))||0,
            padRight = parseFloat($(this).css('padding-right'))||0,
            H = dim.scrollHeight - borderTop - borderBottom,
            h = dim.offsetHeight - borderTop - borderBottom,
            W = dim.scrollWidth - borderLeft - borderRight,
            w = dim.offsetWidth - borderLeft - borderRight,
            $track, trackY;

            //set position for element
            if( pos != 'absolute' && pos != 'relative' ){
                store.pos = pos;
                $(this).css({
                    position:'relative'
                });
            }
            store.padRight = padRight;
            elem._store = store;

            $(this).css({
                overflow:'hidden'
            });

            //test elem has scroll
            var scrollVertical = dim.scrollHeight > dim.offsetHeight,
            scrollHorizotal = dim.scrollWidth > dim.offsetWidth;

            //set event for document
            if(scrollVertical||scrollHorizotal){
                initWheel(elem);
                initFocus(elem);
                initEventDocument();
            }

            //focus and key
            if(scrollVertical||scrollHorizotal){
                $(elem)
                .addEvent('click:scroll',function(){
                    if(!elemKey){
                        elemKey = this;
                    }
                });
            }

            //scroll vertical
            if(scrollVertical){
                $track = $('<div class="scrollbar-track scrollbar-track-vertical">'
                    +'<div class="scrollbar-arrow-top"></div>'
                    +'<div class="scrollbar-button"></div>'
                    +'<div class="scrollbar-arrow-bottom"></div>'
                    +'</div>');
                trackY = $track.k(0);
                $track.css({
                    position:'absolute',
                    zIndex:1000,
                    left: (w+dim.scrollLeft)+'px',
                    top: dim.scrollTop +'px',
                    height: h+'px'
                });

                $(this)
                .append($track);

                $track
                .find('.scrollbar-button')
                .each(function(){
                    var arrowTop = $track.find('.scrollbar-arrow-top').k(0);
                    var arrowBottom = $track.find('.scrollbar-arrow-bottom').k(0);
                    //set height for button
                    var a = 2*h - H;
                    if( a > $.Real(this).offsetHeight ){
                        $(this).css({
                            height: a +'px'
                        });
                    }

                    var bW = $.Real(this).offsetWidth;
                    var p = getScrollBarWidth() - bW;
                    $(elem)
                    .css({
                        width: (w - p - padLeft - padRight - bW ) +'px',
                        'padding-right': (padRight+bW) +'px'
                    });

                    $track.css({
                        left: (w - p - bW) +'px'
                    });

                    this.elem = elem;
                    this.arrowTop = arrowTop;
                    this.arrowBottom = arrowBottom;
                    this.H = H;
                    this.h = h;

                    $(arrowTop)
                    .css({
                        position:'absolute',
                        top: '0px',
                        left:'0px'
                    });

                    $(arrowBottom)
                    .css({
                        position:'absolute',
                        bottom:'0px',
                        left:'0px'
                    });

                    $(this)
                    .css({
                        position:'absolute',
                        top: $.Real(arrowTop).offsetHeight + 'px',
                        left:'0px'
                    });
                })
                .onMousedown(function(event){
                    elem._direction = 'vertical';
                    elem._btnY = this;
                    elem._posY = parseFloat(this.style.top)||0;
                    elem._pageY = event.pageY;
                    elemMove = elem;
                });
            }

            //scroll horizotal
            if(scrollHorizotal){
                $track = $('<div class="scrollbar-track scrollbar-track-horizotal">'
                    +'<div class="scrollbar-arrow-left"></div>'
                    +'<div class="scrollbar-button"></div>'
                    +'<div class="scrollbar-arrow-right"></div>'
                    +'</div>');

                $track.css({
                    position:'absolute',
                    zIndex: 1000,
                    left: dim.scrollLeft+'px',
                    width: w+'px',
                    top: (dim.scrollTop+h)+'px'
                });

                $(this)
                .append($track);

                $track
                .find('.scrollbar-button')
                .each(function(){
                    var arrowLeft = $track.find('.scrollbar-arrow-left').k(0),
                    arrowRight = $track.find('.scrollbar-arrow-right').k(0),
                    a = 2*w - W;

                    if( a > $.Real(this).offsetWidth ){
                        $(this).css({
                            width: a+'px'
                        });
                    }

                    var bH = $.Real(this).offsetHeight;
                    //differ with old scrollbar size
                    var p = getScrollBarWidth() - bH;

                    $(elem)
                    .css({
                        height: ( h - p - padTop - padBottom - bH ) +'px',
                        'padding-bottom': (padBottom+bH) +'px'
                    });

                    $track.css({
                        top: (h - p - bH ) +'px'
                    });

                    this.elem = elem;
                    this.arrowLeft = arrowLeft;
                    this.arrowRight = arrowRight;
                    this.W = W;
                    this.w = w;

                    $(arrowLeft)
                    .css({
                        position:'absolute',
                        top: '0px',
                        left:'0px'
                    });

                    $(arrowRight)
                    .css({
                        position:'absolute',
                        top:'0px',
                        right:'0px'
                    });

                    $(this)
                    .css({
                        position:'absolute',
                        top:'0px',
                        left:'0px'
                    });
                })
                .onMousedown(function(event){
                    elem._direction = 'horizotal';
                    elem._btnX = this;
                    elem._posX = parseFloat(this.style.left)||0;
                    elem._pageX = event.pageX;
                    elemMove = elem;
                });

                if(scrollVertical){
                    $('<div class="scrollbar-conner"></div>')
                    .css({
                        position:'absolute',
                        zIndex: 999,
                        left: (w+dim.scrollLeft)+'px',
                        top: '0px',
                        height: H+'px',
                        width: $.Real(trackY).offsetWidth + 'px'
                    })
                    .appendTo(elem);
                }
            }
        });
    });
})( Owl );