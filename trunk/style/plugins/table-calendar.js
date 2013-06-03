(function ($) {
    /* make date picker */
    var CACHE = null;

    function fill(n) {
        if (n < 10 && !(n.toString().match(/^0/i) && n.toString().length > 1)) {
            return '0' + n;
        }
        return n;
    }

    function create_html(options) {
        if (CACHE) {
        //return CACHE.cloneNode(true);
        }

        var $ = Owl,
        div = null,
        UTC = new Date();

        options = $.Extend({
            link: '',
            format : 'd/m/Y',
            year : UTC.getFullYear(),
            month : UTC.getMonth(),
            date : UTC.getDate(),
            hour : UTC.getHours(),
            minute : UTC.getMinutes(),
            second : UTC.getSeconds(),
            today : {
                year : UTC.getFullYear(),
                month : UTC.getMonth(),
                date : UTC.getDate()
            }
        },
        options || {});

        div = $("<div class='tbc-container'>").k(0);

        $(div)
        .append(
            "<table width='100%' cellpadding='5' class='tbc-cp'><tr>"
            + "<td width='10' align='center'><a lb='cy_pre' title='Năm trước'>&#171;</a></td>"
            + "<td width='10' align='center'><a lb='cm_pre' title='Tháng trước'>&#171;</a></td>"
            + "<td align='center'><a lb='cc_month'></a></td>"
            + "<td width='10' align='center'><a lb='cm_next' title='Tháng tiếp'>&#187;</a></td>"
            + "<td width='10' align='center'><a lb='cy_next' title='Năm tiếp'>&#187;</a></td></tr></table>");

        //table date
        $("<table width='100%' class='picker' cellspacing='0' cellpadding='5' border='1' bordercolor='#EDEFF4'>").append(function () {
            var html = "";
            html += "<tr>";
            for (var j = 2; j < 8; j++) {
                html += "<td class='tbc-day-name'>T" + j + "</td>";
            }

            html += "<td class='tbc-sunday-name'>CN</td>";
            html += "</tr>";
            for (var i = 0; i < 6; i++) {
                html += "<tr>";
                for (var j = 0; j < 7; j++) {
                    html += "<td align='center' class='tbc-date' pos='" + j + "_" + i + "'>&nbsp;</td>";
                }
                html += "</tr>";
            }
            return html;
        }).appendTo(div);

        /* update more */
        var tb = $(div).find('table.picker').k(0);

        $(tb).find("td").slice(7)
        .onMouseover(function () {
            if (this.noaccept_select)
                return;
            $(this).css({
                'background' : '#F6DA9D'
            });
        })
        .onMouseout(function () {
            if (this.noaccept_select)
                return;
            $(this).css({
                'background' : '#fff'
            });
        });

        div.currentYear = options.year;
        div.currentMonth = options.month;
        div.year = options.year;
        div.month = options.month;
        div.date = options.date;
        div.hour = options.hour;
        div.minute = options.minute;
        div.second = options.second;

        $(div).find('a[lb=cc_month]').htm('Tháng ' + (div.currentMonth + 1) + ' / ' + div.currentYear);

        update_calendar(div, tb, div.currentYear, div.currentMonth, options.today, options.link); //next & pre

        $(div)
        .find('a[lb=cm_pre]')
        .each(function () {
            var y = div.currentYear,
            m = div.currentMonth;

            if ( div.currentMonth == 0) {
                m = 11;
                y--;
            } else {
                m--;
            }

            this.href = options.link+'/Month?year=' + fill(y) +'&month='+fill(m+1);
        });


        $(div).find('a[lb=cy_pre]')
        .each(function () {
            var y = div.currentYear -1;
            this.href = options.link+'/Month?year=' + fill(y) +'&month=11';
        });

        $(div)
        .find('a[lb=cm_next]')
        .each(function () {
            var y = div.currentYear,
            m = div.currentMonth;

            if ( div.currentMonth == 11) {
                m = 0;
                y++;
            } else {
                m++;
            }

            this.href = options.link+'/Month?year=' + fill(y) +'&month='+fill(m+1);
        });

        $(div)
        .find('a[lb=cy_next]')
        .each(function () {
            var y = div.currentYear+1;
            this.href = options.link+'/Month?year=' + fill(y) +'&month=01';
        });

        return div;
    }

    CACHE = create_html();

    function update_calendar(div, tb, year, month, today,link ) {
        link=link||'';
        var td = tb.getElementsByTagName('td');
        var d = new Date(year, month, 1, 0, 0, 0, 0);
        var start = d.getDay();
        start = start == 0 ? 6 : start - 1;
        for (var j = 7; j < td.length; j++) {
            var D = new Date(year, month, j - start - 6);
            var rYear = D.getFullYear();
            var rMonth = D.getMonth();
            var rDate = D.getDate();
            td[j].innerHTML = "<a style='color:inherit' href='" + link +"?date="+[fill(rYear),fill(rMonth+1),fill(rDate)].join('-')+"'>"+rDate+"</a>";
            td[j].year = rYear;
            td[j].month = rMonth;
            td[j].date = rDate;

            if (today && today.year == rYear
                && today.month == rMonth
                && today.date == rDate) {
                $(td[j])
                .set("title", "Hôm nay")
                .css('font-weight:bold');
                td[j].today = true;
            } else {
                $(td[j])
                .css('font-weight:normal');
                td[j].today = false;
            }

            if (rMonth != month) {
                $(td[j]).css({
                    color : '#888',
                    background : '#f0f0f0',
                    opacity : 1
                });

                td[j].noaccept_select = true;

                if (j == 42) {
                    $(td[j].parentNode).css("display:none");
                    break;
                }
            } else {
                $(td[j].parentNode).css("display:table-row");
                $(td[j]).css({
                    color : '#555',
                    background : '#fff',
                    opacity : 1
                });
            }
        }
    }
    if (!window.Plugins) {
        window.Plugins = {};
    }

    window.Plugins.TableCalendar = function (id,options) {
        $(create_html(options||{}))
        .css({
            display : 'block'
        })
        .appendTo(id);
    };
})(Owl);
