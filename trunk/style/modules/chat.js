(function($){
    var delayTime = 1;
    var me_name = null,me_img =null,me_id;

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

    function getPhoto(photo){
        var file = photo || 'noavatar.gif';
        file = "files/photo/" + file;
        return "<img class='imct' src='"+baseURL+"'/>";
    }

    function setPosition(){
        $('#chat-frame')
        .css({
            'box-shadow':'0px 0px 2px #637AAE',
            top: '200px',
            left: '500px'
        });
    }

    function addMessage(user,mes,e){
        var toID = user.ID;
        mes = mes || e.value;

        //append chat to ignore ajax send
        $('#cb-' + toID )
        .find(".ms")
        .append(function(){
            this.lastID = me_id;
            return "<div class='cm'><b>" + me_name + "</b>: " + makeContent(mes)+"</div>";
        })
        .each(function(){
            this.scrollTop = this.scrollHeight;
        });

        (function(n){
            var f = arguments.callee;
            $.Ajax(baseURL+'/Chat/Index/Data',{
                type: 'POST',
                data:{
                    to_id: toID,
                    message: mes
                },
                error: function(){
                    //resend max 3 times
                    if( n < 3 )
                        setTimeout(1000,function(){
                            f(n++)
                        });
                },
                create:function(){
                    if(e) e.value = '';
                },
                success: function( data ){
                    render_data(data);
                }
            });
        })(0);
    }

    function openBox(user){
        if( !document.getElementById('cb-'+user.ID) ){
            var icon = user.active <= 15 ? 'online' : 'offline';
            $.createLightBox({
                title: "<div><a class='chaticon-"+user.ID+"'><span class='"+icon+"'></span>&nbsp;</a>"+user.username+"</div>",
                resize:{
                    selector:'.ms',
                    minWidth:180,
                    minHeight:120
                },
                css:{
                    'border-width':'5px'
                },
                css_content:{
                    padding:'0px'
                },
                callback: function(){
                    var img = getPhoto(user.photo);

                    $(this)
                    .htm("<div id='cb-"+user.ID+"' class='cb'>"
                        //+"<div class='cbar'><a title='Xem tin nhắn gần đây'>Lịch sử</a></div>"
                        +"<table heigh='100%' width='100%' cellpadding='2'>"
                        +"<tr><td><div class='ms'></div></td>"
                        +"</tr>"
                        +"<tr><td><a style='color:red;font-weight:bold;font-size:14px;text-shadow:0px 1px 1px #ccc;display:inline-block' class='buzz'>BUZZ</a><textarea spellcheck='false' class='ct'></textarea></td>"
                        +"</tr></table></div>")
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

    var loadlistHttp = null;
    function loadList(){
        if(loadlistHttp)
            loadlistHttp.stop();

        loadlistHttp = $.Ajax(baseURL+'/Chat/Index/Data',{
            cache:false,
            //create: ajax_load,
            create : ajax_show,
            complete : ajax_hide,
            success: function( data ){
                try{
                    eval("var data="+data);
                }catch(e){
                    $.Alert("Không thể tải dữ liệu");
                }

                $.createLightBox({
                    id: 'chat-frame',
                    title: 'Chat trực tuyến',
                    css:{
                        padding:'0px'
                    },
                    css_content:{
                        padding:'0px'
                    },
                    resize: {
                        selector: '#chat-body',
                        minHeight: 50,
                        minWidth: 200
                    },
                    callback: function( ){
                        $(this).htm(getFrame());
                    }
                });

                me_name = data.me.username;
                me_img  = data.me.photo ? "<img class='imct' src='"+baseURL+"/files/photo/"+ data.me.photo +"'/>" : ("<img class='imct' src='"+baseURL+"/files/photo/noavatar.gif'/>");
                me_id   = data.me.ID;

                var mypic = data.me.photo ?
                "<img class='userimg' onclick='load_inframe(baseURL+\"/User/Photo\",{title:\"Thay đổi avatar\",auto_hidden:true})' src='"+baseURL+"/files/photo/"+ data.me.photo +"'/>"
                : ("<img class='userimg' onclick='load_inframe(baseURL+\"/User/Photo\",{title:\"Thay đổi avatar\",auto_hidden:true})' src='"+baseURL+"/files/photo/noavatar.gif'/>");

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
                    .onDblclick(function(){
                        openBox(this.user);
                    })
                    .onClick(function(){
                        $("#chat-body .selected")
                        .removeClass("selected");
                        $(this).addClass("selected");
                    })
                }
            }
        });
    }

    function render_data( data ){
        try{
            eval("var data="+data);
        }catch(e){
            return;
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
    }


    function update(){
        var time = (window.timeChat||5)*1000;
        try{
            $.Ajax( baseURL + '/Chat/Index/Data',{
                cache:false,
                success: function( data ){
                    render_data( data );
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

    update();

    var Chat = {};

    $('#chat-option')
    .onClick(function(){
        if( !document.getElementById('chat-frame') ){
            loadList();
        }
    });

    window.Chat = Chat
})(Owl)