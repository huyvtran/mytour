(function(){
    $(document).onMousedown(function(){
        for( var x in CACHE ){
            if( !CACHE[x].focused ){
                $(CACHE[x]).hide();
            }
            CACHE[x].focused = false
        }
    });

    var CACHE=[];
    APP_CALLBACK['context'] = function(){
        return;
        $(this).find(".userlink").addEvent('contextmenu',function( event ){
            var div = null;
            if( !this.div_ctx ){
                div = $("<div></div>")
                .onClick(function(){
                    this.focused = true;
                })
                .appendTo('body')
                .k(0);
                this.div_ctx = div;
                CACHE.push(div);
            }else{
                div = this.div_ctx;
            }

            $(div)
            .css({
                position:'absolute',
                display:'inline-block',
                background:'#EDEFF4',
                border:'1px solid #96A6CE',
                'border-right-width':'2px',
                left: $(this).left()+'px',//event.pageX+'px',
                top: $(this).top()+$(this).height()+'px'//event.pageY+'px'
            })
            .htm(
                "<div class='context-link context-chat'><a>Trò chuyện</a></div>"
                +"<div class='context-link context-message'><a>Gửi tin nhắn</a></div>"
                +"<div class='context-link context-email'><a>Gửi email</a></div>"
                +"<div class='context-link context-info'><a>Thông tin cá nhân</a></div>"
                )

            event.preventDefault();
            event.stopPropagation();
        },'contextmenu');

        $(this).find(".x-context").addEvent('contextmenu',function( event ){
            var div = null;
            if( !this.div_ctx ){
                div = $(this)
                .child(".x-context-menu")
                .onClick(function(){
                    this.focused = true;
                })
                .appendTo('body')
                .k(0);
                this.div_ctx = div;
                CACHE.push(div);
            }else{
                div = this.div_ctx;
            }

            $(div)
            .css({
                position:'absolute',
                display:'inline-block',
                left: $(this).left()+'px',
                top: $(this).top() + $(this).height()+'px'
            });

            event.preventDefault();
            event.stopPropagation();
        },'contextmenu');
    };
})();