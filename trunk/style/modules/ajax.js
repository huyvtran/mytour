/*
All upload from soft ware will use flash or quick-iframe
Then most of pages are used ajax

FLASH UPLOAD:
As issue of session in Flash, I use hashkey to pass this error.
I have read about HTML5 for ajax upload but it is not good at least for a button browser


FORM POST
I use ajax with json to run a form.
The start page return :{
content: your_text
}
The error set in .form-message:{
message: your_text
}

The error show alert:{
alert: your_text
}
The direct link ( when form success ):{
redirect: your_url
}

Reload page:{
reload: 'yes'
}


ERROR LOGIN
if _json set into request then return:{
error_login: true
}

if _ajax set into request then return a quick login

CONCLUSION:
Don't forget set _json or _ajax for your plugins :">

 */



//(function($){
//
//    window.DRAG_DATA = null;
//    $(function(){
//        if( $("#_resize_style_").size() == 0 ){
//            $("head").append("<style id='_resize_style_'>"
//                +"body.tbresize,body.tbresize *{ cursor: w-resize }"
//                +"table.tbresize{ table-layout: fixed}"
//                +"table.tbresize > tbody > tr > td,table.tbresize > tbody > tr > th,"
//                +"table.tbresize > thead > tr > td,table.tbresize > thead > tr > th"
//                +"{ overflow:hidden;white-space: nowrap!important; text-overflow: ellipsis!important }"
//                +"table.tbresize > thead > tr:not(:first-child) > td,table.tbresize > thead > tr:not(:first-child) > th,"
//                +"table.tbresize > thbody > tr:not(:first-child) > td,table.tbresize > thbody > tr:not(:first-child) > th"
//                +"{ width:auto !important }"
//                +"</style>");
//        }
//    });
//    $(document)
//    .onMousedown(function( event ){
//        if( DRAG_DATA ){
//            DRAG_DATA.e = event;
//            event.preventDefault();
//            event.stopPropagation();
//            if( DRAG_DATA.start )
//                DRAG_DATA.start(event)
//        }
//    },"__plugin_drag_down__")
//    .onMousemove(function( event ){
//        if( DRAG_DATA && DRAG_DATA.drag ){
//            DRAG_DATA.drag( event );
//            DRAG_DATA.e = event;
//        }
//    },"__plugin_drag_move__")
//    .onMouseup(function( event ){
//        if( DRAG_DATA && DRAG_DATA.stop ){
//            DRAG_DATA.stop( event );
//
//        }
//        DRAG_DATA = null;
//    },"__plugin_drag_up___");
//
//    function getWidth(elem){
//        return ( $(elem).width()
//            - (parseFloat($(elem).css('padding-left'))||0)
//            - (parseFloat($(elem).css('padding-right'))||0)
//            - (parseFloat($(elem).css('border-left-width'))||0)
//            - (parseFloat($(elem).css('border-right-width'))||0));
//    }
//
//    $.extendDOM("tbresize",function(options){
//        return this.each(function(){
//            if( this.tagName != "TABLE" ) return true;
//            options = $.Extend({
//                minWidth: 5
//            },options||{});
//
//            $(this)
//            .css({
//                'table-layout':'auto'
//            });
//
//            $(this.rows[0].cells)
//            // .filter(":not([notbresize])")
//            .each(function(){
//
//                // check noresize selector
//                if( options.noresize ){
//                    if( !$.isArray(options.noresize)){
//                        options.noresize = [options.noresize];
//                    }
//                    for(var i=0; i < options.noresize.length; i++){
//                        var a = options.noresize[i],test = false;
//                        if( a && typeof a === 'string' ){
//                            test = $.test(options.noresize[i],this)
//                        }
//
//                        if( a && a.call ){
//                            test = a.call(this)
//                        }
//
//                        if( test ){
//                            $(this)
//                            .css({
//                                width: this.offsetWidth +'px'
//                            })
//                            .each(function(){
//                                this
//                                .setAttribute('notbesize','yes')
//                            });
//                            return;
//                        }
//                    }
//                }
//
//                $(this)
//                .css({
//                    width: (parseInt($(this).attr('width'))||this.offsetWidth)+'px'
//                });
//
//                var dragBar = $("<div></div>")
//                .css({
//                    position:'absolute',
//                    right:'-6px',
//                    top:'0px',
//                    height:'100%',
//                    width:'5px',
//                    cursor: 'w-resize'
//                })
//                .onMousedown(function( event ){
//                    DRAG_DATA = {
//                        cell: this.parentNode.parentNode,
//                        e: event,
//                        start: function(){
//                            $('body').addClass('tbresize');
//                        },
//                        stop: function(){
//                            $('body').removeClass('tbresize');
//                        },
//                        drag: function( e ){
//                            var cell = DRAG_DATA.cell;
//                            var nextCell = $(cell).next(0).k(0);
//                            if( !nextCell || nextCell.getAttribute("notbresize") == "yes" ){
//                                return;
//                            }
//                            var ins = e.pageX - DRAG_DATA.e.pageX,
//                            orgLeftWidth = parseInt( $(cell).css('width') )||0,
//                            orgRightWidth = parseInt($(nextCell).css('width'))||0,
//                            leftWidth = orgLeftWidth + ins,
//                            rightWidth = orgRightWidth - ins;
//
//                            if( ( ins > 0 && rightWidth <= options.minWidth)
//                                ||  (ins < 0 && leftWidth <= options.minWidth) ){
//                                return;
//                            }
//
//                            $(cell).css({
//                                width: leftWidth +'px'
//                            });
//                            $(nextCell).css({
//                                width: rightWidth +'px'
//                            });
//                        }
//                    };
//                })
//                .k(0);
//
//                var dragContainer = $("<div></div>")
//                .css({
//                    position:'relative',
//                    height:'100%',
//                    width:'100%'
//                })
//                .htm("<div style='overflow:hidden;width:100%;text-overflow:ellipsis'>"+this.innerHTML+"</div>" )
//                .k(0);
//
//                if( $(this).next(0).attr("notbresize") != "yes" ){
//                    $(dragContainer).append(dragBar);
//                }
//
//                $(this)
//                .htm('')
//                .append(dragContainer);
//            });
//
//            $(this)
//            .addClass("tbresize");
//            $(this)
//            .css({
//                'table-layout':'fixed'
//            });
//        });
//    });
//})(Owl);

