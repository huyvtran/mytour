/*
	NOTICE
	@ Create: ducminh_ajax
	@ Update: 25/01/2012
*/

Owl(function( $ ){
    var delay = 1;

    function showLabel( n ){
        if( n == 0 ){
            $("#notice-option .nice-title")
            .css("display:none");
            return;
        }

        $("#notice-option .nice-title")
        .css({
            display:'inline-block'
        })
        .find(".nice-title-content")
        .htm(n+"")
    }

    function showNotice( a ){
        $("#noticesound")
        .each(function(){
            try{
                this.play()
            }catch(e){}
        });

        var cc = (  a.created_by_photo ?
            "<img style='margin-right:2px;width:60px;height:60px;padding:1;border:1px solid #ccc;float:left' src='"+baseURL+"/files/photo/"+a.created_by_photo+"'/>"
            : ("<img style='margin-right:2px;width:60px;height:60px;padding:1;border:1px solid #ccc;float:left' src='"+baseURL+"/files/photo/noavatar.gif'/>"))
        +"<a href='"+a.url+"'>"+a.title+"</a>"
        +"<br/>"+a.content;

        $("<div>"
            +"<div style='position:relative;font-size:13px;font-weight:bold;background:#3B5999;padding:8px 0px;color:#fff'>Thông báo mới"
            +"<a class='close' style='background:url("+baseURL+"/style/images/close.gif);display:inline-block;width:19px;height:19px;position:absolute;right:0px;top:9px'></a>"
            +"</div><div style='background:#fff;width:250px;height:70px;overflow:auto;padding:8px 3px'><div class='ncontent'>"+cc+"</div></div>"
            +"</div>")
        .appendTo('body')
        .css({
            position:'fixed',
            border: '6px solid #3B5999',
            borderTop:'0px',
            overflow:'hidden',
            background:'#fff',
            'z-index':2000,
            bottom:'-500px',
            right:'0px'
        })
        .animate({
            bottom:'1px'
        },{
            duration: 600,
            easing: 'outCubic'
        })
        .each(function(){
            var elem = this;
            $(this)
            .find(".close")
            .onClick(function(){
                clearTimeout(this.tt);
                $(elem)
                .stop()
                .animate({
                    bottom: '-500px'
                },{
                    duration: 500,
                    easing: 'inCubic',
                    callback: function(){
                        $(this).remove()
                    }
                })
            })
            .each(function(){
                var e = this;
                this.tt = setTimeout(function(){
                    clearTimeout(e.tt);
                    $(elem)
                    .stop()
                    .animate({
                        bottom: '-500px'
                    },{
                        duration: 500,
                        easing: 'inCubic',
                        callback: function(){
                            $(this).remove()
                        }
                    })
                },7000);
            });

        });
    }

    function getNotice(){
        var self = arguments.callee,
        time = Math.max(4000,  (window.timeNotice||15)*1000 );
        $.Ajax(baseURL+"/Notice/Index/Check",{
            cache: false,
            error: function(){
                delay++;
                setTimeout(self,delay*time);
            },
            success: function( data ){
                try{
                    eval("data="+data);
                    showLabel( data['num']||0 );
                    if( data['post'] ){
                        showNotice( data['post'] );
                    }

                    if( data['message'] && data['message'] != 0 ){
                        $('#message-notice')
                        .htm(" (<b>"+data['message']+"</b>)")
                    }else{
                        $('#message-notice')
                        .htm("");
                    }

                }catch(e){
                    e.stupid=1; // :)
                    return false;
                }
                delay = 1;
                setTimeout(self,time);
            }
        });
    }

    $("#notice-option")
    .onClick(function(){
        load_frame(baseURL+'/Notice',{
            //resize: {selector:'.lightbox-content'},
            title: 'Thông báo gần đây',
            center: false,
            id: 'dashboard',
            css_content:{
                padding:'0px'
            }
        },
        function(){
            var f = arguments.callee;
            $(this)
            .find(".reall")
            .onClick(function(  event ){
                $.Ajax(baseURL+'/Notice/Index/Deleteall',{
                    create: ajax_show,
                    complete: ajax_hide,
                    error: ajax_hide,
                    success: function(data){
                        $(this)
                        .htm(data)
                        .each(f);
                    }
                });
                event.preventDefault();
                event.stopPropagation();
            });
        }
        );
    });

    $("#app-option")
    .onClick(function(e){
        $("#app-list").toggle();
        e.stopPropagation();
        e.preventDefault()
    });

    $(document)
    .onClick(function(){
        $("#app-list").css({
            display: 'none'
        });
    })

    setTimeout(getNotice,5000);

});
