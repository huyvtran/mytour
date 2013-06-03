/*
	A event handler on document is better than every element

*/
(function($){

    function valid( elem, js, evt ){
        if( js.length == 0 )
            return true;
        var a = js.shift(),data={};
        data[a.name] = elem.value;

        switch( a.type ){
            case 'ajax':
                $.Ajax( a.url,{
                    data:data,
                    success: function( data ){
                        if( data == '0' ){
                            $(elem).addClass('valid-error');
                        }else{
                            $(elem).removeClass('valid-error');
                        }
                    }
                });
                break;

            //AUTO SINGLE VALUE
            case 'autosingle':
                if( evt.KEY_DOWN || evt.KEY_UP ){
                    return true;
                }
                if(elem.value == ''){
                    return false;
                }

                $(elem.parentNode)
                .css({
                    zIndex:5
                });

                if( !elem.autodiv ){
                    elem.autodiv = $("<div></div>")
                    .css({
                        minWidth: ( elem.parentNode.offsetWidth - 2) +'px',
                        border: '1px solid #ccc',
                        background: '#fff',
                        position: 'absolute',
                        zIndex:11,
                        top: (elem.parentNode.offsetHeight-1)+'px',
                        display:'none',
                        borderTop: 'none',
                        left:'-1px'
                    })
                    .afterTo(elem)
                    .k(0);
                    try{
                        eval("elem.callback ="+ elem.getAttribute('callback') );
                    }catch(e){}
                    elem.callback = elem.callback||function(){};

                    $(elem)
                    .onKeydown(function( event ){

                        if( !event.KEY_DOWN && !event.KEY_UP && !event.KEY_ENTER){
                            return true;
                        }

                        var b = this.autodiv.getElementsByTagName('div');
                        if( b.length = 0 ) return false;

                        var num = isNaN( parseInt(this.current) ) ? 0 : parseInt(this.current);

                        if( event.KEY_ENTER ){
                            event.preventDefault();
                            event.stopPropagation();

                            if( $(this.parentNode).find('.x-auto-current').size()==0 ){
                                $(this.parentNode)
                                .first('<div class="x-auto-current"></div>');
                            }

                            $(this.parentNode)
                            .find('.x-auto-current')
                            .htm( b[num].value )
                            .each(function(){
                                elem.callback(b[num].json,b[num],this)
                            });

                            $(this.autodiv)
                            .css({
                                display:'none'
                            });
                            this.value ='';
                            this.blur();
                            return false;
                        }

                        num = ( b.length + (num+( event.KEY_DOWN ? 1 : -1 ))) % b.length;
                        this.current = num;

                        $(b)
                        .removeClass('x-auto-selected');
                        $( b[num])
                        .addClass('x-auto-selected');

                    },'next')
                    .onBlur(function(){
                        $(elem.parentNode)
                        .css({
                            zIndex:1
                        });
                        $(this.autodiv)
                        .css({
                            display:'none'
                        })
                    });
                    $(elem.parentNode)
                    .onClick(function(){
                        elem.focus();
                    })
                }

                $.Ajax( a.url,{
                    cache: false,
                    data:{
                        s : elem.value
                    },
                    success: function( dt ){
                        var items =[];
                        try{
                            eval("items = "+ dt );
                        }catch(e){

                        }
                        if( items.length == 0 )
                            $(elem.autodiv).css({
                                display: 'none'
                            })
                            .htm('');
                        else{

                            for(var i=0; i < items.length; i++ ){
                                items[i]['option'] = "<a href='"+a.data.link+items[i].ID+"' target='_blank'>"+items[i][a.data.title]+"</a>";
                                items[i]['value']  = "<input type='hidden' name='"+a.data.name+"' value='"+items[i].ID+"'/>"
                                +"<a target='_blank' href='"+a.data.link+items[i].ID+"'>"+items[i][a.data.title]+"</a>"
                                +"<a class='x' onclick='Owl(this.parentNode).remove();return false'></a>";
                            }

                            $(elem.autodiv)
                            .css({
                                top: (elem.parentNode.offsetHeight-1)+'px',
                                display: 'inline-block'
                            })
                            .htm('');
                            for( var i=0; i < items.length; i++ ){
                                if( $(elem.parentNode).find("input[value="+items[i].ID+"]").size() > 0 ){
                                    continue;
                                }
                                $("<div class='x-auto-line' style='word-space:nowrap'>"+items[i].option+"</div>")
                                .appendTo(elem.autodiv)
                                .onMousedown(function(){
                                    if( $(elem.parentNode).find('.x-auto-current').size()==0 ){
                                        $(elem.parentNode)
                                        .first('<div class="x-auto-current"></div>');
                                    }
                                    var b = this;
                                    $(elem.parentNode)
                                    .find('.x-auto-current')
                                    .htm( this.value )
                                    .each(function(){
                                        elem.callback(b.json,b,this)
                                    });
                                    $(elem.autodiv)
                                    .css({
                                        display:'none'
                                    });
                                    elem.value ='';
                                    elem.blur();
                                    return false;
                                })
                                .onMouseover(function(){
                                    $(this.parentNode)
                                    .find('.x-auto-line')
                                    .removeClass('x-auto-selected');
                                    $( this ).addClass('x-auto-selected');
                                })
                                .onMouseout(function(){
                                    $( this ).removeClass('x-auto-selected');
                                })
                                .set('value',items[i].value)
                                .set('json',items[i]);

                            }
                        }
                    }
                });
                break;

            //AUTO MULTIPLY VALUE
            case 'automutiply':
                if( evt.KEY_DOWN || evt.KEY_UP ){
                    return true;
                }
                if(elem.value == ''){
                    return false;
                }

                if( !elem.autodiv ){
                    elem.autodiv = $("<div></div>")
                    .css({
                        minWidth: ( elem.parentNode.offsetWidth - 2) +'px',
                        border: '1px solid #ccc',
                        background: '#fff',
                        position: 'absolute',
                        zIndex:'200',
                        top: (elem.parentNode.offsetHeight-1)+'px',
                        display:'none',
                        borderTop: 'none',
                        left:'-1px'
                    })
                    .afterTo(elem)
                    .k(0);

                    try{
                        eval("elem.callback ="+ elem.getAttribute('callback') );
                    }catch(e){}
                    elem.callback = elem.callback||function(){};

                    $(elem)
                    .onKeydown(function( event ){

                        if( !event.KEY_DOWN && !event.KEY_UP && !event.KEY_ENTER){
                            return true;
                        }

                        var b = this.autodiv.getElementsByTagName('div');
                        if( b.length == 0 ) return false;

                        var num = isNaN( parseInt(this.current) ) ? 0 : parseInt(this.current);

                        if( event.KEY_ENTER ){
                            event.preventDefault();
                            event.stopPropagation();
                            if(this.value =='' ) return false;

                            $(this.parentNode)
                            .first('<div class="x-auto-current"></div>')
                            .find('.x-auto-current')
                            .slice(0,1)
                            .htm( b[num].value )
                            .each(function(){
                                elem.callback(b[num].json,b[num],this)
                            });
                            $(this.autodiv)
                            .css({
                                display:'none'
                            });
                            this.value ='';
                            this.blur();
                            this.value ='';
                            return false;
                        }

                        num = ( b.length + (num+( event.KEY_DOWN ? 1 : -1 ))) % b.length;
                        this.current = num;

                        $(b)
                        .removeClass('x-auto-selected');
                        $( b[num])
                        .addClass('x-auto-selected');

                    },'next')
                    .onBlur(function(){
                        $(this.autodiv)
                        .css({
                            display:'none'
                        })
                    });
                    $(elem.parentNode)
                    .onClick(function(){
                        elem.focus();
                    })
                }


                $.Ajax( a.url,{
                    cache: false,
                    data:{
                        s : elem.value
                    },
                    success: function( dt ){
                        var items =[];
                        try{
                            eval("items = "+ dt );
                        }catch(e){

                        }
                        if( items.length == 0 )
                            $(elem.autodiv).css({
                                display: 'none'
                            })
                            .htm('');
                        else{

                            for(var i=0; i < items.length; i++ ){

                                items[i]['option'] = "<a href='"+a.data.link+items[i].ID+"' target='_blank'>"+items[i][a.data.title]+"</a>";
                                items[i]['value']  = "<input type='hidden' name='"+a.data.name+"' value='"+items[i].ID+"'/>"
                                +"<a target='_blank' href='"+a.data.link+items[i].ID+"'>"+items[i][a.data.title]+"</a>"
                                +"<a class='x' onclick='Owl(this.parentNode).remove();return false'></a>";
                            }

                            $(elem.autodiv)
                            .css({
                                top: (elem.parentNode.offsetHeight-1)+'px',
                                display: 'inline-block'
                            })
                            .htm('');
                            for( var i=0; i < items.length; i++ ){
                                if( $(elem.parentNode).find("input[value="+items[i].ID+"]").size() > 0 ){
                                    continue;
                                }

                                $("<div class='x-auto-line' style='word-space:nowrap'>"+items[i].option+"</div>")
                                .appendTo(elem.autodiv)
                                .onMousedown(function(){
                                    var b = this;
                                    $(elem.parentNode)
                                    .first('<div class="x-auto-current"></div>')
                                    .find('.x-auto-current')
                                    .slice(0,1)
                                    .htm( this.value )
                                    .each(function(){
                                        elem.callback(b.json,b,this)
                                    });

                                    $(elem.autodiv)
                                    .css({
                                        display:'none'
                                    });
                                    elem.value ='';
                                    elem.blur();
                                    elem.value =='';
                                    return false;
                                })
                                .onMouseover(function(){
                                    $(this.parentNode).find('.x-auto-line').removeClass('x-auto-selected');
                                    $( this ).addClass('x-auto-selected');
                                })
                                .onMouseout(function(){
                                    $( this ).removeClass('x-auto-selected');
                                })
                                .set('value',items[i].value)
                                .set('json',items[i]);
                            }
                        }
                    }
                });
                break;

            default:
                valid(elem,js,evt);
        }
    }

    $(document)
    .addEvent("keyup",function( e ){
        var elem = e.target;
        if( $.test('input[valid]',elem) ){
            var dt = elem.getAttribute('valid');

            try{
                eval("var js="+dt);
            }catch(e){
                return false;
            }

            valid(elem,js,e);
        }
    });
})(Owl);