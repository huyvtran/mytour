/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function getSelectionBoundaryElement(isStart) {
    var range, sel, container;
    if (document.selection) {
        range = document.selection.createRange();
        range.collapse(isStart);
        return range.parentElement();
    } else {
        sel = window.getSelection();
        if (sel.getRangeAt) {
            if (sel.rangeCount > 0) {
                range = sel.getRangeAt(0);
            }
        } else {
            // Old WebKit
            range = document.createRange();
            range.setStart(sel.anchorNode, sel.anchorOffset);
            range.setEnd(sel.focusNode, sel.focusOffset);

            // Handle the case when the selection was selected backwards (from the end to the start in the document)
            if (range.collapsed !== sel.isCollapsed) {
                range.setStart(sel.focusNode, sel.focusOffset);
                range.setEnd(sel.anchorNode, sel.anchorOffset);
            }
        }

        if (range) {
            container = range[isStart ? "startContainer" : "endContainer"];

            // Check if the container is a text node and return its parent if so
            return container.nodeType === 3 ? container.parentNode : container;
        }
    }
}

Owl(function(){
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

    window.SCROLLBAR_WIDTH =  (w1 - w2);
});

var Editor = function(editor){
    Editor.textarea = $(editor).find('textarea.plain').k(0);

    $(editor)
    .find(".editor-panel .icon-simple")
    .onMousedown(function(e){
        e.preventDefault();
        e.stopPropagation()
    },'editor')
    .onClick(function() {
        if( $(this).hasClass('icon_insertlink')){
            Editor.showFrameLink(Editor.getSelectText());

        }else if( $(this).hasClass('icon_forecolor')){
            if( this.currentValue ){
                Editor.doCommand('forecolor',this.currentValue);
                this.currentValue = null;
                $(this)
                .find('.editor-pickcolor')
                .css({
                    display: 'none'
                });
            }
        }else if( $(this).hasClass('icon_backcolor')){
            if( this.currentValue ){
                Editor.doCommand('backcolor',this.currentValue);
                this.currentValue = null;
                $(this)
                .find('.editor-pickcolor')
                .css({
                    display: 'none'
                });
            }
        }else if( $(this).hasClass('icon_font')){
            if( this.currentValue ){
                Editor.doCommand('fontname',this.currentValue);
                this.currentValue = null;
                $(this)
                .find('.editor-options')
                .css({
                    display: 'none'
                });
            }
        }else if( $(this).hasClass('icon_fontsize')){
            if( this.currentValue ){
                Editor.doCommand('fontsize',this.currentValue);
                this.currentValue = null;
                $(this)
                .find('.editor-options')
                .css({
                    display: 'none'
                });
            }
        }else if( $(this).hasClass('icon_insertemo')){
            if( this.currentValue ){
                Editor.doCommand('insertemo',this.currentValue);
                this.currentValue = null;
                $(this)
                .find('.editor-options')
                .css({
                    display: 'none'
                });
            }
        }else{
            var command = $(this).attr('editor-command');
            Editor.doCommand(command);
            Editor.update();
        }
    },'editor');

    $(editor)
    .find('.icon_forecolor,.icon_backcolor')
    .onMouseenter(function(){
        $(this)
        .find('.editor-pickcolor')
        .css({
            display: 'inline-block'
        });
    })
    .onMouseleave(function(){
        $(this)
        .find('.editor-pickcolor')
        .css({
            display: 'none'
        });
    });

    $(editor)
    .find('.icon_font,.icon_fontsize,.icon_insertemo')
    .onMouseenter(function(){
        $(this)
        .find('.editor-options')
        .css({
            display: 'inline-block'
        });
    })
    .onMouseleave(function(){
        $(this)
        .find('.editor-options')
        .css({
            display: 'none'
        });
    });

    $(Editor.textarea)
    .addEvent('change keyup',function(){
        document.body.innerHTML = this.value;
    });
};

/**
 * Cross exeCommand
 **/
