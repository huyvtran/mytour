(function($){
    var delayTime = 1;
    var me_name = null,me_img =null;
    var emotions = [
    {
        syntax: '&gt;:)',
        title: 'Quỷ dữ',
        icon: '1.gif'
    },

    {
        syntax: ':((',
        title: 'Khóc',
        icon: '2.gif'
    },

    {
        syntax: ';;)',
        title: 'batting eyelashes',
        icon: '3.gif'
    },

    {
        syntax: '&gt;:D&lt;',
        title: 'big hug',
        icon: '4.gif'
    },

    {
        syntax: '&lt;):)',
        title: 'Cao bồi',
        icon: '5.gif'
    },

    {
        syntax: ':D',
        title: 'big grin',
        icon: '6.gif'
    },

    {
        syntax: ':-/',
        title: 'confused',
        icon: '7.gif'
    },

    {
        syntax: ':x',
        title: 'Yêu đời',
        icon: '8.gif'
    },

    {
        syntax: '&gt;:P',
        title: 'phbbbbt',
        icon: '10.gif'
    },

    {
        syntax: ':-*',
        title: 'Hôn',
        icon: '11.gif'
    },

    {
        syntax: '=((',
        title: 'Tan vỡ',
        icon: '12.gif'
    },

    {
        syntax: ':-O',
        title: 'Kinh ngạc',
        icon: '13.gif'
    },

    {
        syntax: '~X(',
        title: 'at wits\' end',
        icon: '14.gif'
    },

    {
        syntax: ':&gt;',
        title: 'smug',
        icon: '15.gif'
    },

    {
        syntax: 'B-)',
        title: 'Very kool',
        icon: '16.gif'
    },

    {
        syntax: ':-SS',
        title: 'nail biting',
        icon: '17.gif'
    },

    {
        syntax: '#:-S',
        title: 'whew!',
        icon: '18.gif'
    },

    {
        syntax: ':))',
        title: 'Ha ha',
        icon: '19.gif'
    },

    {
        syntax: ':(',
        title: 'Buồn thế',
        icon: '20.gif'
    },

    {
        syntax: '/:)',
        title: 'raised eyebrows',
        icon:'21.gif'
    },

    {
        syntax: '(:|',
        title: 'yawn',
        icon: '22.gif'
    },

    {
        syntax: ':)]',
        title: 'on the phone',
        icon: '23.gif'
    },

    {
        syntax: '=))',
        title: 'rolling on the floor',
        icon: '24.gif'
    },

    {
        syntax: 'O:-)',
        title: 'angel',
        icon: '25.gif'
    },

    {
        syntax: ':-B',
        title: 'nerd',
        icon: '26.gif'
    },

    {
        syntax: '=;',
        title: 'talk to the hand',
        icon: '27.gif'
    },

    {
        syntax: '8-|',
        title: 'rolling eyes',
        icon: '29.gif'
    },

    {
        syntax: 'L-)',
        title: 'Mất rồi',
        icon: '30.gif'
    },

    {
        syntax: ':-&amp;',
        title: 'Bị ốm',
        icon: '31.gif'
    },

    {
        syntax: ':-$',
        title: 'don\'t tell anyone',
        icon: '32.gif'
    },

    {
        syntax: '[-(',
        title: 'no talking',
        icon: '33.gif'
    },

    {
        syntax: ':O)',
        title: 'clown',
        icon: '34.gif'
    },

    {
        syntax: '8-}',
        title: 'Ngốc ngếch',
        icon: '35.gif'
    },

    {
        syntax: '&lt;:-P',
        title: 'party',
        icon: '36.gif'
    },

    {
        syntax: ':|',
        title: 'straight face',
        icon: '37.gif'
    },

    {
        syntax: '=P~',
        title: 'drooling',
        icon: '38.gif'
    },

    {
        syntax: ':-?',
        title: 'Suy tư',
        icon: '39.gif'
    },

    {
        syntax: '#-o',
        title: 'd\'oh',
        icon: '40.gif'
    },

    {
        syntax: '=D&gt;',
        title: 'applause',
        icon: '41.gif'
    },

    {
        syntax: ':-S',
        title: 'Lo lắng',
        icon: '42.gif'
    },

    {
        syntax: '@-)',
        title: 'hypnotized',
        icon: '43.gif'
    },

    {
        syntax: ':^o',
        title: 'liar',
        icon: '44.gif'
    },

    {
        syntax: ':-w',
        title: 'waiting',
        icon: '45.gif'
    },

    {
        syntax: ':-&lt;',
        title: 'sigh',
        icon: '46.gif'
    },

    {
        syntax: ':P',
        title: 'tongue',
        icon: '47.gif'
    },

    {
        syntax: ';)',
        title: 'winking',
        icon: '48.gif'
    },

    {
        syntax: ':)',
        title: 'Hạnh phúc',
        icon: '100.gif'
    },

    {
        syntax: ':-c',
        title: 'call me',
        icon: '101.gif'
    },

    {
        syntax: 'X(',
        title: 'Tức giận',
        icon: '102.gif'
    },

    {
        syntax: ':-h',
        title: 'wave',
        icon: '103.gif'
    },

    {
        syntax: '8-&gt;',
        title: 'day dreaming',
        icon: '105.gif'
    }
    ];

    var tb_emos = '';
    for(var i = 0 ;i < emotions.length; i++){
        tb_emos +='<img src="'+baseURL+'/style/css/chat/emo/'+ emotions[i].icon +'"/>';
    }

    function initSound(){
        $('body')
        .append("<audio id='chat-message' src='"+baseURL+"/style/css/chat/sounds/message.wav'></audio>")
        .append("<audio id='chat-buzz' src='"+baseURL+"/style/css/chat/sounds/buzz.wav'></audio>");
    }
    $(initSound);

    function soundMessage(){
        $('#chat-message').each(function(){
            this.play();
        });
    }

    function soundBuzz(){
        $('#chat-buzz').each(function(){
            this.play();
        });
    }

    function makeContent(data){
        var text = data;
        if( text == 'BUZZ!!!' ){
            return "<span style='font-weight:bold;color:red;font-size:120%'>BUZZ!!!</span>";
        }
        for( var i =0; i< emotions.length; i++ ){
            text = text.replace(new RegExp($.safe(emotions[i].syntax),"gi")
                ,"<img valign='middle' src='"+baseURL+"/style/css/chat/emo/"+emotions[i].icon+"' title='"+emotions[i].title+"'/>");
        }

        text = text.replace(/\n/gi,"<br/>");
        text = text.replace(/(http:\/\/[^\s\t]*)/gi, function(m){
            return "<a href='"+ m +"'>"+m+"</a>";
        });

        return text;
    }

    function getFrame(){
        //if( frame ) return frame;
        var div = document.createElement('div');
        $(div)
        .htm("<div id='chat-load' style='padding:10px'>Đang tải...</div><div style='visibility:hidden' id='chat-frame'><div class='chat-head'>"
            +"<div id='chat-me' style='border-bottom:1px solid #B1BCD5;min-height:56px;background:#fff'></div>"
            +"<input type='text' class='s' placeholder='Tìm người dùng để trò chuyện'/>"
            +"</div>"
            +"<div id='chat-body'>Loading...</div><div class='chat-foot' style='display:none'><span class='offline'></span><span class='online'></span></div></div>");
        $(div)
        .find(".close")
        .onClick(function(){
            $(div).remove();
        })

        $(div)
        .find("input.s")
        .onKeyup(function(){
            var t = this.value;
            $(div)
            .find("#chat-body .r")
            .each(function(){
                if( $(this).find(".u").text().indexOf(t) == -1 ){
                    $(this).css({
                        display:'none'
                    })
                }else{
                    $(this).css({
                        display:'block'
                    })
                }
            })
        })
        return div;
    }


    var AppChat = {
        Url:{
            Default: baseURL+'/Chat/Index/Data'
        },
        Data:{
            ToolBarId: 'ChatBar'
        },
        getAvatar: function( file ){
            return file ? '<img class="pt" src="'+baseURL+'/files/photo/'+file+'"/>'
            : '<img class="pt" src="'+baseURL+'/files/static/noavatar.gif"/>'
        },
        getStatus: function( user ){
            return '<span class="offline"></span>'
        },
        Render: function( data ){
            var me  	= data.me,
            img 	= AppChat.getAvatar(me.photo),
            users	= data.users;

            $("#chat-header")
            .append(img)
            .append('<a class="u">'+me.username+'</a>')
            .append('<a class="t">'+me.status+'</a>');


            //update list
            for(var i=0; i < users.length; i++ ){
                var mem = users[i];
                $('<div class="l">'
                    + AppChat.getAvatar(mem.photo)
                    + '<a class="u"><span class="offline"></span>' + mem.username + '</a><br/>'
                    + '<span class="s">' + (mem.status !== null ? mem.status : '') + "</span>"
                    +'</div>')
                .appendTo("#chat-body")
                .set('dataChat',mem)
                .onClick(function(){
                    AppChat.OpenBox(this)
                });
            }

            $("#toolbar-body-control .ml")
            .onClick(function(e){
                e.preventDefault();
                e.stopPropagation();
                $("#toolbar-body-main").stop();

                var size = $("#toolbar-body-main .chat-box").width() + parseInt($("#toolbar-body-main .chat-box").css("marginLeft"))||0;
                if( !size  ) return;

                var width 		= $("#toolbar-body-main").width();
                var pad 		= $("#toolbar-body-control").width()+2;
                var marginLeft 	= parseInt($("#toolbar-body-main").css('marginLeft'))|| 0;
                var maxWidth 	= $("#toolbar-body").width();

                if( width + marginLeft < maxWidth ){
                    return;
                }

                $("#toolbar-body-main")
                .stop()
                .animate({
                    marginLeft: '-=' + Math.min(size, width + marginLeft - maxWidth + pad)
                },{
                    easing: 'swing',
                    duration: 200,
                    callback: function(){
                        AppChat.CheckControl();
                    }
                });
            });

            $("#toolbar-body-control .mr")
            .onClick(function(e){
                e.preventDefault();
                e.stopPropagation();
                $("#toolbar-body-main").stop();

                var size = $("#toolbar-body-main .chat-box").width() + parseInt($("#toolbar-body-main .chat-box").css("marginLeft"))||0;
                if( !size  ) return;

                var width 		= $("#toolbar-body-main").width();
                var pad 		= $("#toolbar-body-control").width()+2;
                var marginLeft 	= parseInt($("#toolbar-body-main").css('marginLeft'))|| 0;
                var maxWidth 	= $("#toolbar-body").width();

                if( marginLeft > 0 ){
                    return;
                }

                $("#toolbar-body-main")
                .stop()
                .animate({
                    marginLeft: '+=' + Math.min(size, Math.abs(marginLeft) )
                },{
                    easing: 'swing',
                    duration: 200,
                    callback:function(){
                        AppChat.CheckControl();
                    }
                });
            });
        },
        LoadList: function(){
            window.update_chat = $.Ajax(AppChat.Url.Default,{
                cache:false,
                success: function( data ){
                    try{
                        eval("var data="+data);
                    }catch(e){}
                    AppChat.Render(data)
                }
            });
        },
        ShowList: function(){
            if( $('#chat-list').size() == 0 ){
                var div = $('<div id="chat-list">'
                    +'<div id="chat-header"></div>'
                    +'<div id="chat-body">'
                    +'</div>'
                    +'<div id="chat-search">'
                    +'<input type="text" placeholder="tìm kiếm người chat"/>'
                    +'</div>'
                    +'</div>')
                .k(0);

                $('#chat-option').append(div);
            }
        },
        OpenBox: function( obj ){
            var user = obj.dataChat;
            $('<div class="chat-box">'
                +'<div class="b">'+ AppChat.getStatus() +'&nbsp;&nbsp;'+ user.username+'</div>'
                +'</div>')
            .appendTo("#toolbar-body-main")
            .each(function(){
                AppChat.CheckScreen(false);
                AppChat.FocusTo(this);
            });
            AppChat.OpenWindow();
        },
        OpenWindow: function(user){
            $.createLightBox({
                title: "Test",
                resize:{
                    selector:'.lightbox-content',
                    minWidth:140,
                    minHeight:120
                },
                css:{
                    'border-width':'5px'
                },
                css_content:{
                    padding:'0px'
                },
                callback: function(){
                    $(this)
                    .htm("abc abc")
                }
            });
        },
        CheckScreen: function( reset ){
            var width = $("body").width() - $("#toolbar-bts").width();
            $("#toolbar-body")
            .css({
                width: width+'px',
            })

            if(reset)
                $("#toolbar-body-main")
                .css({
                    marginLeft: '0px'
                });

            $("#toolbar-body-control").css({
                display: ( width < $("#toolbar-body-main").width() ) ?'inline-block':'none'
            })
        },
        FocusTo: function(elem){
            var marginLeft 	= parseInt($("#toolbar-body-main").css('marginLeft'))|| 0;
            var maxWidth 	= $("body").width() - $("#toolbar-bts").width();//$("#toolbar-body").width();
            var pad 		= $("#toolbar-body-control").width()+2;
            var size = elem.offsetLeft - maxWidth + elem.offsetWidth + pad - marginLeft;

            if( size == Math.abs(marginLeft) ){
                return;
            }

            $("#toolbar-body-main")
            .stop()
            .animate({
                marginLeft: '-'+ Math.max(0,size) + 'px'
            },{
                easing: 'swing',
                duration: 200,
                callback: function(){
                    AppChat.CheckControl();
                }
            });
        },
        CheckControl: function(){
            var marginLeft 	= parseInt($("#toolbar-body-main").css('marginLeft'))|| 0,
            pad 		= $("#toolbar-body-control").width()+2,
            maxWidth 	= $("body").width() - $("#toolbar-bts").width(),
            width 		= $("#toolbar-body-main").width();

            if( marginLeft < 0 ){
                $("#toolbar-body-control .mr span").addClass("mr-a");
            }else{
                $("#toolbar-body-control .mr span").removeClass("mr-a");
            }

            if( maxWidth - pad < width + marginLeft ){
                $("#toolbar-body-control .ml span").addClass("ml-a");
            }else{
                $("#toolbar-body-control .ml span").removeClass("ml-a");
            }
        }
    };

    $(function(){
        //AppChat.ShowList();
        //AppChat.LoadList();
        $('#chat-option .m')
        .onClick(function(){
            //	$('#chat-list').toggle();
            });

        $(window).onResize(function(){
            //	AppChat.CheckScreen(true);
            //	AppChat.CheckControl();
            });
    });


    //getFrame();

    function setPosition(){
        $('#chat-frame')
        .css({
            'box-shadow':'0px 0px 2px #637AAE',
            top: '200px',
            left: '500px'
        });
    }

    function addMessage(user,mes,e){
        $.Ajax(baseURL+'/Chat/Index/Add',{
            type: 'POST',
            data:{
                to_id: user.ID,
                message: mes || e.value
            },
            error: function(){
            //e.disabled = false;
            },
            create:function(){
                e.value = '';
            //if( e ) e.disabled = true;
            },
            success: function( data ){
                if(e){

                //e.disabled = false;
                }
            }
        });
    }

    function openBox(user){
        if( !document.getElementById('cb-'+user.ID) ){
            var icon = user.active <= 15 ? 'online' : 'offline';
            $.createLightBox({
                title: "<div><a class='chaticon-"+user.ID+"'><span class='"+icon+"'></span>&nbsp;</a>"+user.username+"</div>",
                resize:{
                    selector:'.ms',
                    minWidth:140,
                    minHeight:120
                },
                css:{
                    'border-width':'5px'
                },
                css_content:{
                    padding:'0px'
                },
                callback: function(){
                    var img = user.photo ?
                    "<img class='imct' src='"+baseURL+"/files/photo/"+ user.photo +"'/>"
                    : ("<img class='imct' src='"+baseURL+"/files/photo/noavatar.gif'/>");

                    $(this)
                    .htm("<div id='cb-"+user.ID+"' class='cb'>"
                        //+"<div class='cbar'><a title='Xem tin nhắn gần đây'>Lịch sử</a></div>"
                        +"<table heigh='100%' width='100%' cellpadding='2'>"
                        +"<tr><td><div class='ms'></div></td>"
                        +"<td align='center' class='ir1'>"+img+"</td></tr>"
                        +"<tr><td><a style='color:red;font-weight:bold;font-size:14px;text-shadow:0px 1px 1px #ccc;display:inline-block' class='buzz'>BUZZ</a><textarea spellcheck='false' class='ct'></textarea></td>"
                        +"<td class='ir2' width='70' align='center'>"
                        + me_img
                        +"</td></tr></table></div>")
                    /*.find(".ct")
						.each(function(){
							new nicEditor({
								iconsPath: baseURL+'/style/plugins/editor/icons.gif',
								buttonList: ['forecolor','bgcolor','bold','italic','underline','strikeThrough'],
							}).panelInstance(this);
						});*/
                    .each(function(){
                        $(this)
                        .find(".buzz")
                        .onClick(function(){
                            if( this.time && ( (new Date()).getTime() - this.time < 15*1000 )){
                                return
                            }
                            addMessage(user,'BUZZ!!!');
                            this.time = (new Date()).getTime();
                        })
                    })
                    .find("textarea.ct")
                    .onFocus(function(){
                        this.focused = true;
                    })
                    .onBlur(function(){
                        this.focused = false;
                    })
                    .onKeydown(function( event ){
                        var e = this;
                        if( event.originalEvent.ctrlKey == 1 ){
                            if( event.which == 13 ){
                                var p = $(e).getPointer();
                                e.value = e.value.substr(0,p.start) + "\n" + e.value.substr(p.start);
                                $(e).selectText(p.start+1,p.start+1);
                                event.preventDefault();
                                event.stopPropagation();
                                return ;
                            }
                        }
                        if( event.which == 13 ){
                            addMessage(user,null,e);
                            event.preventDefault();
                            event.stopPropagation();
                        }
                    })
                }
            });
        }
    }

    function loadList(){
        window.update_chat = $.Ajax(baseURL+'/Chat/Index/Data',{
            cache:false,
            success: function( data ){
                try{
                    eval("var data="+data);
                }catch(e){}

                me_name = data.me.username;
                me_img = data.me.photo ? "<img class='imct' src='"+baseURL+"/files/photo/"+ data.me.photo +"'/>" : ("<img class='imct' src='"+baseURL+"/files/photo/noavatar.gif'/>");

                var mypic = data.me.photo ?
                "<img class='userimg' onclick='load_inframe(baseURL+\"/User/Photo\",{title:\"Thay đổi avatar\",auto_hidden:true})' style='margin:5px;width:50px;height:50px;padding:1px;border:1px solid #ddd' src='"+baseURL+"/files/photo/"+ data.me.photo +"'/>"
                : ("<img class='userimg' onclick='load_inframe(baseURL+\"/User/Photo\",{title:\"Thay đổi avatar\",auto_hidden:true})' style='margin:5px;width:50px;height:50px;padding:1px;border:1px solid #ddd' src='"+baseURL+"/files/photo/noavatar.gif'/>");

                $('#chat-me')
                .htm("<table width='100%' cellpadding='0'>"
                    +"<tr><td width='60' valign='top'>"
                    +mypic
                    +"</td><td align='left' valign='top'>"
                    +"<a><span class='online'></span>&nbsp;<b>"+me_name+"</b></a>"
                    +"<div style='padding:0px 10px 2px 0px'><textarea spellcheck='false' rows='1' style='overflow:hidden;height:33px;font-family:Comic Sans MS;width:100%;border:none;resize:none' maxlength='100'>"+ (data.me.status||'')+"</textarea></div>"
                    +"</td></tr></table>"
                    )
                .find('textarea')
                .addEvent('keydown paste click',function(){
                    //this.style.height = '10px';
                    if( this.scrollHeight > this.clientHeight ){
                    //	this.style.height = this.scrollHeight +'px';
                    }
                })
                .onFocus(function(){
                    $(this).css('border:1px dotted #777;overflow:auto');
                })
                .onBlur(function(){
                    var e= this;
                    this.scrollTop = 0;
                    $(this).css('border:none;overflow:hidden');
                    $.Ajax(baseURL+'/Chat/Index/Changestatus',{
                        type: 'POST',
                        data: {
                            title: this.value
                        },
                        success: function(){
                            e.title = e.value;
                            e.value = e.value.replace(/\n/gi,' ');
                        }
                    });
                });


                var a = data.users;
                $('#chat-frame')
                .css({
                    visibility: 'visible'
                })
                $('#chat-load')
                .remove();
                $('#chat-body').htm('');
                for(var i=0;i< a.length;i++){
                    var img = a[i].photo ?
                    "<img class='imc' src='"+baseURL+"/files/photo/"+ a[i].photo +"'/>"
                    : ("<img class='imc' src='"+baseURL+"/files/photo/noavatar.gif'/>");
                    var icon = a[i].active <= 5 ? 'online' : 'offline';
                    $("<div class='r'><table width='100%' cellpadding='2' id='cu-"+a[i].ID+"'><tr><td width='20' align='center'>"
                        +img
                        +"</td><td align='center' width='20'>"
                        +"<a class='chaticon-"+a[i].ID+"'><span class='"+icon+"'></span></a></td>"
                        +"<td><a><b class='u'>"+a[i].username+"</b></a>&nbsp;<i class='stt'>"
                        +(a[i].status !== null ? makeContent(a[i].status) : '')+"</i></td></tr></table></div>")
                    .set('user',a[i])
                    .appendTo('#chat-body')
                    .onClick(function(){
                        if($(this).hasClass('selected')){
                            openBox(this.user);
                        }
                        $("#chat-body .selected")
                        .removeClass("selected");
                        $(this).addClass("selected");
                    })
                }
            }
        });
    }

    function update(){
        var time = (window.timeChat||5)*1000;
        try{
            $.Ajax( baseURL + '/Chat/Index/Data',{
                cache:false,
                success: function( data ){
                    try{
                        eval("var data="+data);
                    }catch(e){
                        return
                    }
                    me_name = data.me.username;
                    me_img = data.me.photo ? "<img class='imct' src='"+baseURL+"/files/photo/"+ data.me.photo +"'/>" : ("<img class='imct' src='"+baseURL+"/files/photo/noavatar.gif'/>");

                    var a = data.users, num_online = 0;


                    $('.chaticon-'+data.me.ID+' span').set('className','online');
                    for(var i=0;i < a.length;i++){
                        if( a[i].active <= 5 )
                            num_online++;
                        var icon = a[i].active <= 5 ? 'online' : 'offline';

                        $('.chaticon-'+a[i].ID+' span').set('className',icon);

                        $('#cu-'+a[i].ID+' i')
                        .htm((a[i].status !== null ? makeContent(a[i].status) : ''));
                    }


                    var a = data.messages, b = data.me;
                    for( var i=0;i < a.length; i++ ){
                        var name = "<b>"+a[i].from_name+"</b>";
                        if( a[i].from_id != b.ID ){
                            name = "<a>"+name+"</a>";
                        }

                        if( $('#cb-'+a[i].to_id+',#cb-'+a[i].from_id).size() == 0 ){
                            openBox({
                                ID	: a[i].from_id,
                                username : a[i].from_name,
                                photo: a[i].from_photo,
                                active: a[i].from_active
                            });
                        }

                        $('#cb-'+a[i].to_id+',#cb-'+a[i].from_id)
                        .find(".ms")
                        .append(function(){
                            if(a[i].from_name != me_name ){
                                document.title = a[i].from_name +" nói ...";
                            }
                            if( this.lastID ==  a[i].from_id ){
                                this.lastID =  a[i].from_id;
                                return "<div class='cm'>" + makeContent(a[i].message)+"</div>";
                            }
                            this.lastID =  a[i].from_id;

                            return "<div class='cm'>" + name + ": " + makeContent(a[i].message)+"</div>";
                        })
                        .each(function(){
                            this.scrollTop = this.scrollHeight;
                        });

                        //make sound
                        $('#cb-'+a[i].to_id+',#cb-'+a[i].from_id)
                        .find("textarea.ct")
                        .each(function(){
                            if( a[i].message == 'BUZZ!!!' ){
                                soundBuzz();
                            }
                            if( !this.focused ){
                                soundMessage();
                            }
                        })

                    }
                    delayTime = 1;
                    setTimeout(update,time);
                },
                error: function(){
                    setTimeout(update,delayTime*time);
                }
            });
        }catch(e){

        }
    }

    //update();

    var Chat = {};

    $('#chat')
    .onClick(function(){
        if( !document.getElementById('chat-frame') ){
            $.createLightBox({
                id: 'chat-frame',
                title: 'Trò chuyện trực tuyến',
                css:{
                    'min-width': '250px',
                    padding:'0px'
                },
                mask: false,
                css_content:{
                    padding:'0px'
                },
                resize: {
                    selector: '#chat-body',
                    minHeight: 230,
                    minWidth: 230
                },
                callback: function( ){
                    $(this)
                    .htm(getFrame());
                }
            });
        }
        setPosition();
        loadList();
    });

    window.Chat = Chat
})(Owl)