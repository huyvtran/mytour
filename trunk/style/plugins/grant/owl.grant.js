/*



*/
(function($){
    var pos,pos1;
    $(document)
    .onMouseup(function(){
        if(pos) delete pos.pos;
        $(pos).removeClass("grant-chart-hand");
        pos = null;
    })
    .onMousemove(function(event){
        if(pos){
            pos.parentNode.scrollLeft = pos.pos.x - event.pageX;
            pos.parentNode.scrollTop  = pos.pos.y - event.pageY;
        }
    })
    .onKeydown(function( event ){
        if(pos1){
            var i = 0;
            if( event.KEY_LEFT ) i = -1;
            if( event.KEY_RIGHT ) i = 1;
            if(i){
                pos1.parentNode.scrollLeft += i*25;
                event.preventDefault();
                event.stopPropagation();
            }

            i= 0;
            if( event.KEY_UP ) i = -1;
            if( event.KEY_DOWN ) i = 1;
            if(i){
                pos1.parentNode.scrollTop += i*25;
                event.preventDefault();
                event.stopPropagation();
            }
        }
    });

    //@get max of array
    function getMax(a,p){
        var b=0;
        for(var i=0; i < a.length; i++){
            b = !b ? getTime(a[i][p]) : Math.max( b,getTime(a[i][p]) );
        }
        return b
    }

    //@get min of array
    function getMin(a,p){
        var b;
        for(var i=0; i < a.length; i++){
            b = !b ? getTime(a[i][p]) : Math.min(b,getTime(a[i][p]));
        }

        return b
    }

    //@convert
    function getTime(s){
        s = s.split("-");

        var  m = parseInt(s[1].replace(/^0(\d)$/i,"$1") )-1;
        m = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"][m];
        var n = parseInt(s[2].replace(/^0(\d)$/i,"$1") );
        return Date.parse( m + " " + n +", "+s[0]);
    }

    $.makeGrantChart = function( options ){
        var elem = $(options.container).k(0),store={};

        var fw = elem.offsetWidth
        - parseInt($(elem).css("padding-left"))||0
        - parseInt($(elem).css("padding-right"))||0
        - parseInt($(elem).css("border-width-left"))||0
        - parseInt($(elem).css("border-width-right"))||0;

        $(elem).css({
            width: + fw + "px"
            });

        $(elem)
        .addClass('grant-chart');

        var container = $("<div class='gc-container'>").appendTo(elem);
        container
        .css({
            width: elem.offsetWidth+"px",
            height: elem.offsetHeight+"px"
        });

        var tasks = options.tasks,s;

        var start = getMin(tasks,'date_start');
        var end = getMax(tasks,'date_end');

        var dateStart = new Date(start);

        var s = dateStart.getDay();

        s = (s == 0) ? 5 : s-1;

        var time = start - s*24*3600*1000;

        var divModel = $("<div class='gc-model'></div>");

        for( var i=2;i < 9;i++ ){
            var c = i == 8 ? 'C' : i;
            var hd = i==8 ? ' gc-holiday' :''
            $("<div class='gc-columns"+hd+"'><div class='gc-day'>"+c+"</div><div class='gc-column'></div></div>")
            .appendTo(divModel.k(0));
        }

        var nWeek = Math.max(Math.round((end-start)/(7*24*3600*1000)) + 1,8), unitWidth=0,orgTop=0;

        for(var i=-1; i < nWeek; i++){
            var d = new Date( time + i*7*24*3600*1000 );
            var title = [d.getDate().toString().replace(/^(\d)$/i,'0$1'),
            (d.getMonth()+1).toString().replace(/^(\d)$/i,'0$1'),
            d.getFullYear()].join(" - ");

            var div = divModel.k(0).cloneNode(true);

            $(div)
            .find(".gc-columns")
            .each(function( j ){
                var k = new Date( d.getTime()+(j*24*3600*1000) );
                k = [k.getFullYear(),
                (k.getMonth()+1).toString().replace(/^(\d)$/i,'0$1'),
                k.getDate().toString().replace(/^(\d)$/i,'0$1')].join("-");
                store[k] = this
                this.title = k
            });

            $(div)
            .first("<div class='gc-quarter'>" + title +"</div>");
            container.append(div);
            unitWidth += div.offsetWidth;
            orgTop = Math.max(orgTop,$(div).find('.gc-column').ofssetTop);
        }

        container.css("width:"+unitWidth+"px");


        //@display task
        var oW = container.find(".gc-column").width();

        for(var i = 0 ; i < tasks.length; i++ ){
            var a = tasks[i];
            var id1 = store[a.date_start];
            var id2 = store[a.date_end];

            var left  = $(id1).left() - container.parent(0).left();
            //alert(left)
            var width = $(id2).left() - $(id1).left();

            //var status = (getTime(a.date_end) < options.currentTime) && a.status < 100
            //? " gc-over-deadline":"";
            //alert(left+" "+width)
            $("<div class='gc-item'><div class='gc-progress'></div></div>")
            .appendTo(container.k(0))
            .css({
                top: 70 + i*30 + "px",
                left: left+"px",
                width: width+"px"
            })
            .onClick(function(){
                if( a.url ) location.hash = a.url;
            })
            .set('title', a.title +" " + a.status +"% \n" + a.date_start )
            .append(
                $("<span class='gc-title'>"+a.title+"</span>").k(0)
                )
            .each(function(){
                if(a.users){
                    $("<span class='gc-user'></span>")
                    .set('title',a.users)
                    .appendTo(this)
                }
            })
            .find(".gc-progress")
            .css({
                width: a.status+"%"
            })

        }

        container
        .onMousedown(function( event ){
            $(this).addClass("grant-chart-hand");
            this.pos = {
                x: this.parentNode.scrollLeft + event.pageX,
                y: this.parentNode.scrollTop + event.pageY
            };
            pos = this;
            event.preventDefault();
            event.stopPropagation();
        })
        .onMouseover(function(event){
            pos1 = this;
        })
        .onMouseout(function(){
            pos1 = null;
        })
        .find('.gc-column')
        .css({
            height: tasks.length*30+100+"px"
        })
    };
})(Owl);