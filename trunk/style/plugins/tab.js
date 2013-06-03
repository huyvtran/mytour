function do_tab( elem, index ){
    var $ = Owl;
    if( $(elem).hasClass("tab-link-active") ){
        return false;
    }
    $(elem.parentNode).child(".tab-link").removeClass("tab-link-active");
    $(elem).addClass("tab-link-active");

    $(elem.parentNode.parentNode)
    .child(".tabs")
    .child(".tab")
    .css("display:none");

    $(elem.parentNode.parentNode)
    .child(".tabs")
    .find( !isNaN(parseInt(index)) ? ".tab:nth-child("+index+")" : index)
    .css("display:block");
}

function expand_tab( elem, title ){
    elem.c = elem.c !== undefined ? elem.c + 1 : 1;
    $( elem.parentNode )
    .next('.tabs')
    .append(
        $( elem.childNodes[1].nodeValue.replace(/^[\s\t\r\f]+/gi,'') ).setAttr('tab-mark',elem.c).k(0)
        );

    $("<a class='tab-link' style='position:relative;padding-right:20px;display:inline-block' onclick='do_tab(this,\".tab[tab-mark="+elem.c+"]\")'>"
        + title +" ("+elem.c+")"
        +"<span onclick='close_expand_tab(this);return false' title='Đóng tab này' onmouseout='this.style.opacity=0.5' onmouseover='this.style.opacity=1' style='opacity:0.5;font-weight:bold;font-size:10px;display:inline-block;line-height:10px;padding:1px 2px;color:#BD0000;position:absolute;top:-1px;right:2px'>x</span>    </a>")
    .beforeTo( elem )
    .setAttr('tab-mark',elem.c)
}

function close_expand_tab( elem ){
    if( $(elem.parentNode).hasClass('tab-link-active') ){
        $(elem.parentNode)
        .pre(0)
        .each(function(){
            var on = this.getAttribute('onclick');
            if( on.match(/do_tab\(this,(['"]*)(.*)\1\)/i ) ){
                do_tab(this,RegExp.$2);
            }
        });
    }

    var id = elem.parentNode.getAttribute('tab-mark');
    $( elem.parentNode.parentNode )
    .next('.tabs')
    .find('.tab[tab-mark='+ id +']')
    .remove();

    $( elem.parentNode ).remove();
}
