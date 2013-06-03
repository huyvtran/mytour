var STATUS_DIV_CONTAINER = null

function status_create_container() {
    if (STATUS_DIV_CONTAINER) {
        return STATUS_DIV_CONTAINER.cloneNode(true);
    }

    var wrapDiv = $("<div style='display:inline-block'>").k(0);

    $("<div></div>").append("<div class='status-flag'></div>").append(function() {
        var html = "";
        for (var i = 0; i <= 10; i++) {
            html += "<a class='status-stone'>" + i * 10 + "</a>"
        }
        return "<div class='status-picker-container'>" + html + "</div>";
    }).appendTo(wrapDiv);
    return wrapDiv;
}

STATUS_DIV_CONTAINER = status_create_container();

function status_picker(obj, options) {
    obj.setAttribute('autocomplete', 'off', 2);
    var $ = Owl,
    div = null,
    UTC = new Date();
    obj.is_open = true;

    options = $.Extend({}, options || {});

    if (!obj.status_div_picker) {
        var wrapDiv = status_create_container();
        $(wrapDiv).append("<div style='clear:both'></div>").onClick(function(event) {
            event.preventDefault();
            event.stopPropagation();
            obj.is_open = true;
            return false;
        }).find(".status-stone").onClick(function() {
            obj.value = $(this).text();
            $(wrapDiv).css({
                display: 'none'
            });
        });
        //body
        $(document).onClick(function() {
            if (!obj.is_open) {
                $(wrapDiv).css({
                    display: 'none'
                });
            }
            obj.is_open = false;
        });

        wrapDiv.obj = obj;
        obj.status_div_picker = wrapDiv;

        $(obj.status_div_picker).afterTo(obj).css({
            display: 'block',
            position: 'absolute',
            'z-index': 2000
        });

        $("<span style='display:inline-block'></span>").beforeTo(obj).append(obj);
        $(obj).after("<br/>");
        $(obj.status_div_picker).afterTo(obj).css({
            position: 'absolute',
            'z-index': 2000
        });
    }
    $(obj.status_div_picker).show();
}







/* time picker*/
var TIME_DIV_CONTAINER = null

function time_create_container() {
    if (TIME_DIV_CONTAINER) {
        return TIME_DIV_CONTAINER.cloneNode(true);
    }

    var wrapDiv = $("<div class='time-picker-container'>").k(0);

    $("<div></div>").append(function() {
        var html = "";
        for (var i = 0; i < 24; i += 3) {
            var a = i,
            b = i + 1,
            c = i + 2;
            a = a < 10 ? '0' + a : a;
            b = b < 10 ? '0' + b : b;
            c = c < 10 ? '0' + c : c;

            html += "<div>" + "<a class='time-stone'>" + a + ":00</a>" + "<a class='time-stone'>" + a + ":30</a>" + "<a class='time-stone'>" + b + ":00</a>" + "<a class='time-stone'>" + b + ":30</a>" + "<a class='time-stone'>" + c + ":00</a>" + "<a class='time-stone'>" + c + ":30</a>" + "</div>";
        }
        return html;
    }).appendTo(wrapDiv);
    return wrapDiv;
}

TIME_DIV_CONTAINER = time_create_container();

function time_picker(obj, options) {
    obj.setAttribute('autocomplete', 'off', 2);
    var $ = Owl;
    obj.is_open = true;

    options = $.Extend({}, options || {});

    if (!obj.time_div_picker) {
        var wrapDiv = time_create_container();
        $(wrapDiv).append("<div style='clear:both'></div>").onClick(function(event) {
            event.preventDefault();
            event.stopPropagation();
            obj.is_open = true;
            return false;
        }).find(".time-stone").onClick(function() {
            obj.value = $(this).text();

            //update callback
            var id = obj.getAttribute('parent-date');
            if (id !== null) {
                var t1 = $('#time_' + id).get('value');
                var t2 = $('#date_' + id).get('value');
                $('#' + id).set('value', t2 + ' ' + t1 + ':00');
            }

            $(wrapDiv).css({
                display: 'none'
            });
        });
        //body
        $(document).onClick(function() {
            if (!obj.is_open) {
                $(wrapDiv).css({
                    display: 'none'
                });
            }
            obj.is_open = false;
        });

        wrapDiv.obj = obj;
        obj.time_div_picker = wrapDiv;

        $("<div style='display:inline-block'></div>").beforeTo(obj).append(obj);
        $(obj).after("<br/>");
        $(obj.time_div_picker).afterTo(obj).css({
            position: 'absolute',
            'z-index': 2000
        });
    }

    $(obj.time_div_picker).show()
}





/* make date picker */
var DATE_DIV_CONTAINER = null