function open_login() {
    /*
     * $.createLightBox($.Extend({ title: options.title||'Trình duyệt nhanh: ',
     * callback: function(){ $(this) .htm("<table width='100%'>" +"<tr><td></td>" +"<td></td></tr>" +"<tr><td></td>" +"<td></td></tr>" +"</table>"); }
     * },options||{} ));
     */
    location.href = baseURL + '/Login';
}

function isEmpty(k) {
    k = k || {};
    for (var x in k)
        return false;
    return true;
}

var APP_CALLBACK = {};
(function($) {

    /* ajax loading */
    $("<div id='ajax-load'>Đang tải dữ liệu ... </div>").firstTo("body");

    window.ajax_show = function() {
        move_center();
        $("#ajax-load").css("display:block");
    };

    window.ajax_hide = function() {
        $("#ajax-load").css("display:none");
    };

    window.ajax_error = function() {
        $("#ajax-load").css("display:none");
        $.Alert("Không thể tải dữ liệu");
    };

    function move_center() {
        $(function() {
            $("#ajax-load").css({
                left: $(window).width() / 2 - $("#ajax-load").width() / 2 + "px"
            });
        });
    }

    $(window).addEvent("scroll resize", move_center);

    /* Remove before load */
    window.app_clean = function() {
    // $(".app-auto-hidden").remove();
    // hideMask();
    };

    function updateContent(id, data, fn, type_update) {
        // alert(id+' '+type_update)
        fn = fn || (function() {});
        type_update = type_update === undefined ? 'htm' : type_update;
        if (id == null) {
            id = '#main-content';
        }

        if( typeof data !== 'string' && typeof data !== 'number' )
            if ('frame_title' in data) {
                var title = data.frame_title || "";
                var opt = data.frame_settings || {};
                delete data.frame_title;
                delete data.frame_settings;

                if ('content' in data) {
                    $.createLightBox($.Extend({
                        title: title,
                        callback: function() {
                            fr = this;
                            fr.is_frame = true;
                            updateContent(fr, data);
                            fn.call(fr);
                        }
                    }, opt));
                    return;
                }
            }

        //call form again , use in progress
        var is_repeat = !isNaN(data.progress) && data.progress < 100;

        for (var x in data) {
            switch (x) {
                case 'error_login':
                    $.Alert('Bạn đã bị thoát ra', function() {
                        open_login();
                    });
                    break;

                case 'title':
                    document.title = data[x];
                    break;

                case 'alert':
                    var msg = data[x];
                    delete data[x];
                    $.Alert(msg, function() {
                        updateContent(id, data)
                    });
                    return;
                case 'url':
                    location.href = data[x];
                    break;
                case 'redirect':
                    app_clean(id);
                    if (location.hash == data[x]) {
                        app_load();
                    } else {
                        location.hash = data[x];
                    }
                    break;
                case 'content':
                    app_clean(id);
                    $(data.selector ? data.selector : id)[type_update](data['content']).each(function() {
                        app_callback.call(this)
                        fn.call(this)
                    });
                    delete data.selector;
                    break;
                case 'update':
                    var a = data[x];
                    $(a.selector).htm(a.html).each(function() {
                        app_callback.call(this)
                        fn.call(this)
                    });
                    break;
                case 'reload':
                    app_load();
                    break;
                case 'message':
                    if ($(id).find(".form-message").size() > 0) {
                        $(id).find(".form-message").htm(data[x]);
                        if ($(id).parent(".lightbox").size() == 0) {
                            document.body.scrollTop = Math.max(0, $(id).find(".form-message").parent(0).top() - 50);
                        }
                    } else {
                        $.Alert(data[x]);
                    }
                    break;

                case 'progress':
                    if ($(id).find(".form-message .x-progress").size() == 0) {
                        $progress = $('<div class="x-progress"><div class="x-progress-bar" style="width: ' + data.progress + '%; "></div></div>');
                        $(id).find(".form-message").append($progress.k(0));
                    } else {

                        $(id).find(".x-progress-bar").css({
                            width: data.progress + '%'
                        });
                    }
                    break;
                case 'callback':
                    eval(data[x]);
                    break;

                default:
            }

        // delete data[x];
        //arguments.callee.call(window, id, data);
        }

        return is_repeat;
    }

    window.updateContent = updateContent;

    function app_callback() {
        for (var x in APP_CALLBACK) {
            APP_CALLBACK[x].call(this);
        }
    }

    window.app_callback = app_callback;

    /* Load content of action */
    window.app_load = function() {
        if (window.APP_AJAX && window.APP_AJAX.active) {
            if (window.APP_AJAX.options && window.APP_AJAX.options.type) if (window.APP_AJAX.options.type.toString().toLowerCase() == 'post') {
                $.Confirm("Bỏ qua tác vụ đang thực hiện ?", function() {
                    window.APP_AJAX.stop();
                    app_load();
                });
                return;
            } else {
                window.APP_AJAX.stop();
            }
        }

        app_clean();
        var url = '',
        m;

        if (location.hash == '' || location.hash == '#') {
            url = baseURL + '/Hotel';
            $(".menu .main-link").removeClass('menu-active');
            $(".menu .m-hotel").addClass('menu-active');
        } else {
            url = baseURL + '/' + location.hash.split('#')[1];
            if ((m = location.hash.match(/#([a-z0-9]+)/i))) {

                $(".menu .main-link").removeClass('menu-active');

                $(".menu .m-" + m[1].toLowerCase()).addClass('menu-active');
            }
        }

        window.APP_AJAX = $.Ajax(url, {
            cache: false,
            create: ajax_show,
            complete: ajax_hide,
            error: function() {
                $.Alert("Không thể tải dữ liệu.");
                ajax_hide();
            },
            data: {
                _json: 'yes'
            },
            success: function(data) {
                try {
                    eval("var result = " + data + ";");
                } catch (e) {
                    $("#main-content").htm(data).each(function() {
                        app_callback.call(this)
                    });
                    return false;
                }
                updateContent("#main-content", result);
            }
        });
    };

    window.ajax_load = function(url, callback, selector, type_update, disable_load) {
        var f = callback || (function() {});
        selector = selector === undefined ? "#main-content" : selector;
        type_update = type_update === undefined ? 'htm' : type_update;
        if (window.APP_AJAX && window.APP_AJAX.active) {
            if (window.APP_AJAX.options.type.toString().toLowerCase() == 'post') {
                $.Confirm("Bỏ qua tác vụ đang thực hiện ?", function() {
                    ajax_load(url, callback);
                    window.APP_AJAX.stop();
                });
                return;
            } else {
                window.APP_AJAX.stop();
            }
        }

        $.Ajax(url, {
            cache: false,
            create: disable_load ?
            function() {} : ajax_show,
            complete: disable_load ?
            function() {} : ajax_hide,
            error: function() {
                $.Alert("Không thể tải dữ liệu.");
                ajax_hide();
            },
            data: {
                _json: 'yes'
            },
            success: function(data) {
                try {
                    eval("var result = " + data + ";");
                } catch (e) {
                    $.Alert(data);
                    return false;
                }
                updateContent(selector, result, f, type_update);
            }
        });
    };

    /* Tu dong load tu hash */
    $(app_load);

    /* Tu dong hash change */
    if ('onhashchange' in window) {
        $(window).addEvent("hashchange", app_load);
    } else {
        window.cross_hashchange_link = null;
        setInterval(function() {
            if (window.cross_hashchange_link != location.hash) {
                window.cross_hashchange_link = location.hash;
                app_load();
            }
        }, 5);
    }

    // fixed for hashchange
    $(document).onClick(function(event) {
        if (!event.target.tagName || event.target.tagName != "A") {
            return true;
        }

        var hash = $(event.target).getAttr("href") + "";
        if (hash.match(/^#/i)) {
            if (hash == location.hash) {
                app_load();
            }
        }
    });

    /*
     * load a action normally
     */
    window.ajax_form = function(form, is_main, dt, opt) {
        $(form).find("textarea").each(function() {
            if (this.renderEditor) {
                CKEDITOR.instances[this.renderEditor].updateElement();
            }
        });

        opt = opt || {};

        function disableInput(elem, status) {
            return;
            $(elem).find("select,textarea,input,button").each(function() {
                if (status === true) {
                    if (this.old_disabled === undefined) this.old_disabled = this.disabled;
                    this.disabled = true;
                } else {
                    if (this.old_disabled !== undefined) this.disabled = this.old_disabled;
                }
            });

            //for render
            $(elem).find("span.check").each(function() {
                if (status === true) {
                    if (this.old_disabled === undefined) {
                        this.old_disabled = $(this).hasClass("disabled");
                    }
                    $(this).addClass("disabled");
                } else {
                    if (this.old_disabled === false) $(this).removeClass("disabled");
                }

            });
        }


        if (opt['warning_empty'] && isEmpty(dt)) {
            $.Alert("Chưa có bản ghi nào được chọn");
            return false;
        }

        dt = $.Extend({
            _json: 'yes'
        }, dt || {});

        //        if( window.APP_AJAX && window.APP_AJAX.active ){
        //                $.Confirm("Bỏ qua tác vụ đang thực hiện ?",function(){
        //                    ajax_form(form, is_main, dt, opt, true);
        //                    window.APP_AJAX.stop();
        //                });
        //                return false;
        //            }else{
        //                window.APP_AJAX.stop();
        //            }
        //        }
        disableInput(form, false);

        window.APP_AJAX = $(form).submitAjax({
            data: dt,
            create: function() {
                ajax_show();
                disableInput(form, true);
            },
            complete: ajax_hide,
            error: function() {
                $.Alert("Không th\u1ec3 tải dữ liệu.");
                ajax_hide();
                disableInput(form, false);
            },
            success: function(data) {
                try {
                    eval("var json_data = " + data + ";");
                } catch (e) {
                    $.Alert(data);
                    disableInput(form, false);
                    return false;
                }

                if (json_data && json_data.close) {
                    $(form).parent('.lightbox').each(LightBox.Action.Close);
                    delete json_data.close;
                }

                if (updateContent(is_main || $(form).parent('.lightbox').size() == 0 ? null : form, json_data)) {
                    ajax_show();
                    setTimeout(function() {
                        ajax_form(form, is_main, dt, opt);
                    }, 5000);
                    return false;
                }
                disableInput(form, false);
            }
        });
        return false;
    };

    window.load_frame = window.load_inframe = function(url, options, fn) {
        options = options || {};
        var data = {
            _json: 'yes',
            remove_layout: 'yes'
        };

        if (options['dataForm']) {
            var data1 = $(options['dataForm']).query();
            data = $.Extend(data, data1);
        }

        $.Ajax(url, {
            cache: false,
            data: data,
            create: ajax_show,
            complete: ajax_hide,
            success: function(data) {
                try {
                    eval("var result = " + data + ";");
                } catch (e) {
                    $.Alert(data);
                }

                var fr = null;
                if ('content' in result) {
                    $.createLightBox($.Extend({
                        title: options.title || 'Trình duyệt nhanh',
                        callback: function() {
                            fr = this;
                            fr.is_frame = true;
                            updateContent(fr, result);
                            (fn ||
                                function() {}).call(fr);
                        }
                    }, options || {}));
                } else {
                    updateContent(null, result);
                }
            }
        });
    };

    window.load_html = function(selector, options) {
        options = options || {};
        $.createLightBox($.Extend({
            title: options.title || 'Trình duyệt nhanh',
            callback: function() {
                if ($(selector).size() > 0) {
                    $(this).htm('').append($(selector).k(0).cloneNode(true));
                }
            }
        }, options || {}));

    };

    /* init some script */
    APP_CALLBACK["drop_button"] = function() {

        // menu options popup
        $(this).find(".x-options-container").onMouseenter(function() {
            $(this).find(".x-button").addClass('x-options-hover');
            $(this).find(".x-options").css({
                display: 'block'
            })
        }).onMouseleave(function() {
            $(this).find(".x-button").removeClass('x-options-hover');
            $(this).find(".x-options").css({
                display: 'none'
            })
        }).child(".x-options").child(".x-options-item").onMouseenter(function() {
            $(this).child(".x-options-child").css({
                display: 'block',
                right: (this.parentNode.offsetWidth - 2) + 'px'
            });
        }).onMouseleave(function() {
            $(this).child(".x-options-child").css({
                display: 'none'
            });

        });

    };

    /* user multi check */
    APP_CALLBACK["user_check"] = function() {
        $(this).find(".x-select-users,.x-select-user").each(function() {
            var obj = this,
            single = this.className == 'x-select-user';
            $(obj).css({
                display: 'inline-block'
            });

            var ip = document.createElement('input');
            $(ip).css({
                border: 'none',
                outline: 'none',
                width: '100px'
            });
            $(obj).onClick(function() {
                ip.focus();
            });

            var ipWrap = $("<div style='display:inline-block;position:relative'></div>");
            var suggest = $("<div style='display:none;position:absolute;z-index:100'>" + "<table cellpadding='0' cellspacing='0'><tr>" + "<td style='background:#fff;min-width:150px;border:1px solid #ccc;'></td></tr></table></div>");
            ipWrap.append(ip).append(suggest.k(0)).appendTo(obj);

            suggest.css({
                top: ip.offsetHeight + 'px',
                left: '0px'
            });

            $(ip).onBlur(function() {
                suggest.css({
                    display: 'none'
                });
            }).onKeydown(function(e) {
                if (e.which == $.Event.KEY_ENTER) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (this.value == '') return false;

                    var nodes = suggest.find('td > div').nodes;
                    $(nodes[ip.idx]).trigger('mousedown');
                    suggest.css({
                        display: 'none'
                    });
                    this.value = '';
                }
            }).onKeydown(function(e) {
                if (!(e.which == $.Event.KEY_DOWN || e.which == $.Event.KEY_UP)) return;
                if (ip.idx === undefined) ip.idx = -1;
                var nodes = suggest.find('td > div').nodes;
                if (nodes.length == 0) return;
                ip.idx = (ip.idx + (e.which == $.Event.KEY_DOWN ? 1 : -1)) % nodes.length;
                if (ip.idx <= -1) ip.idx = nodes.length - 1;

                $(nodes).css({
                    background: '#fff'
                });

                $(nodes[ip.idx]).css({
                    background: '#eee'
                });

            }).onKeyup(function(e) {
                if (e.which == $.Event.KEY_DOWN || e.which == $.Event.KEY_UP) return;
                suggest.css({
                    // display:'none'
                    });

                $.Ajax(baseURL + '/User/Select/Suggest?s=' + this.value, {
                    success: function(data) {
                        try {
                            eval("var a = " + data + ";");
                        } catch (e) {
                            return false;
                        }

                        if (!a.content || a.content.length == 0) {
                            return false;
                        }

                        suggest.find('td').htm('');
                        td = suggest.find('td').k(0);

                        var display = "none";
                        for (var i = 0; i < a.content.length; i++) {
                            var u = a.content[i];

                            if ($(obj).find("input[value=" + u.ID + "]").size() > 0) {
                                continue;
                            }

                            display = "inline-block";

                            $('<div style="padding:5px">' + '<a style="display:inline-block">' + u.fullname + '<span style="display:inline-block;padding:0px 10px;font-size:10px;font-style:italic;color:#444">' + u.department_title + '</span>' + '</a>' + '</div>').onMouseenter(function() {
                                $(this).css({
                                    background: '#eee'
                                })
                            }).onMouseleave(function() {
                                $(this).css({
                                    background: '#fff'
                                })
                            }).set('xname', $(obj).getAttr('x-name')).set('xvalue', u.ID).set('xlabel', u.fullname).onMousedown(function() {
                                if (single) {
                                    $(obj).find('span.item').remove()
                                }

                                $("<span class='item'>" + "<input type='hidden' name='" + this.xname + "' value='" + this.xvalue + "'/>" + "<a onclick=\"load_inframe( baseURL+'/User/Info?ID=" + this.xvalue + "',{ title: 'Thông tin cá nhân của <u>" + this.xlabel + "</u>'})\">" + this.xlabel + "</a>" + "&nbsp;&nbsp;<span class='x' onclick='$(this.parentNode).remove()'></span>" + "</span>").beforeTo(ip);
                                ip.value = '';
                                ip.focus();
                            }).appendTo(td);
                        }

                        ip.idx = 0;
                        suggest.css({
                            display: display,
                            left: ip.offsetLeft + 'px',
                            top: (ip.offsetTop + ip.offsetHeight) + 'px'
                        });
                        suggest.find('td > div:first-child').css({
                            background: '#eee'
                        })
                    }
                });
            });

            $('<a class="x" title="Xóa hết"></a>').appendTo(obj).css({
                position: 'absolute',
                top: '5px',
                right: '5px'
            }).onClick(function() {
                $(obj).find('span.item').remove()
            });

            $('<a class="x-select-users-icon" title="Chọn người dùng"></a>').afterTo(obj).onClick(function() {
                $.createLightBox({
                    title: $(obj).getAttr('x-title') || 'Chọn người dùng',
                    css_content: {
                        width: '300px',
                        height: '350px',
                        overflow: 'auto',
                        position: 'relative'
                    },
                    callback: function() {
                        var elem = this,
                        name = $(obj).getAttr('x-name')
                        url = baseURL + '/User/Select/Multicheck?name=' + name;
                        $.Ajax(url, {
                            data: {
                                is_checkbox: single ? 0 : 1
                            },
                            success: function(data) {
                                try {
                                    eval("var result = " + data + ";");
                                } catch (e) {
                                    $.Alert(data);
                                    return false;
                                }
                                if (result['error_login']) {
                                    $.Alert('Bạn đã bị thoát ra');
                                } else if ('alert' in result) {
                                    $.Alert(result.alert);
                                } else if ('location' in result) {
                                    location.href = result.location;
                                } else if ('redirect' in result) {
                                    app_clean();
                                    if (location.hash == result.redirect) {
                                        app_load();
                                    } else {
                                        location.hash = result.redirect;
                                    }
                                } else if ('content' in result) {
                                    $(elem.parentNode.parentNode).onClick(function(event) {
                                        // event.preventDefault();
                                        event.stopPropagation();
                                    });

                                    $(elem)
                                    .htm('<div class="user-select-search" style="display:none"></div>'
                                        +'<div class="user-select-list">'
                                        +result.content+'</div>')
                                    .first("<div class='select-search'>"
                                        +"<input type='text' class='x-text' placeholder='Tìm kiếm theo tên' /></div>")
                                    .each(function(){
                                        $(elem)
                                        .find(".user-select-search")
                                        .onClick(function(){
                                            $(obj).find('span.item').remove();
                                            $(elem).find("input:checked").each(function() {
                                                if (this.name == name) {
                                                    $("<span class='item'>" + "<input type='hidden' name='" + name + "' value='" + this.value + "'/>" + "<a>" + $(this).getAttr('x-label') + "</a>" + "&nbsp;&nbsp;<span class='x' onclick='$(this.parentNode).remove()'></span>" + "</span>").firstTo(obj).find(".close").onClick(function(event) {
                                                        event.preventDefault();
                                                        event.stopPropagation();
                                                        $(this.parentNode).animate({
                                                            opacity: 0
                                                        }, {
                                                            duration: 250,
                                                            callback: function() {
                                                                $(this).remove();
                                                            }
                                                        });
                                                    });
                                                }
                                            })
                                        });

                                        $('.select-search input')
                                        .onKeyup(function(){
                                            var v = this, r = document.createElement('div');
                                            if( v.value != '' ){
                                                $(elem)
                                                .find(".tree-item")
                                                .each(function(e){
                                                    if( this.title.toString().toLowerCase().indexOf(v.value.toLowerCase()) > -1 ){
                                                        r.appendChild(this.cloneNode(true));
                                                    }
                                                });

                                                if( v != '' ){
                                                    $(elem)
                                                    .find('.user-select-search')
                                                    .htm( r );
                                                }else{
                                                    $(elem)
                                                    .find('.user-select-search')
                                                    .htm('<i>Không tìm thấy người dùng nào</i>');
                                                }

                                                $(elem)
                                                .find('.user-select-search')
                                                .css({
                                                    display: 'block'
                                                });
                                                $(elem)
                                                .find('.user-select-list')
                                                .css({
                                                    display: 'none'
                                                });
                                            }else{
                                                $(elem)
                                                .find('.user-select-search')
                                                .htm('')
                                                .css({
                                                    display: 'none'
                                                });
                                                $(elem)
                                                .find('.user-select-list')
                                                .css({
                                                    display: 'block'
                                                });
                                            }
                                        });
                                    })
                                    .find("input[type=checkbox],input[type=radio]").each(function() {
                                        var el = this;
                                        $(obj).find("input[name]").each(function() {
                                            if (this.value == el.value) {
                                                el.checked = true;
                                                return false;
                                            }
                                        })
                                    })
                                    .onClick(function() {
                                        $(obj).find('span.item').remove();

                                        $(elem).find("input:checked").each(function() {
                                            if (this.name == name) {
                                                $("<span class='item'>" + "<input type='hidden' name='" + name + "' value='" + this.value + "'/>" + "<a>" + $(this).getAttr('x-label') + "</a>" + "&nbsp;&nbsp;<span class='x' onclick='$(this.parentNode).remove()'></span>" + "</span>").firstTo(obj).find(".close").onClick(function(event) {
                                                    event.preventDefault();
                                                    event.stopPropagation();
                                                    $(this.parentNode).animate({
                                                        opacity: 0
                                                    }, {
                                                        duration: 250,
                                                        callback: function() {
                                                            $(this).remove();
                                                        }
                                                    });
                                                })
                                            }
                                        })
                                    });
                                }
                            }
                        });
                    }
                });
            });
        }).find(".close").onClick(function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this.parentNode).animate({
                opacity: 0
            }, {
                duration: 250,
                callback: function() {
                    $(this).remove();
                }
            });
        });
    };

    //    APP_CALLBACK['editor'] = function () {
    //        $(this)
    //        .find(".x-editor")
    //        .each(function () {
    //            if (this.convertEditor)
    //                return true;
    //
    //            // new nicEditor().panelInstance(this);
    //            // new nicEditor({fullPanel : true}).panelInstance(this);
    //            new nicEditor({
    //                maxHeight : $(this).parent('.lightbox-content').size() > 0 ? 200 : 800,
    //                iconsPath : baseURL + '/style/plugins/editor/icons.gif'
    //            }).panelInstance(this);
    //            // new nicEditor({buttonList :
    //            // ['fontSize','bold','italic','underline','strikeThrough','subscript','superscript','html','image']}).panelInstance('area4');
    //            // new nicEditor({maxHeight : 100}).panelInstance('area5');
    //            this.convertEditor = true
    //        });
    //    };
    APP_CALLBACK['full-editor'] = function() {
        $(this).find(".x-full-editor").each(function() {

            //create a id if elem hasn't
            if (!this.id) this.id = "ckeditor" + (new Date()).getTime();

            var editor = new CKEDITOR.replace(this.id, {
                skin: 'v2'
            });

            this.renderEditor = editor.name;
            editor.setData(this.value);
            CKFinder.setupCKEditor(editor, baseURL + '/style/editor/ckfinder/');
        })
    };

    APP_CALLBACK['view_profile'] = function() {
        $(this).find("a.userlink").onClick(function(event) {
            load_inframe(baseURL + '/' + this.href.split('#')[1], {
                title: 'Thông tin cá nhân của <u>' + this.innerHTML + '</u>'
            });
            event.preventDefault();
            event.stopPropagation();
        });
    }

    /* Action delete with confirm */
    window.module_delete = function(url, id) {

        if (id === undefined) {
            $.Confirm("Bạn có chắc muốn xóa không ?", function() {
                remove_frame(this);
                ajax_load(url);
            });
            return
        }

        var params = $('#' + id).query();

        if (isEmpty(params)) {
            return $.Alert('Chưa có bản ghi nào được chọn');
        }

        params['_json'] = 'yes';

        $.Confirm('Bạn có chắc muốn xóa hết không ?', function() {
            remove_frame(this);
            $.Ajax(url, {
                type: 'POST',
                cache: false,
                create: ajax_show,
                complete: ajax_hide,
                error: function() {
                    $.Alert("Không thể tải dữ liệu.");
                    ajax_hide();
                },
                data: params,
                success: function(data) {
                    try {
                        eval('var result = ' + data + ';');
                    } catch (e) {
                        $.Alert(data);
                        return false;
                    }
                    updateContent('#main-content', result);
                }
            });

        });
    };

    window.select_user = function(elem) {
        var name = elem.getAttribute('x-name');
        $.Ajax(baseURL + '/User/Select/Check?name=' + name, {
            success: function(data) {
                try {
                    eval("var result = " + data + ";");
                } catch (e) {
                    $.Alert(data);
                    return false;
                }
                if (result['error_login']) {
                    $.Alert('Bạn đã bị thoát ra');
                } else if ('alert' in result) {
                    $.Alert(result.alert);
                } else if ('location' in result) {
                    location.href = result.location;
                } else if ('redirect' in result) {
                    app_clean();
                    if (location.hash == result.redirect) {
                        app_load();
                    } else {
                        location.hash = result.redirect;
                    }
                } else if ('content' in result) {
                    $.createLightBox({
                        css_content: {
                            width: '300px',
                            height: '300px'
                        },
                        title: 'Chọn người dùng',
                        callback: function() {
                            var c = this;
                            $(this).htm(result.content).find(".tree-check").onClick(function() {
                                $(elem).htm("<input type='hidden' value='" + this.value + "' name='" + this.name + "'/><b>" + this.getAttribute('x-label') + "</b>");
                                $(c.parentNode).remove();
                            });
                        }
                    });
                }
            }
        });
    };

    var IFRAME = 0;
    window.module_frame = function(url, id, a) {
        a.loading = true;
        if (!a.iframe) {
            a.iframe = document.createElement('iframe');
            a.iframe.name = 'ajaxiframe' + IFRAME++;
            a.iframe.style.display = "none";

            $(a.iframe)
            .appendTo('body');
        }

        $("<form style='display:none'>")
        .appendTo('body')
        .set('action', url)
        .set('method', 'post')
        .set('target', a.iframe.name)
        .each(function() {
            var fo = this;

            $('#' + id).find('input[type=checkbox]').each(function() {
                $(fo).append(this.cloneNode(true))
            });
            this.submit();
            $(this).remove();
        });
    }

    window.module_ajax = function(url, id, obj, opt) {
        if (obj.xhr) {
            obj.xhr.stop();
        }

        var params = $('#' + id).query(),
        opt = opt || {};

        if (opt['warning_empty'] && isEmpty(params)) {
            $.Alert("Chưa có bản ghi nào được chọn");
            return;
        }

        params['_json'] = 'yes';

        obj.xhr = $.Ajax(url, {
            type: 'POST',
            data: params,
            create: ajax_show,
            complete: ajax_hide,
            error: function() {
                $.Alert("Không thể tải dữ liệu.");
                ajax_hide();
            },
            success: function(data) {

                try {
                    eval("var json_data = " + data + ";");
                } catch (e) {
                    $.Alert(data);
                    return false;
                }
                updateContent(null, json_data);
            }
        });
        return false;
    };

    window.module_confirm = function(msg,url,id) {
        if (id === undefined) {
            $.Confirm(msg, function() {
                remove_frame(this);
                ajax_load(url);
            });
            return;
        }

        var params = $('#' + id).query();

        if (isEmpty(params)) {
            return $.Alert('Chưa có bản ghi nào được chọn');
        }

        params['_json'] = 'yes';

        $.Confirm(msg, function() {
            remove_frame(this);
            $.Ajax(url, {
                cache: false,
                create: ajax_show,
                complete: ajax_hide,
                error: function() {
                    $.Alert("Không thể tải dữ liệu.");
                    ajax_hide();
                },
                data: params,
                success: function(data) {
                    try {
                        eval('var result = ' + data + ';');
                    } catch (e) {
                        $.Alert(data);
                        return false;
                    }
                    updateContent('#main-content', result);
                }
            });

        });
    };

    /*
     * Load a form from list checked
     */

    window.ajaxExport = function(url, selector, obj, options) {
        if (obj.xhr) obj.xhr.stop();

        var params = $(selector).query(),
        options = options || {};

        if (isEmpty(params)) {
            $.Alert(App.trans('check_empty_notice'));
            return;
        }

        params['_json'] = 'yes';

        obj.xhr = $.Ajax(url, {
            type: 'GET',
            data: params,
            create: ajax_show,
            complete: ajax_hide,
            error: function() {
                $.Alert(App.trans('ajax_can_not_load'));
                ajax_hide();
            },
            success: function(data) {
                try {
                    eval("var result = " + data + ";");
                } catch (e) {
                    $.Alert(data);
                }

                var fr = null;
                if ('content' in result) {
                    $.createLightBox($.Extend({
                        title: options.title || '&nbsp;',
                        callback: function() {
                            fr = this;
                            fr.is_frame = true;
                            updateContent(fr, result);
                        }
                    }, options || {}));
                } else {
                    updateContent(null, result);
                }
            }
        });
        return false;
    };

    var obj_tool = null;
    $(document).onMousemove(function(event) {
        if (!document.body) return;
        if (!obj_tool) {
            obj_tool = $("<div class='tooltip' style='position:absolute;top:10px;left:10px;z-index:10000'></div>").appendTo("body").k(0);
        }

        if (!obj_tool.activeElement || !$.Contains(obj_tool.activeElement, document.body)) {
            obj_tool.activeElement = null;
            $(obj_tool).css({
                display: 'none'
            });
        }

        if ($(obj_tool).css('display') == 'none') {
            return;
        }

        $(obj_tool).each(function() {
            var t = event.pageY,
            l = event.pageX,
            h = $(window).height(),
            w = $(window).width(),
            p = 15;

            var scrollTop = document.body.scrollTop;
            var scrollLeft = document.body.scrollLeft;

            var x = (l < w / 2) ? (l + p) : (l - $(this).width() - p);
            var y = (t < h / 2) ? (t + p) : (t - $(this).height() - p);

            $(this).css({
                position: 'absolute',
                top: y + 'px',
                left: x + 'px'
            })
        });
    });

    APP_CALLBACK['tooltip'] = function() {
        $(this).find(".x-tooltip").onMouseover(function() {
            var elem = $(this).find(".x-toolbox").k(0);
            if (obj_tool.activeElement != elem) {
                obj_tool.activeElement = $(this).find(".x-toolbox").k(0);
                $(obj_tool).htm(obj_tool.activeElement.innerHTML).css({
                    display: 'inline-block'
                });
            }
        }).onMouseout(function() {
            if (obj_tool.activeElement == $(this).find(".x-toolbox").k(0)) {
                obj_tool.activeElement = null;
                $(obj_tool).css({
                    display: 'none'
                });
            }
        });
    };

    function simulatedClick(target, options) {
        if (target.ownerDocument.createEvent) {
            var event = target.ownerDocument.createEvent('MouseEvents');
            options = options || {};

            // Set your default options to the right of ||
            var opts = {
                type: options.click || 'click',
                canBubble: options.canBubble || true,
                cancelable: options.cancelable || true,
                view: options.view || target.ownerDocument.defaultView,
                detail: options.detail || 1,
                screenX: options.screenX || 0,
                // The coordinates within the
                // entire page
                screenY: options.screenY || 0,
                clientX: options.clientX || 0,
                // The coordinates within the
                // viewport
                clientY: options.clientY || 0,
                ctrlKey: options.ctrlKey || false,
                altKey: options.altKey || false,
                shiftKey: options.shiftKey || false,
                metaKey: options.metaKey || false,
                // I *think* 'meta' is
                // 'Cmd/Apple' on Mac, and
                // 'Windows key' on Win. Not
                // sure, though!
                button: options.button || 0,
                // 0 = left, 1 = middle, 2 =
                // right
                relatedTarget: options.relatedTarget || null
            }

            // Pass in the options
            event.initMouseEvent(
                opts.type, opts.canBubble, opts.cancelable, opts.view, opts.detail, opts.screenX, opts.screenY, opts.clientX, opts.clientY, opts.ctrlKey, opts.altKey, opts.shiftKey, opts.metaKey, opts.button, opts.relatedTarget);

            // Fire the event
            target.dispatchEvent(event);
        } else {
            var opts = {
                pointerX: 0,
                pointerY: 0,
                clientX: 0,
                clientY: 0,
                button: 0,
                ctrlKey: false,
                altKey: false,
                shiftKey: false,
                metaKey: false,
                bubbles: true,
                cancelable: true
            };
            var evt = document.createEventObject();
            for (var x in opts) {
                evt[x] = opts[x]
            }

            target.fireEvent('onclick', evt);
        }
    }

    window.simulatedClick = simulatedClick;

    $(document).onClick(function() {
        $(this).find("input[type=checkbox]").each(function() {
            var elem = this.renderElement;
            if (!elem) return;

            $(elem)[this.checked ? 'addClass' : 'removeClass']("checked");
            $(elem)[this.disabled ? 'addClass' : 'removeClass']("disabled");
        });
    });

    APP_CALLBACK['checkbox_render'] = function() {
        return;
        $(this).find("input[type=checkbox]").each(function() {
            if ($(this).css('display') != 'none') {
                var elem = document.createElement("span");
                this.renderElement = elem;

                $(this).css({
                    display: 'none'
                });

                if (this.checked) {
                    $(elem).addClass("checked");
                }

                if (this.disabled) {
                    $(elem).addClass("disabled");
                }

                elem.originElement = this;

                $(elem).addClass("check").afterTo(this);

                if (this.getAttribute('readonly') != "readonly") {
                    $(elem).onClick(function(event) {
                        var checked = elem.originElement.getAttribute('checked') == 'checked';

                        if (!$(this).hasClass("disabled")) {
                            simulatedClick(elem.originElement);
                        }

                        //fix chrome with value == 0
                        elem.originElement.setAttribute('checked', checked ? null : 'checked');

                        event.preventDefault();
                        event.stopPropagation();
                    })

                }
            }
        });
    };

    APP_CALLBACK["check_list"] = function() {
        $(this).find(".x-check-container").onMouseenter(function() {
            $(this).find(".x-check").addClass("x-check-over");
            $(this).find(".x-check-options").css({
                display: 'block'
            })
        }).onMouseleave(function() {
            $(this).find(".x-check").removeClass("x-check-over");
            $(this).find(".x-check-options").css({
                display: 'none'
            })
        });

        var $tb = $(this).find("table.x-list");
        $tb.find("td:last-child .x-check-item,td:last-child .x-check input[type=checkbox]").add("th:last-child .x-check-item,th:last-child .x-check input[type=checkbox]").onClick(function() {
            var sl = this.getAttribute("rows");
            if (this.tagName != "INPUT") {
                this.checked = !this.checked;
            }

            var checked = this.checked,
            elem = this;

            $tb.find("td:last-child input[type=checkbox]").each(function() {
                this.checked = false;
                if (this.parentNode.tagName == "TD") {
                    $(this.renderElement).removeClass("checked");
                    $(this.parentNode.parentNode).removeClass("x-check-row");
                }
            })

            if (checked) {
                $tb.find(sl).find("td:last-child input[type=checkbox]:not([disabled])").each(function() {
                    if (this != elem) {
                        this.checked = true;
                        $(this.renderElement).addClass("checked");
                        if (this.parentNode.tagName == "TD") {
                            $(this.parentNode.parentNode).addClass("x-check-row");
                        }
                    }
                });
            }

            if ($tb.find("td:last-child input:not(:checked)").size() > 0) {
                $tb.find("td:last-child .x-check input[type=checkbox]").each(function() {
                    this.checked = false;
                    $(this.renderElement).removeClass("checked")
                })
            } else {
                $tb.find("td:last-child .x-check input[type=checkbox]").each(function() {
                    this.checked = true;
                    $(this.renderElement).addClass("checked")
                })
            }

            $tb.find("td:last-child .x-check-item").each(function() {
                var all = true,
                empty = true;
                var sl = this.getAttribute("rows");
                $tb.find(sl).find("td:last-child input[type=checkbox]").each(function() {
                    empty = false;
                    if (this.checked === false) {
                        return (all = false);
                    }
                });

                if (all && !empty) {
                    if (this == elem) $(this).addClass("x-check-current");
                } else {
                    $(this).removeClass("x-check-current");
                    this.checked = false
                }
            });
        });

        // @hover single row
        $tb.find("td:last-child input[type=checkbox]").onClick(function() {
            $(this).parent("table.x-list").find("tr").each(function() {
                $(this)[$(this).find("td:last-child input[type=checkbox]").get('checked') ? 'addClass' : 'removeClass']("x-check-row")
            });
        });
    };

    APP_CALLBACK["checkbox"] = function() {

        $(this).find("form").each(function() {
            var fo = this;

            function fn() {
                var a = $(this).attr("_related").split(','),
                css = [];
                for (var i = 0; i < a.length; i++) {
                    css.push(["input[name=" + a[i] + "]", "select[name=" + a[i] + "]", "textarea[name=" + a[i] + "]"].join(","));
                }
                $(fo).find(css.join(",")).set("disabled", !this.checked);
                var is_not = !this.checked;
                if (this.getAttribute('_related_selector')) {
                    $(this.getAttribute('_related_selector')).css({
                        opacity: is_not ? '0.5' : 1
                    }).find("input,select,textarea").set('disabled', is_not);
                }
            }

            function fn_sl() {
                var a = $(this).attr("_related").split(','),
                css = [];
                for (var i = 0; i < a.length; i++) {
                    css.push("input[name=" + a[i] + "]" + "," + "select[name=" + a[i] + "]");
                }

                var is_not = (',' + (this.getAttribute('_related_value') || '') + ',').indexOf(',' + this.value + ',') == -1;
                $(fo).find(css.join(",")).set("disabled", is_not);

                if (this.getAttribute('_related_selector')) {
                    $(this.getAttribute('_related_selector')).css({
                        opacity: is_not ? '0.5' : 1
                    }).find("input,select,textarea").set('disabled', is_not);
                }


            }

            $(this).find("input[type=checkbox][_related]").onClick(fn).each(fn);

            $(this).find("select[_related]").onChange(fn_sl).each(fn_sl);
        });
    };

    //    APP_CALLBACK["resizetb"] = function(){
    //        return true;
    //        $(this)
    //        .find("table.x-list")
    //        .css("table-layout:fixed")
    //        .tbresize({
    //            minWidth: 10,
    //            noresize: [":first-child",".k",":last-child",function(){
    //                return this.parentNode.cells.length - 2 == this.cellIndex
    //            }]
    //        });
    //    };
    APP_CALLBACK["filters"] = function() {
        $(this).find(".list-filter").onMouseenter(function() {
            $(this).find(".links").slideDown(200)
        }).onMouseleave(function() {
            $(this).find(".links").slideUp(200)
        })
    };

    //set scroll
    APP_CALLBACK["scroll"] = function() {
        $(this).find(".scroll").showScroll();
    };

    //auto collapse blockquote
    APP_CALLBACK["blockquote"] = function() {
        $(this).find(".user-message-row-content blockquote").each(function() {
            $(this).attr('js', 'yes');
            var elem = this,
            div = $("<div title='Hiện/ẩn trích dẫn' class='blockquote-viewmore'><div>---</div></div>").k(0);
            $(div).beforeTo(elem).onMousedown(function(e) {
                e.preventDefault();
                e.stopPropagation();
            }).onClick(function() {
                $(elem).css({
                    display: $(elem).css('display') == 'none' ? 'block' : 'none'
                });
            });
            $(elem).css({
                display:'none'
            });
        });
    };

    //confirm message
    APP_CALLBACK["button_confirm"] = function(){
        $(this)
        .find("a[confirm-message],button[confirm-message],input[confirm-message]")
        .onClick(function(event){
            var elem = this;
            if( !elem.is_confirming ){
                event.preventDefault();
                event.stopPropagation();
                $.Confirm(elem.getAttribute('confirm-message'),function(){
                    elem.is_confirming = true;
                    simulatedClick(elem);
                });
            }
            elem.is_confirming = false;
        });
    };
})(Owl);