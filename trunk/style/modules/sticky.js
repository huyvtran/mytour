//Tao ra mot box co the drag drop
var OWL_CACHE_STICKY = [];
var STICKY_ZINDEX = 2000;
Owl.createSticky  = function( options ){
    var settings = $.Extend({
        center: true,
        title: "",
        css:{},
        css_content:{},
        postID: 0,
        id: 'sticky-'+(new Date).getTime(),
        auto_hidden: false
    //callback:
    },options||{});

    var obj_drag = null, drag_x =0, drag_y = 0, obj_resize = null,
    resize_x = 0, resize_y = 0, resize_width = 0, resize_height = 0;

    //Xoa sticky cu~
    $('#'+settings.id).remove();


    function sticky_update(){
        if( this.ajax_post )
            this.ajax_post.stop();
        var elem = this;
        this.ajax_post = $.Ajax(baseURL+"/Sticky/Index/Edit",{
            type: 'post',
            data: {
                ID:	settings.postID,
                content: this.value,
                dx: $(OWL_CACHE_STICKY[settings.id]).left(),
                dy: $(OWL_CACHE_STICKY[settings.id]).top(),
                mW: parseInt($(this).css('width')),
                mH: parseInt($(this).css('height'))
            },
            create: function(){
                $(elem).next(0).htm('Đang lưu ...');
            },
            success: function(){
                $(elem).next(0).htm('&nbsp;');
            }
        });
    }

    function sticky_delete(){
        $.Ajax(baseURL+"/Sticky/Index/Delete",{
            data: {
                ID:	settings.postID
            }
        });
    }

    $(document).onMousemove(function( event ){
        if( obj_drag ){
            $( obj_drag ).css({
                left: Math.min( Math.max(0, event.pageX - drag_x),
                    screen.width - $(obj_drag).width() -10
                    ) + 'px',
                top: Math.min( Math.max(0, event.pageY - drag_y),
                    screen.height - $(obj_drag).height()-10
                    ) + 'px'
            });
        }

        if( obj_resize ){
            $( obj_resize )
            .css({
                width: resize_width + event.pageX - resize_x +'px',
                height: resize_height + event.pageY - resize_y +'px'
            })
        }
    });

    if( !OWL_CACHE_STICKY[settings.id] ){
        OWL_CACHE_STICKY[settings.id] = $("<div class='sticky"+(settings.auto_hidden ? ' app-auto-hidden': '')+"' id='"+ settings.id +"'>"
            +"<div class='sticky-bar'>"+settings.title+"<a class='sticky-close'></a></div>"
            +"<div class='sticky-content'><textarea spellcheck='false' class='sticky-text' style='resize:none'></textarea><div class='sticky-status'></div></div></div>").k(0);
    }

    $(document )
    .onMouseup(function( event ){
        $(obj_drag).find("textarea").each(sticky_update);
        $(obj_resize).each(sticky_update);

        obj_drag = null;
        obj_resize = null;
        $("body").removeClass('sticky-unselect');
        $("body").removeClass('sticky-resize');
    });
    $(OWL_CACHE_STICKY[settings.id])
    .find(".sticky-content")
    .append("<div class='sticky-resize-icon'></div>")
    .find(".sticky-resize-icon")
    .onMousedown(function( event ){
        $("body").addClass('sticky-resize');
        obj_resize = $(this.parentNode).find("textarea").k(0);
        resize_x = event.pageX;
        resize_y = event.pageY;
        resize_width = $(obj_resize).width();
        resize_height = $(obj_resize).height();
    });

    $(OWL_CACHE_STICKY[settings.id])
    .css("z-index:"+(STICKY_ZINDEX++))
    .find(".sticky-bar .sticky-close")
    .onClick(function(){
        sticky_delete();
        $('#'+settings.id)
        .stop()
        .animate({
            opacity: 0
        },{
            duration:100,
            callback: function(){
                $(this).remove();
            }
        });
    });

    $( OWL_CACHE_STICKY[settings.id] )
    .appendTo('body')
    .onMousedown(function( event ){
        $(this).css("z-index:"+ (STICKY_ZINDEX++));
        drag_x = event.offsetX;
        drag_y = event.offsetY;
    })
    .onMouseup(function(){
        obj_drag = null;
        obj_resize = null;
        $("body").removeClass('sticky-unselect');
        $("body").removeClass('sticky-resize');
    })
    .css( settings.css||{})
    .child(".sticky-bar")
    .onMousedown(function( event ){
        obj_drag = this.parentNode;
        $("body").addClass('sticky-unselect').selectText(0,-1);
    //event.preventDefault();
    //event.stopPropagation();
    })
    .parent(0)
    .child(".sticky-content")
    .css( settings.css_content||{})
    .each(function(){
        $(this)
        .child('textarea')
        /*.each(function(){
					this.old_height = this.scrollHeight;
				})*/
        .each(options.callback|| function(){})
        /*.onKeypress(function(){
					if( this.scrollHeight < 300  && this.old_height < this.scrollHeight ){
						this.style.height = this.scrollHeight +"px";
					}
					this.old_height = this.scrollHeight;
				})*/
        .onKeyup(sticky_update);
    })
    .parent(0)
    .each(function(){
        //canh giua tu dong
        if( settings.center ){
            $(this).css({
                top: 0.8*( Math.max(parseInt(document.body.scrollTop)||0) + $(window).height()/2 - $(this).height()/2 )-20 + "px",
                left: $(window).width()/2 - $(this).width()/2 + "px",
                opacity:0.5
            })
            .stop()
            .animate({
                opacity:1
            },{
                duration:50
            });
        }
    });
};


/* Load sticky */
function app_load_sticky(){
    $.Ajax( baseURL+'/Sticky',{
        success: function( data ){
            eval("var a="+data);
            for(var x in a){
                if( $('#sticky-'+a[x].ID).k(0) ){
                //	continue;
                }
                Owl.createSticky({
                    id: 'sticky-'+a[x].ID,
                    postID: a[x].ID,
                    title: "<b>"+(a[x].date||"&nbsp;")+"</b>",
                    center:false,
                    css:{
                        top: a[x].y +'px',
                        left: a[x].x +'px'
                    },
                    callback: function(){
                        this.value = a[x].content;
                        $(this).css({
                            width: a[x].width   +'px',
                            height: a[x].height +'px'
                        });
                    }
                });
            }
        }
    });
}

function open_sticky(){
    $.Ajax(baseURL+"/Sticky/Index/Add",{
        type: 'post',
        data: {
            content:''
        },
        create: function(){
            ajax_show();
        },
        success: function( id ){
            ajax_hide();
            $.createSticky({
                postID: id,
                title: "<b>Ghi chú mới</b>"
            });
        }
    });
}

app_load_sticky();