function date_create_container() {
    if (DATE_DIV_CONTAINER) {
        return DATE_DIV_CONTAINER.cloneNode(true);
    }
    var wrapDiv = $("<div class='date-picker-container'>").k(0);

    $(wrapDiv).append("<table width='100%' cellpadding='5' class='date-picker-cp'><tr>" + "<td width='10' align='center'><a lb='cy_pre' title='Năm trước'>&#171;</a></td>" + "<td width='10' align='center'><a lb='cm_pre' title='Tháng trước'>&#171;</a></td>" + "<td align='center' nowrap='nowrap'><a lb='cc_month'></a></td>" + "<td width='10' align='center'><a lb='cm_next' title='Tháng tiếp'>&#187;</a></td>" + "<td width='10' align='center'><a lb='cy_next' title='Năm tiếp'>&#187;</a></td></tr></table>");

    //table date
    $("<table width='100%' class='picker' cellspacing='0' cellpadding='5' border='1' bordercolor='#EDEFF4'>").append(function() {
        var html = "";
        html += "<tr>";
        for (var j = 2; j < 8; j++) {
            html += "<td class='date-picker-day-name'>T" + j + "</td>";
        }

        html += "<td class='date-picker-sunday-name'>CN</td>";
        html += "</tr>";
        for (var i = 0; i < 6; i++) {
            html += "<tr>";
            for (var j = 0; j < 7; j++) {
                html += "<td align='center' class='date-picker-date' pos='" + j + "_" + i + "'>&nbsp;</td>";
            }
            html += "</tr>";
        }
        return html;
    }).appendTo(wrapDiv);
    return wrapDiv;
}

DATE_DIV_CONTAINER = date_create_container();

