if (!App) {
    var App = {
        Lang: {},
        addLang: function(s,v){
            App.Lang[s] = v;
        },
        trans : function ( s ) {
            return ( s in App.Lang ) ? App.Lang[s] : s
        }
    };
}

/*
Auto expand a textarea
 */
function auto_text(max_row, event, elem) {
    if (!event) {
        event = window.event;
    }
    var $ = Owl;
    var n = elem.value.split(/\n/i).length;
    if (n < max_row && elem.scrollTop > 0) {
        elem.rows = n + 1;
    } else {
        elem.style.overflowY = 'auto';
    }
}

function add_emo(textarea, emo) {
    var p = $(textarea).getPointer();
    var str = $(textarea).get('value');
    $(textarea).set('value', str.substr(0, p.start) + ' ' + emo + ' ' + str.substr(p.end));
}

function show_about() {
    $.createLightBox({
        title : "Giới thiệu",
        id : 'frameabout',
        css_content : {
            width : '450px',
            padding : '0px',
            textAlign : 'justify'
        },
        callback : function () {
            $(this).htm($("#about").htm());
            $("#frameabout")
            .css({
                border : '1px solid #637AAE',
                borderRadius : '3px'
            })
        }
    });
}

function load_faq(url) {
    $.Ajax(url, {
        cache : false,
        error : function () {},
        success : function (data) {
            $('#faq-content').htm(data);
        }
    });

}

var FAQ = null;
function search_faq() {
    if (FAQ)
        FAQ.stop();
    FAQ = $.Ajax(baseURL + "/User/Faq/Search", {
        cache : false,
        data : {
            faq_s : $('#faq_s').get('value')
        },
        error : function () {},
        success : function (data) {

            $('#faq-content').htm(data);
        }
    });
}

function load_state(obj) {
    var con = obj.parentNode;

    var a = $(con)
    .find("select[dt=state]")
    .empty("option")
    .k(0);
    var b = $(con)
    .find("select[dt=district]")
    .empty("option")
    .k(0);

    a.disabled = true;
    b.disabled = true;

    $.Ajax(baseURL + '/Crm/Helper/Autolocal?parent_id=' + obj.value, {
        success : function (data) {
            try {
                eval("var c = " + data);
            } catch (e) {
                alert(data)
                return false;
            }

            $(a).append("<option>Tỉnh thành</option>");
            $(b).append("<option>Quận huyện</option>");
            for (var i = 0; i < c.length; i++) {
                $(a).
                append("<option value='" + c[i].ID + "'>" + c[i].title + "</option>");
            }

            a.disabled = false;
            b.disabled = false;
        }
    });
}

function load_district(obj) {
    var con = obj.parentNode;

    var b = $(con)
    .find("select[dt=district]")
    .empty("option")
    .k(0);

    b.disabled = true;

    $.Ajax(baseURL + '/Crm/Helper/Autolocal?parent_id=' + obj.value, {
        success : function (data) {
            try {
                eval("var c = " + data);
            } catch (e) {
                return false;
            }

            $(b).append("<option>Quận huyện</option>");
            for (var i = 0; i < c.length; i++) {
                $(b).
                append("<option value='" + c[i].ID + "'>" + c[i].title + "</option>");
            }

            b.disabled = false;
        }
    });
}

/* check_list in table */
function check_list(selector, obj) {
    if (obj.tagName != "INPUT") {
        obj.checked = !obj.checked;
        if (obj.checked) {
            $(obj).addClass("x-check-current");
        } else {
            $(obj).removeClass("x-check-current");
        }
    }

    var $tb = $(obj)
    .parent("table.x-list")
    .p(0);

    $tb
    .find(selector)
    .find("input[type=checkbox]")
    .each(function () {
        if (this != obj) {
            this.checked = obj.checked
        }
    });

    if ($tb.find("input:not(:checked)").size() > 0) {
        $tb
        .find(".x-check input[type=checkbox]")
        .set('checked', false);
    } else {
        $tb
        .find(".x-check input[type=checkbox]")
        .set('checked', true);
    }
}