$.Extend(Editor,{
    update: function(){
        var body = document.body.cloneNode(true);
        $(body).find(".elem-except").remove();
        Editor.textarea.value = body.innerHTML;
    },

    doCommandDOM: function(elem) {
        var uid = (new Date()).getTime(),
        editor = this.editor,
        doc = editor.doc.contentWindow.document;
        doCommandHTML(editor, "<span id='" + uid + "'></span>");
        doc.body.replaceChild(elem, doc.getElementById(uid));
    },

    doCommandHTML:function(value) {
        if (document.queryCommandSupported("inserthtml")) {
            document.body.focus();
            document.execCommand("inserthtml", false, value);
        } else if (document.selection && document.selection.createRange) {
            var range = document.selection.createRange();
            if (range.pasteHTML) {
                range.pasteHTML(value);
            }
        }
    },

    doCommand : function(command, value) {
        value = value || null;
        if (command == "hilitecolor") {
            if (!document.queryCommandSupported(command)) {
                command = "backcolor";
            }
        }

        switch (command) {
            case "insertimage" :
                var img = document.createElement("img");
                img.src = value;
                Editor.doCommandDOM(img);
                document.execCommand('enableObjectResizing', false, false);
                break;
            case "insertemo":
                Editor.doCommandHTML('<img src="'+value+'" class="editor-emo"/>');
                break;
            case "inserthtml" :
                Editor.doCommandHTML(value);
                break;
            case "fontsize" :
                var markClass ='_editor_';
                if( !Editor.rangy )
                    Editor.rangy = rangy.createCssClassApplier(markClass);
                Editor.rangy.toggleSelection();
                $(document.body)
                .find('span.'+markClass)
                .removeAttr('class')
                .css({
                    fontSize: value
                });
                break;
            case "undo" :
                //Stack.undo(editor);
                // no update undo/redo with this command
                return;
            case "redo" :
                //Stack.redo(editor);
                // no update undo/redo with this command
                return;
            default :
                if (document.queryCommandSupported(command)) {
                    document.execCommand(command, false, value);
                } else {
                    alert("Your browser doesn't support " + command);
                }
        }
        Editor.update();
    }
});

/**
 * Helpers
 */

$.Extend(Editor,{
    getSelectText: function(){
        var txt = '';
        if (window.getSelection){
            txt = window.getSelection();
        }else if (document.getSelection){
            txt = document.getSelection();
        }else if (document.selection){
            txt = document.selection.createRange().text;
        }
        return txt;
    }
});

/**
 * Frame plugins
 */
$.Extend(Editor,{
    FRAME: $("<div class='editor-frame'>"
        +"<div class='editor-frame-bar'><a href='javascript:void(0)' class='close'>x</a></div>"
        +"<div class='editor-frame-content'></div>"
        +"</div>")
    .each(function(){
        $(this)
        .find('.close')
        .onClick(function(event){
            Editor.hideMask();
            event.preventDefault();
            event.stopPropagation();
        })
    })
    .k(0),
    MASK: $("<div class='editor-mask'></div>")
    .css({
        top: '0px',
        left: '0px',
        background: '#aaa',
        zIndex: 99999,
        position:'absolute',
        opacity: 0.5
    }).k(0),
    showMask: function(){
        var win = window.parent, doc = win.document;
        var maxWidth = Math.max(doc.documentElement.scrollWidth,doc.body.scrollWidth, $(win).width(),win.screen.width||0);
        var maxHeight = Math.max(doc.documentElement.scrollHeight,doc.body.scrollHeight, $(win).height(),win.screen.height||0);
        $(doc.body)
        .css({
            overflow:'hidden'
        });
        return $(this.MASK)
        .appendTo(doc.body)
        .css({
            width: maxWidth+'px',
            height: maxHeight+'px'
        })
        .k(0);
    },
    hideMask: function(){
        $(window.parent.document.body)
        .css({
            overflow:'visible'
        })
        .empty('.editor-mask,.editor-frame');
        $(window.parent).removeEvent('resize:editor-mask');
    }
});



/**
 * Main functions
 */