function date_picker(obj, options) {

    obj.setAttribute('autocomplete', 'off', 2);
    var $ = Owl,
    div = null,
    UTC = new Date();
    obj.is_open = true;

    options = $.Extend({
        format: 'd/m/Y',
        year: UTC.getFullYear(),
        month: UTC.getMonth(),
        date: UTC.getDate(),
        hour: UTC.getHours(),
        minute: UTC.getMinutes(),
        second: UTC.getSeconds(),
        today: {
            year: UTC.getFullYear(),
            month: UTC.getMonth(),
            date: UTC.getDate()
        }
    }, options || {});

    /*fillup date*/
    function fill(n) {
        if (n < 10 && !(n.toString().match(/^0/i) && n.toString().length > 1)) {
            return '0' + n;
        }
        return n;
    }

    if (!obj.date_div_picker) {
        var wrapDiv = date_create_container();
        $(wrapDiv).append("<div style='clear:both'></div>").onClick(function(event) {
            event.preventDefault();
            event.stopPropagation();
            obj.is_open = true;
            return false;
        });
        //body
        $(document).onClick(function() {
            if (!obj.is_open) {
                $(wrapDiv).css({
                    display: 'none'
                });
            }
            obj.is_open = false;
        });

        wrapDiv.obj = obj;

        obj.date_div_picker = wrapDiv;

        div = wrapDiv;

        /* update more */
        var tb = $(div).find('table.picker').k(0);
        if( options.format.toString().match(/d/i) == null ){
            $(tb).css({
                display:'none'
            });
            $(div).find('.date-picker-cp').addClass('date-picker-month')
        }

        $(tb).find("td").slice(7).onMouseover(function() {
            if (this.noaccept_select) return;
            $(this).css({
                'background': '#F6DA9D'
            });
        }).onMouseout(function() {
            if (this.noaccept_select) return;
            $(this).css({
                'background': '#fff'
            });
        });
        var m;
        if ((m = obj.value.match(/^(\d+)\/0?(\d+)\/(\d+)$/i))) {
            div.currentYear = m[3];
            div.currentMonth = parseInt(m[2]) - 1;
        } else {
            div.currentYear = options.year;
            div.currentMonth = options.month;
        }
        div.year = options.year;
        div.month = options.month;
        div.date = options.date;
        div.hour = options.hour;
        div.minute = options.minute;
        div.second = options.second;

        var html = options.format == "Y" ? div.currentYear : ('Th.' + (div.currentMonth + 1) + ' / ' + div.currentYear) ;
        $(div).find('a[lb=cc_month]')
        .htm(html)
        .onClick(function() {
            div.year = div.currentYear;
            div.month = div.currentMonth;
            div.date = div.currentDate;
            div.update();
            $(div).css({
                display: 'none'
            });
        });

        update_calendar(div, tb, div.currentYear, div.currentMonth, options.today); //next & pre
        $(div).find('a[lb=cm_pre]').onClick(function() {
            if (div.currentMonth == 0) {
                div.currentMonth = 11;
                div.currentYear--;
            } else {
                div.currentMonth--;
            }
            var html = options.format == "Y" ? div.currentYear : ('Th.' + (div.currentMonth + 1) + ' / ' + div.currentYear) ;

            $(div).find('a[lb=cc_month]').htm(html);
            update_calendar(div, tb, div.currentYear, div.currentMonth, options.today);
        });

        $(div).find('a[lb=cy_pre]').onClick(function() {
            div.currentMonth = 11;
            div.currentYear--;
            var html = options.format == "Y" ? div.currentYear : ('Th.' + (div.currentMonth + 1) + ' / ' + div.currentYear) ;

            $(div).find('a[lb=cc_month]').htm(html);
            update_calendar(div, tb, div.currentYear, div.currentMonth, options.today);
        });

        $(div).find('a[lb=cm_next]').onClick(function() {
            if (div.currentMonth == 11) {
                div.currentMonth = 0;
                div.currentYear++;
            } else {
                div.currentMonth++;
            }
            var html = options.format == "Y" ? div.currentYear : ('Th.' + (div.currentMonth + 1) + ' / ' + div.currentYear) ;

            $(div).find('a[lb=cc_month]').htm(html);
            update_calendar(div, tb, div.currentYear, div.currentMonth, options.today);
        });

        $(div).find('a[lb=cy_next]').onClick(function() {
            div.currentMonth = 0;
            div.currentYear++;
            var html = options.format == "Y" ? div.currentYear : ('Th.' + (div.currentMonth + 1) + ' / ' + div.currentYear) ;
            $(div).find('a[lb=cc_month]').htm(html);
            update_calendar(div, tb, div.currentYear, div.currentMonth, options.today);
        });


        if( options.format == "Y" ){
            $(div).find('a[lb=cm_next],a[lb=cm_pre]')
            .css({
                display: 'none'
            });
        }


        div.update = function() {
            var stam = {
                Y: fill(div.year),
                d: fill(div.date),
                m: fill(div.month + 1),
                H: fill(div.hour),
                i: fill(div.minute),
                s: fill(div.second)
            };
            var result = options.format;
            for (var x in stam) {
                result = result.replace(x, stam[x]);
            }

            div.obj.value = result;

            //update callback
            var id = div.obj.getAttribute('parent-date');
            if (id !== null) {
                var t1 = $('#time_' + id).get('value');
                var t2 = $('#date_' + id).get('value');
                $('#' + id).set('value', t2 + ' ' + t1 + ':00');
            }

            div.obj.focus();
            if (options.onChange) {
                options.onChange.call(div.obj);
            }
        };

        $("<div style='display:inline-block;z-index:100;overflow:visible'></div>").beforeTo(obj).append(obj);
        $(obj).after("<br/>");
        $(obj.date_div_picker).afterTo(obj).css({
            position: 'absolute',
            'z-index': 2000
        });
    }
    $(obj.date_div_picker).each(function() {
        var div = this.parentNode;
        while (div != document.body) {
            var pos = $(div).css('position');
            if (pos == "relative" || pos == "absolute") {
                break;
            } else {
                div = div.parentNode;
            }
        }

        var left = $(obj).left() - $(div).left() - $(this).width() + $(obj).width();
        var top = $(obj).top() - $(div).top() + $(obj).height() + 1;
        $(this).css({
            left: left + 'px',
            top: top + 'px'
        }).fadeIn(200);
    });

}

function update_calendar(div, tb, year, month, today) {
    var td = tb.getElementsByTagName('td');
    var d = new Date(year, month, 1, 0, 0, 0, 0);
    var start = d.getDay();
    start = start == 0 ? 6 : start - 1;
    for (var j = 7; j < td.length; j++) {
        var D = new Date(year, month, j - start - 6);
        var rYear = D.getFullYear();
        var rMonth = D.getMonth();
        var rDate = D.getDate();
        td[j].innerHTML = rDate;
        td[j].year = rYear;
        td[j].month = rMonth;
        td[j].date = rDate;
        if (!td[j].initClick) {
            $(td[j]).onClick(function() {
                div.year = this.year;
                div.month = this.month;
                div.date = this.date;
                div.update();
                $(div).css({
                    display: 'none'
                });
            });

            td[j].initClick = true;

        }


        if (today && today.year == rYear && today.month == rMonth && today.date == rDate) {
            $(td[j]).set("title", "Hôm nay").css('font-weight:bold');
            td[j].today = true;
        } else {
            $(td[j]).css('font-weight:normal');
            td[j].today = false;
        }


        if (rMonth != month) {
            $(td[j]).css({
                color: '#888',
                background: '#f0f0f0',
                opacity: 1
            });

            td[j].noaccept_select = true;

            if (j == 42) {
                $(td[j].parentNode).css("display:none");
                break;
            }
        } else {
            $(td[j].parentNode).css("display:table-row");
            $(td[j]).css({
                color: '#555',
                background: '#fff',
                opacity: 1
            });
        }
    }
}