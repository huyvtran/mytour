(function($) {
    var defaultSettings = {


    };
    var MAX_SCREEN_WIDTH = screen.width,
    MAX_SCREEN_HEIGHT = screen.height,
    memory = [],
    isInitEvent = false,
    zIndex = 1000,
    EventData = {
        Drag: {
            Elem: null,
            X: 0,
            Y: 0
        },
        Resize: {
            Elem: null,
            X: 0,
            Y: 0,
            Dir: null
        }
    };

    function getScreenSize() {
        return {
            width: Math.max(document.documentElement ? document.documentElement.scrollWidth : 0, document.body ? document.body.scrollWidth : 0, $(window).width(), window.screen.width || 0),
            height: Math.max(document.documentElement ? document.documentElement.scrollHeight : 0, document.body ? document.body.scrollHeight : 0, $(window).height(), window.screen.height || 0)
        }
    }

    function initEventResize() {
        if (isInitEvent) return;
        $(window).addEvent('resize:lightbox', function() {
            var screenSize = getScreenSize();
            MAX_SCREEN_WIDTH = screenSize.width;
            MAX_SCREEN_HEIGHT = screenSize.height;
        });
    }

    function closeEventResize() {
        $(window).remveEvent('resize:lightbox')
    }


    function showMask() {

    }

    function closeMask() {

    }



    function createLightBox(options) {
        options = $.Extend(defaultSettings, options);
        initEventResize();
        var settings = $.Extend({
            center: true,
            title: "&nbsp;",
            mask: false,
            toolbar: true,
            css: {},
            css_content: {},
            id: 'lightbox-' + (new Date).getTime(),
            auto_hidden: false,
            callback: function() {}
        }, options || {}),
        elem;

        if ($('#' + settings.id).size() == 0) {
            elem = $("<div class='lightbox" + (settings.auto_hidden ? ' app-auto-hidden' : '') + "' id='" + settings.id + "'>" + "<div class='lightbox-inner'>" + "<div class='lightbox-bar'>" + "<span class='lightbox-title' title='" + settings.title.replace(/[<>"]/gi, ' ') + "'>" + settings.title + "</span>" + "<div class='lightbox-bts'>" + "<a class='lightbox-min'></a>" + "<a class='lightbox-max'></a>" + "<a class='lightbox-close'></a>" + "</div></div>" + "<div class='lightbox-content'>Loading...</div>" + "</div></div>").k(0);
            memory[settings.id] = elem;
            elem.settings = settings;
            elem.elemContent = $(elem).find(".lightbox-content").k(0);
            elem.setCenter = function() {
                var top = Math.min(170, Math.round(Math.max(0, $(window).height() / 2 - $(this).height() / 2 + Math.round(Math.random() * 100) - 50)));
                var left = Math.round(Math.max(0, $(window).width() / 2 - $(this).width() / 2 + Math.round(Math.random() * 100) - 50));
                $(this).css({
                    visibility: 'visible',
                    top: top + "px",
                    left: left + "px"
                });
            };

            if (settings.resize) {
                var labels = {
                    w: "<div style='width:2px;height:100%; cursor:w-resize;position:absolute;left:0px;top:0px;z-index:1'></div>",
                    e: "<div style='width:2px;height:100%; cursor:e-resize;position:absolute;right:0px;top:0px;z-index:1'></div>",
                    n: "<div style='width:100%;height:2px; cursor:n-resize;position:absolute;top:0px;left:0px;z-index:1'></div>",
                    s: "<div style='width:100%;height:2px; cursor:s-resize;position:absolute;bottom:0px;left:0px;z-index:1'></div>",
                    nw: "<div style='width:3px;height:3px; cursor:nw-resize;position:absolute;top:0px;left:0px;z-index:2'></div>",
                    ne: "<div style='width:3px;height:3px; cursor:ne-resize;position:absolute;top:0px;right:0px;z-index:2'></div>",
                    sw: "<div style='width:3px;height:3px; cursor:sw-resize;position:absolute;bottom:0px;left:0px;z-index:2'></div>",
                    se: "<div style='width:3px;height:3px; cursor:se-resize;position:absolute;bottom:0px;right:0px;z-index:2'></div>"
                };
                for (var x in labels) {
                    $(labels[x]).set('_dir', x).appendTo(elem).onMousedown(function(event) {
                        EventData.Resize.X = event.pageX;
                        EventData.Resize.Y = event.pageY;
                        EventData.Resize.Dir = this._dir;
                        elem.resize = settings.resize;
                        EventData.Resize.Elem = elem;
                        $("body").addClass('lightbox-unselect lb-' + this._dir).selectText(0, -1);
                    });
                }
            }
            //                //add item to toolbar
            //                if (settings.toolbar) {
            //                    LightBox.ToolBar.Add(settings);
            //                }
            $(elem)
            .find(".lightbox-bar .lightbox-close,.box-close")
            .onClick(function() {
                $(this).parent(".lightbox").each(eventCloseBox);
            })
            .find(".lightbox-bar > .lightbox-title")
            .htm(settings.title);

            //resize
            //                $(elem).find(".lightbox-bar .lightbox-min").onClick(function() {
            //                    $(this).parent(".lightbox").each(LightBox.Action.Min);
            //                });
            //                $(elem).find(".lightbox-bar .lightbox-max").onClick(function() {
            //                    $(this).parent(".lightbox").each(LightBox.Action.Max);
            //                });
            $(elem).css(settings.css || {}).onMousedown(function(event) {
                $(this).css("z-index:" + (zIndex++));
                EventData.Drag.X = event.offsetX;
                EventData.Drag.Y = event.offsetY;
            }).find(".lightbox-bar").onMousedown(function(event) {
                EventData.Drag.Elem = this.parentNode.parentNode;
                $("body").addClass('lightbox-unselect');
                event.preventDefault();
            });

            $(elem.elemContent)
            .css(settings.css_content || {});
        } else {
            memory[settings.id] = $('#' + settings.id).k(0);
            elem = memory[settings.id];
        }

        if (settings.mask) {
            showMask();
        }

        $(elem).css({
            visibility: 'hidden',
            zIndex: zIndex++,
            display: 'inline-block'
        }).appendTo('body');

        $(elem.elemContent).htm('').each(settings.callback);
        elem.setCenter();
    }










    var LightBox = {
        Data: {
            ZIndex: 2000,
            Box: [],
            ToolBarId: 'lightbox-toolbar'
        },
        Helper: {
            GetRange: function(a, b, c) {
                var x = a;
                if (b) x = Math.max(b, x);
                if (c) x = Math.min(c, x);

                return x;
            },
            MaxWidth: function() {
                return ('innerWidth' in window ? window.innerWidth : document.documentElement ? document.documentElement.clientWidth : document.body.clientWidth) - 50;
            },
            MaxHeight: function() {
                return ('innerHeight' in window ? window.innerHeight : document.documentElement ? document.documentElement.clientHeight : document.body.clientHeight) - 50;
            }
        },
        Mask: {
            ID: 'mask',
            Create: function() {
                $("<div></div>").set('id', this.ID).css({
                    opacity: '0.3',
                    background: '#000',
                    position: 'fixed',
                    top: '0px',
                    left: '0px',
                    'z-index': 2000
                }).appendTo("body");
            },
            Show: function() {
                if ($('#' + this.ID).size() == 0) {
                    this.Create();
                }
                $('#' + this.ID).css("display:block");

                this.Resize();
            },
            Hide: function() {

                $('#' + this.ID).css("display:none");
            },
            Resize: function() {
                var width = Math.max(document.body.scrollTop, document.scrollTop || 0) + Math.max($(window).width(), $("#wrapper").width(), $("body").width());
                var height = Math.max(document.body.scrollTop, document.scrollTop || 0) + Math.max($(window).height(), $("#wrapper").height(), $("body").height());
                $('#' + this.ID).css({
                    width: width + 'px',
                    height: height + 'px'
                });
            }
        },
        Event: {
            Data: {
                Drag: {
                    Elem: null,
                    X: 0,
                    Y: 0
                },
                Resize:

                {
                    Elem: null,
                    X: 0,
                    Y: 0,
                    Dir: null
                }
            },
            MouseMove: function(event) {

                //console.log(event.pageX+":"+event.pageY);
                if (dragElem || resizeElem) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                var dragElem = LightBox.Event.Data.Drag.Elem,
                dragX = LightBox.Event.Data.Drag.X,
                dragY = LightBox.Event.Data.Drag.Y,
                resizeElem = LightBox.Event.Data.Resize.Elem,
                resizeX = LightBox.Event.Data.Resize.X,
                resizweY = LightBox.Event.Data.Resize.Y,
                resizeDir = LightBox.Event.Data.Resize.Dir,
                range = LightBox.Helper.GetRange;

                if (dragElem) {
                    $(dragElem).css({
                        left: Math.min(Math.max(0, event.pageX - dragX), LightBox.Helper.MaxWidth()) + 'px',
                        top: Math.min(Math.max(0, event.pageY - dragY), LightBox.Helper.MaxHeight()) + 'px'
                    });
                }
                if (resizeElem) {
                    var resize = resizeElem.resize,
                    top = $(resizeElem).top(),
                    left = $(resizeElem).left(),
                    dx = event.pageX - resizeX,
                    dy = event.pageY - resizweY,
                    //resize element inside
                    $e = $(resizeElem).find(resize.selector),
                    sx = parseInt($e.css('width')),
                    sy = parseInt($e.css('height'));
                    switch (resizeDir) {
                        case "n":
                            $(resizeElem).css({
                                top: Math.max(0, top + dy) + 'px'
                            });
                            $e.css({
                                height: range(sy - dy, resize.minHeight, resize.maxHeight) + 'px'
                            });
                            break;
                        case "s":
                            $e.css({
                                height: range(sy + dy, resize.minHeight, resize.maxHeight) + 'px'
                            });
                            break;
                        case "w":
                            $(resizeElem).css({
                                left: Math.max(0, left + dx) + 'px'
                            });
                            $e.css({
                                width: range(sx - dx, resize.minWidth, resize.maxWidth) + 'px'
                            });
                            break;
                        case "e":
                            $e.css({
                                width: range(sx + dx, resize.minWidth, resize.maxWidth) + 'px'
                            });
                            break;
                        case "nw":
                            $(resizeElem).css({
                                top: Math.max(0, top + dy) + 'px',
                                left: Math.max(0, left + dx) + 'px'
                            });
                            $e.css({
                                width: range(sx - dx, resize.minWidth, resize.maxWidth) + 'px',
                                height: range(sy - dy, resize.minHeight, resize.maxHeight) + 'px'
                            });
                            break;
                        case "ne":
                            $(resizeElem).css({
                                top: Math.max(0, top + dy) + 'px'
                            });
                            $e.css({
                                width: range(sx + dx, resize.minWidth, resize.maxWidth) + 'px',
                                height: range(sy - dy, resize.minHeight, resize.maxHeight) + 'px'
                            });
                            break;
                        case "se":
                            $e.css({
                                width: range(sx + dx, resize.minWidth, resize.maxWidth) + 'px',
                                height: range(sy + dy, resize.minHeight, resize.maxHeight) + 'px'
                            });
                            break;
                        case "sw":
                            $(resizeElem).css({
                                left: Math.max(0, left + dx) + 'px'
                            });
                            $e.css({
                                width: range(sx - dx, resize.minWidth, resize.maxWidth) + 'px',
                                height: range(sy + dy, resize.minHeight, resize.maxHeight) + 'px'
                            });
                            break;
                    }
                    LightBox.Event.Data.Resize.X = event.pageX;

                    LightBox.Event.Data.Resize.Y = event.pageY;
                }
            },
            MouseUp: function() {
                LightBox.Event.Data.Drag.Elem = null;
                LightBox.Event.Data.Resize.Elem = null;

                $("body").removeClass('lightbox-unselect lb-n lb-s lb-w lb-e lb-ne lb-nw lb-se lb-sw');
            },
            MouseDown: function(event) {
                if (event.KEY_ESC) {
                    $(LightBox.Event.Data.Drag.Elem).each(function() {
                        $(this).remove();
                        LightBox.Mask.Hide();
                    });
                }
                return true;
            }
        },
        Action: {
            Close: function() {
                $(this).each(function() {
                    LightBox.Mask.Hide();
                    var elem = this;
                    $(this).remove();
                    delete LightBox.Data.Box[elem.settings.id];
                    if (elem.toolbar) {
                        var size = this.toolbar.offsetWidth;
                        var marginLeft = parseInt($("#toolbar-body-main").css("marginLeft")) || 0;
                        size = Math.min(0, marginLeft + size);
                        $(this.toolbar).remove();
                        $("#toolbar-body-main").stop().animate({
                            marginLeft: size + 'px'
                        }, {
                            easing: 'swing',
                            duration: 200,
                            callback: function() {
                                LightBox.ToolBar.CheckScreen();
                                LightBox.ToolBar.CheckControl();

                                (elem.settings.closeEvent ||
                                    function() {})();
                            }
                        });
                    } else {

                        (elem.settings.closeEvent ||
                            function() {})();
                    }
                });
            },
            Min: function() {
                $(this).css({
                    display: 'none'
                });
                if (this.figgy) $(this.figgy).stop().remove();
                this.figgy = $("<div class='lightbox-figgy'></div>").k(0);
                $(this.figgy).css({
                    width: ($(this).width() - 2) + 'px',
                    height: ($(this).height() - 2) + 'px',
                    position: 'fixed',
                    top: $(this).top() + 'px',
                    left: $(this).left() + 'px',
                    opacity: 1
                }).appendTo("body").animate({
                    width: ($(this.toolbar).width() - 2) + 'px',
                    height: ($(this.toolbar).height() - 2) + 'px',
                    top: $(this.toolbar).top() + 'px',
                    left: $(this.toolbar).left() + 'px',
                    opacity: 0
                }, {
                    duration: 200,
                    easing: 'swing',
                    callback: function() {

                        $(this).remove();
                    }
                });
            },
            Restore: function() {
                if (this.figgy) $(this.figgy).stop().remove();
                var elem = this;
                this.figgy = $("<div class='lightbox-figgy'></div>").k(0);
                $(this.figgy).css({
                    width: ($(this.toolbar).width() - 2) + 'px',
                    height: ($(this.toolbar).height() - 2) + 'px',
                    top: $(this.toolbar).top() + 'px',
                    position: 'fixed',
                    left: $(this.toolbar).left() + 'px',
                    opacity: 0
                }).appendTo("body").animate({
                    width: ($(this).width() - 2) + 'px',
                    height: ($(this).height() - 2) + 'px',
                    top: $(this).top() + 'px',
                    left: $(this).left() + 'px',
                    opacity: 1
                }, {
                    duration: 200,
                    easing: 'swing',
                    callback: function() {
                        $(elem).css({
                            display: 'inline-block',
                            zIndex: (LightBox.Data.ZIndex++)
                        });

                        $(this).remove();
                    }
                });
            },
            Max: function() {
                var width = $('body').width() - (parseInt($(this).find(".lightbox-inner").css("borderLeftWidth")) || 0) - (parseInt($(this).find(".lightbox-inner").css("borderRightWidth")) || 0) - (parseInt($(this).find(".lightbox-content").css("paddingTop")) || 0) - (parseInt($(this).find(".lightbox-content").css("paddingTop")) || 0);
                var height = $(window).height() - (parseInt($(this).find(".lightbox-inner").css("borderTopWidth")) || 0) - (parseInt($(this).find(".lightbox-inner").css("borderBottomWidth")) || 0) - (parseInt($(this).find(".lightbox-content").css("paddingLeft")) || 0) - (parseInt($(this).find(".lightbox-content").css("paddingRight")) || 0) - (parseInt($(this).find(".lightbox-bar").height()) || 0);
                $(this).css({
                    top: '0px',
                    left: '0px'
                }).find(".lightbox-content").css({
                    width: width + 'px',
                    height: height + 'px'
                });
            }
        },
        Create: function(options) {
            var settings = $.Extend({
                center: true,
                title: "",
                mask: false,
                toolbar: true,
                //accept add to toolbar
                css: {},
                css_content: {},
                id: 'lightbox-' + (new Date).getTime(),
                auto_hidden: false,
                callback: function() {}
            }, options || {});
            if (settings.title === undefined) settings.title = "&nbsp;"; //$('#'+settings.id).remove();
            if ($('#' + settings.id).size() == 0) {
                LightBox.Data.Box[settings.id] = $("<div class='lightbox" + (settings.auto_hidden ? ' app-auto-hidden' : '') + "' id='" + settings.id + "'>" + "<div class='lightbox-inner'>" + "<div class='lightbox-bar'>" + "<span class='lightbox-title' title='" + settings.title.replace(/[<>"]/gi, ' ') + "'>" + settings.title + "</span>" + "<div class='lightbox-bts'>" + "<a class='lightbox-min'></a>" + "<a class='lightbox-max'></a>" + "<a class='lightbox-close'></a>" + "</div>" + "</div>" + "<div class='lightbox-content'>Loading...</div>" + "</div>" + "</div>").k(0);
                var elem = LightBox.Data.Box[settings.id];
                elem.settings = settings;
                elem.elemContent = $(elem).find(".lightbox-content").k(0);

                elem.setCenter = function() {
                    //alert($(this).css("top"))
                    //if( $(this).css("top") !== "" && $(this).css("top") !== null ){
                    var top = Math.min(170, Math.round(Math.max(0, $(window).height() / 2 - $(this).height() / 2 + Math.round(Math.random() * 100) - 50)));
                    var left = Math.round(Math.max(0, $(window).width() / 2 - $(this).width() / 2 + Math.round(Math.random() * 100) - 50));
                    $(this).css({
                        visibility: 'visible',
                        top: top + "px",
                        left: left + "px"
                    }); //}
                };
                if (settings.resize) {
                    var labels = {
                        w: "<div style='width:2px;height:100%; cursor:w-resize;position:absolute;left:0px;top:0px;z-index:1'></div>",
                        e: "<div style='width:2px;height:100%; cursor:e-resize;position:absolute;right:0px;top:0px;z-index:1'></div>",
                        n: "<div style='width:100%;height:2px; cursor:n-resize;position:absolute;top:0px;left:0px;z-index:1'></div>",
                        s: "<div style='width:100%;height:2px; cursor:s-resize;position:absolute;bottom:0px;left:0px;z-index:1'></div>",
                        nw: "<div style='width:3px;height:3px; cursor:nw-resize;position:absolute;top:0px;left:0px;z-index:2'></div>",
                        ne: "<div style='width:3px;height:3px; cursor:ne-resize;position:absolute;top:0px;right:0px;z-index:2'></div>",
                        sw: "<div style='width:3px;height:3px; cursor:sw-resize;position:absolute;bottom:0px;left:0px;z-index:2'></div>",
                        se: "<div style='width:3px;height:3px; cursor:se-resize;position:absolute;bottom:0px;right:0px;z-index:2'></div>"
                    };
                    for (var x in labels) {
                        $(labels[x]).set('_dir', x).appendTo(elem).onMousedown(function(event) {
                            LightBox.Event.Data.Resize.X = event.pageX;
                            LightBox.Event.Data.Resize.Y = event.pageY;
                            LightBox.Event.Data.Resize.Dir = this._dir;
                            elem.resize = settings.resize;
                            LightBox.Event.Data.Resize.Elem = elem;
                            $("body").addClass('lightbox-unselect lb-' + this._dir).selectText(0, -1);
                        });
                    }
                }
                //add item to toolbar
                if (settings.toolbar) {
                    LightBox.ToolBar.Add(settings);
                }
                $(elem).find(".lightbox-bar .lightbox-close,.box-close").onClick(function() {
                    $(this).parent(".lightbox").each(LightBox.Action.Close);
                }).find(".lightbox-bar > .lightbox-title").htm(settings.title); //resize
                $(elem).find(".lightbox-bar .lightbox-min").onClick(function() {
                    $(this).parent(".lightbox").each(LightBox.Action.Min);
                });
                $(elem).find(".lightbox-bar .lightbox-max").onClick(function() {
                    $(this).parent(".lightbox").each(LightBox.Action.Max);
                });
                $(elem).css(settings.css || {}).onMousedown(function(event) {
                    $(this).css("z-index:" + (LightBox.Data.ZIndex++));
                    LightBox.Event.Data.Drag.X = event.offsetX;
                    LightBox.Event.Data.Drag.Y = event.offsetY;
                }).find(".lightbox-bar").onMousedown(function(event) {
                    LightBox.Event.Data.Drag.Elem = this.parentNode.parentNode;
                    $("body").addClass('lightbox-unselect');
                    event.preventDefault(); //event.stopPropagation();
                });
                $(elem.elemContent).css(settings.css_content || {});
            } else {
                LightBox.Data.Box[settings.id] = $('#' + settings.id).k(0);
            }
            if (settings.mask) {
                LightBox.Mask.Show();
            }
            $(LightBox.Data.Box[settings.id]).css({
                visibility: 'hidden',
                zIndex: LightBox.Data.ZIndex++,
                display: 'inline-block'
            }).appendTo('body');
            $(LightBox.Data.Box[settings.id].elemContent).htm('').each(settings.callback);
            LightBox.Data.Box[settings.id].setCenter();
        },
        ToolBar: {
            Init: function() {
                $("#toolbar-body-control .ml").onClick(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $("#toolbar-body-main").stop();
                    var size = $("#toolbar-body-main .toolbar-item").width() + parseInt($("#toolbar-body-main .toolbar-item").css("marginLeft")) || 0;
                    if (!size) return;
                    var width = $("#toolbar-body-main").width();
                    var pad = $("#toolbar-body-control").width() + 2;
                    var marginLeft = parseInt($("#toolbar-body-main").css('marginLeft')) || 0;
                    var maxWidth = $("#toolbar-body").width();
                    if (width + marginLeft < maxWidth) {
                        return;
                    }
                    $("#toolbar-body-main").animate({
                        marginLeft: '-=' + Math.min(size, width + marginLeft - maxWidth + pad)
                    },

                    {
                        easing: 'swing',
                        duration: 200,
                        callback: function() {
                            LightBox.ToolBar.CheckControl();
                        }
                    });
                });
                $("#toolbar-body-control .mr").onClick(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $("#toolbar-body-main").stop();
                    var size = $("#toolbar-body-main .toolbar-item").width() + parseInt($("#toolbar-body-main .toolbar-item").css("marginLeft")) || 0;
                    if (!size) return;
                    var width = $("#toolbar-body-main").width();
                    var pad = $("#toolbar-body-control").width() + 2;
                    var marginLeft = parseInt($("#toolbar-body-main").css('marginLeft')) || 0;
                    var maxWidth = $("#toolbar-body").width();
                    if (marginLeft > 0) {
                        return;
                    }
                    $("#toolbar-body-main").stop().animate({
                        marginLeft: '+=' + Math.min(size, Math.abs(marginLeft))
                    },

                    {
                        easing: 'swing',
                        duration: 200,
                        callback: function() {

                            LightBox.ToolBar.CheckControl();
                        }
                    });
                });
            },
            Add: function(settings) {
                var obj = LightBox.Data.Box[settings.id];
                var title = (settings.toolbar_title ? settings.toolbar_title : $("<span>").htm(settings.title).text());
                $('<div class="toolbar-item" title="' + title + '">' + '<div class="c"></div>' + title + '</div>').appendTo("#toolbar-body-main").each(function() {
                    obj.toolbar = this;
                    this.box = obj;
                }).onClick(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if ($(this.box).css("display") != "none") {
                        $(this.box).each(LightBox.Action.Min);
                    } else {
                        $(this.box).each(LightBox.Action.Restore);
                    }
                }).find(".c").onClick(function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    $(this.parentNode.box).each(LightBox.Action.Close);
                });
                LightBox.ToolBar.CheckScreen();

                LightBox.ToolBar.CheckControl();
            },
            CheckScreen: function(reset) {
                var width = $("body").width() - $("#toolbar-bts").width();
                $("#toolbar-body").css({
                    width: width + 'px'
                });
                if (reset) {
                    $("#toolbar-body-main").css({
                        marginLeft: '0px'
                    });
                }
                $("#toolbar-body-control").css({
                    display: (width < $("#toolbar-body-main").width()) ? 'inline-block' : 'none'
                });
            },
            CheckControl: function() {
                if ($("#toolbar-body-control").css("display") == "none") {
                    return;
                }
                var marginLeft = parseInt($("#toolbar-body-main").css('marginLeft')) || 0,
                pad = $("#toolbar-body-control").width() + 2,
                maxWidth = $("body").width() - $("#toolbar-bts").width(),
                width = $("#toolbar-body-main").width();
                if (marginLeft < 0) {
                    $("#toolbar-body-control .mr span").addClass("mr-a");
                } else {
                    $("#toolbar-body-control .mr span").removeClass("mr-a");
                }
                if (maxWidth - pad < width + marginLeft) {
                    $("#toolbar-body-control .ml span").addClass("ml-a");
                } else {
                    $("#toolbar-body-control .ml span").removeClass("ml-a");
                }
            }
        }
    };
    $(function() {
        LightBox.ToolBar.Init();
        $(window).onResize(function() {
            LightBox.ToolBar.CheckScreen(true);
            LightBox.ToolBar.CheckControl();
            LightBox.Mask.Resize();
        });
    });


    $(function() {

        $(document).addEvent({
            'mousemove:lightbox': LightBox.Event.MouseMove,
            'mouseup:lightbox': LightBox.Event.MouseUp,
            'mousedown:lightbox': LightBox.Event.MouseDown
        });

        $.createLightBox = LightBox.Create;

        $.Alert = function(message, callback) {

            var fn = callback ||
            function() {};
            $.createLightBox({
                id: '__alert__',
                title: 'Lời nhắn từ hệ thống',
                mask: true,
                toolbar: false,
                css:

                {
                    'z-index': 5000,
                    'text-align': 'center'
                },
                callback: function() {

                    $(this).htm(message);
                },
                closeEvent: callback ||
                function() {}
            });
        };

        $.Confirm = function(message, fc) {

            fc = fc ||
            function() {};
            $.createLightBox({
                id: '__confirm__',
                auto_hidden: true,
                mask: true,
                title: '',
                toolbar: false,
                css:

                {
                    'z-index': 5000
                },
                callback: function() {
                    var elem = this;
                    $(this).htm(message + "<div style='text-align:center;padding:10px'><div class='x-button x-ok'>Đồng ý</div>" + "<div class='x-button x-cancel'>Bỏ qua</div></div>").find(".x-cancel").onClick(function() {
                        LightBox.Mask.Hide();
                        remove_frame(this);
                    });
                    $(this).find(".x-ok").onClick(function() {
                        LightBox.Mask.Hide();
                        remove_frame(this);
                        fc.call(elem);
                    });
                }
            });
        };
    });
    window.LightBox = LightBox;

    window.remove_frame = function(obj) {
        LightBox.Mask.Hide();
        $(obj).parent('.lightbox').each(LightBox.Action.Close);
    };
})(Owl);