$.Extend(Editor,{
    TOOLTIP: $("<div style='position:absolute;border:1px solid #ccc;padding:5px'></div>").k(0),
    showFrame: function( dom ){
        var win = window.parent, doc = win.document, frame = this.FRAME;
        Editor.showMask();
        $(frame)
        .each(function(){
            $(this)
            .find('.editor-frame-content')
            .htm(dom);
        })
        .appendTo(doc.body)
        .css({
            position: 'absolute',
            display: 'inline-block',
            zIndex: 9999999,
            top: (1/4)*Math.max(0,doc.body.offsetHeight - frame.offsetHeight) +'px',
            left: Math.max(0,doc.body.offsetWidth - frame.offsetWidth)/2 +'px'
        });

        $(win).addEvent('resize:editor-mask',function(){
            $(frame)
            .css({
                top: (1/4)*(doc.body.clientHeight - frame.offsetHeight) +'px',
                left: (doc.body.clientWidth - frame.offsetWidth)/2 +'px'
            });
        });
    },
    showFrameLink: function( text, url ){
        var elem;
        if ( text && text.nodeType == 1 ){
            elem = text;
            text = undefined;
            url = $(elem).attr('href');
            if( $(elem).child('*').size() == 0 ){
                text = elem.innerHTML;
            }
        }else{
            text = text === undefined ? '' : text;
            url = url === undefined ? '' : url;
        }

        var html ="<table cellpadding='8'>";
        //display a edit without command
        if( text !== undefined ){
            html+="<tr>"
            +"<td>Tiêu đề:</td>"
            +"<td><input type='text' value='"+text+"' class='editor-insertlink-title'/></td>"
            +"</tr>";
        }

        html+="<tr>"
        +"<td>Đường dẫn:</td>"
        +"<td><input type='text' value='"+url+"' class='editor-insertlink-url'/></td>"
        +"</tr>"
        +"<tr>"
        +"<td></td>"
        +"<td><input type='button' value='Đồng ý' class='editor-insertlink-submit'/></td>"
        +"</tr>"
        +"</table>";
        var form = $(html)
        .each(function(){
            var a = $(this).find('.editor-insertlink-title').k(0);
            var b = $(this).find('.editor-insertlink-url').k(0);
            $(this)
            .find('.editor-insertlink-submit')
            .onClick(function(){
                if( elem ){
                    elem.href = b.value;
                    if(a)
                        elem.innerHTML = a.value;
                }else{
                    var html = "<a href='"+b.value+"'>"+a.value+"</a>";
                    Editor.doCommand('unlink');
                    Editor.doCommand('inserthtml',html);
                }
                Editor.hideMask();
            })
        })
        .k(0);
        Editor.showFrame(form);

    }
});

//add event for document

$('body')
    .addEvent('paste',function(){
        setTimeout(function(){
            Editor.update();
        },1);
    })
    .addEvent('keypress key keyup blur',function(){
        Editor.update();
    })
    .addEvent('click',function(){
        $(".editor-rightmenu").remove()
    })
    .addEvent('contextmenu',function( event){
        var elem = event.target;
        var rightMenu = $("<div class='elem-except editor-rightmenu'></div>");
        var elemLink = elem.tagName == 'A' ? elem : $(elem).parent('a').k(0);

        if( elemLink ){

            $("<div class='editor-rightmenu-item'>Thay đổi liên kết</div>")
            .onMousedown(function(){
                Editor.showFrameLink(elemLink);
                $(rightMenu).remove();
            })
            .appendTo(rightMenu);

            $("<div class='editor-rightmenu-item'>Xóa liên kết</div>")
            .onMousedown(function( event ){
                Editor.doCommand('unlink');
                $(rightMenu).remove();
                event.stopPropagation();
                event.preventDefault();
            })
            .appendTo(rightMenu);

            $(rightMenu)
            .css({
                left: event.pageX + 'px',
                top: event.pageY + 'px'
            })
            .appendTo('body');
            event.stopPropagation();
            event.preventDefault();

        }
    });


