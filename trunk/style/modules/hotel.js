
function load_has_extrabed(selector) {
    var form = $(selector).k(0);
    var room_type_id = form.room_type_id.value;
    var url = baseURL+"/Hotel/Order/Loadextrabed?room_type_id="+room_type_id;
    $.Ajax(url,{
        error: function(){
            alert(1111);
        },
        success: function(msg){
           $(form).find('#result_extrabed').htm(msg);
        }
    });
}


function load_bill_each_day(selector) {
    var form = $(selector).k(0);
    var date_start = form.date_start.value;
    var date_end = form.date_end.value;
    var room_type_id = form.room_type_id.value;
    var is_apply_campaign = form.is_apply_campaign.value;
    
    var url = baseURL+"/Hotel/Order/Loadbillcurrent?"+['date_start=',date_start,'&date_end=',date_end,'&room_type_id=',room_type_id,'&is_apply_campaign=',is_apply_campaign].join('');
    
    $.Ajax(url,{
        creat: function(){
            $(form).find('.bill-list').htm('loading...');
           
        },
        error: function(){
            alert(1111);
        },
        success: function(data){
            $(form).find('.bill-list').htm(data);
        }
    });
    
}
function ajax_date_add_bill(order_id,number_date_add,type,is_apply_campaign){
    jQuery.ajax({
        type: "GET",
        url :  baseURL+"/Hotel/Order/Addbill?"+['order_id=',order_id,'&number_date_add=',number_date_add,'&type=',type,'&is_apply_campaign=',is_apply_campaign].join(''),
        success: function(msg){
            jQuery('.bill_date_add').html(msg);
        }
    });
}

/**
 * load google map hotel_viewAction
 */
function load_google_map_view(lat,lng) {
    if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        var center = new GLatLng(lat,lng);
        map.setCenter(center, 16);
        geocoder = new GClientGeocoder();
        var marker = new GMarker(center, {
            draggable: false
        });  
        map.addOverlay(marker);
        jQuery("#lat").html( center.lat().toFixed(5));
        jQuery("#lng").html( center.lng().toFixed(5));
    }
}
function load_google_map(lat,lng) {
    if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        var center = new GLatLng(lat,lng);
        map.setCenter(center, 16);
        geocoder = new GClientGeocoder();
        var marker = new GMarker(center, {
            draggable: true
        });  
        map.addOverlay(marker);
        jQuery("#lat").html('<input type="text" name="lat" value="'+ center.lat().toFixed(5) +'" readonly="true"/>');
        jQuery("#lng").html('<input type="text" name="lng" value="'+ center.lng().toFixed(5) +'" readonly="true"/>');
        //        document.getElementById("lat").innerHTML = center.lat().toFixed(5);
        //        document.getElementById("lng").innerHTML = center.lng().toFixed(5);

        GEvent.addListener(marker, "dragend", function() {
            var point = marker.getPoint();
            map.panTo(point);
            jQuery("#lat").html('<input type="text" name="lat" value="'+ point.lat().toFixed(5) +'" readonly="true"/>');
            jQuery("#lng").html('<input type="text" name="lng" value="'+ point.lng().toFixed(5) +'" readonly="true"/>');
        //        document.getElementById("lat").innerHTML = point.lat().toFixed(5);
        //        document.getElementById("lng").innerHTML = point.lng().toFixed(5);

        });

        GEvent.addListener(map, "moveend", function() {
            map.clearOverlays();
            var center = map.getCenter();
            var marker = new GMarker(center, {
                draggable: true
            });
            map.addOverlay(marker);
            jQuery("#lat").html('<input type="text" name="lat" value="'+ center.lat().toFixed(5) +'" readonly="true"/>');
            jQuery("#lng").html('<input type="text" name="lng" value="'+  center.lng().toFixed(5) +'" readonly="true"/>');
            //            document.getElementById("lat").innerHTML = center.lat().toFixed(5);
            //            document.getElementById("lng").innerHTML = center.lng().toFixed(5);

            GEvent.addListener(marker, "dragend", function() {
                var point =marker.getPoint();
                map.panTo(point);
                jQuery("#lat").html('<input type="text" name="lat" value="'+ point.lat().toFixed(5) +'" readonly="true"/>');
                jQuery("#lng").html('<input type="text" name="lng" value="'+ point.lng().toFixed(5) +'" readonly="true"/>');
            //                    document.getElementById("lat").innerHTML = point.lat().toFixed(5);
            //                    document.getElementById("lng").innerHTML = point.lng().toFixed(5);

            });
 
        });

    }
}

function showAddress(address) {
    var map = new GMap2(document.getElementById("map"));
    map.addControl(new GSmallMapControl());
    map.addControl(new GMapTypeControl());
    if (geocoder) {
        geocoder.getLatLng(
            address,
            function(point) {
                if (!point) {
                    alert(address + " not found");
                } else {
                    document.getElementById("lat").innerHTML = point.lat().toFixed(5);
                    document.getElementById("lng").innerHTML = point.lng().toFixed(5);
                    map.clearOverlays()
                    map.setCenter(point, 14);
                    var marker = new GMarker(point, {
                        draggable: true
                    });  
                    map.addOverlay(marker);

                    GEvent.addListener(marker, "dragend", function() {
                        var pt = marker.getPoint();
                        map.panTo(pt);
                        document.getElementById("lat").innerHTML = pt.lat().toFixed(5);
                        document.getElementById("lng").innerHTML = pt.lng().toFixed(5);
                    });


                    GEvent.addListener(map, "moveend", function() {
                        map.clearOverlays();
                        var center = map.getCenter();
                        var marker = new GMarker(center, {
                            draggable: true
                        });
                        map.addOverlay(marker);
                        document.getElementById("lat").innerHTML = center.lat().toFixed(5);
                        document.getElementById("lng").innerHTML = center.lng().toFixed(5);

                        GEvent.addListener(marker, "dragend", function() {
                            var pt = marker.getPoint();
                            map.panTo(pt);
                            document.getElementById("lat").innerHTML = pt.lat().toFixed(5);
                            document.getElementById("lng").innerHTML = pt.lng().toFixed(5);
                        });
 
                    });

                }
            }
            );
    }
}

function hotel_load_state(obj) {
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

    $.Ajax(baseURL + '/Hotel/Helper/Autolocal?parent_id=' + obj.value, {
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

function hotel_load_district(obj) {
    var con = obj.parentNode;

    var b = $(con)
    .find("select[dt=district]")
    .empty("option")
    .k(0);

    b.disabled = true;

    $.Ajax(baseURL + '/Hotel/Helper/Autolocal?parent_id=' + obj.value, {
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

function pieChart(container, title, subtitle, passed_datas){
    var chart;
    jQuery(document).ready(function() {            
        chart = new Highcharts.Chart({
            chart: {
                renderTo: container,
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '<p style="font-size: 14px;">'+ title +' </p>'
            },
            subtitle: {
                text: subtitle
            },            
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage}%</b>',
                percentageDecimals: 2
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Tỷ lệ',
                data: passed_datas
            }]
        });
    });
}

function columnChart(container, title, subtitle, cats, data, rotate, unit ){
    var chart;
    jQuery(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: container,
                type: 'column',
                margin: [ 50, 50, 100, 80]
            },
            title: {
                text: title
            },
            subtitle: {
                text: subtitle
            },
            xAxis: {
                categories: cats,
                labels: {
                    rotation: rotate,
                    align: 'center',
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: unit
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                formatter: function() {
                    return '<b>Ngày '+ this.x +'</b><br/>'+
                    'có : '+ Highcharts.numberFormat(this.y,0) +
                    ' đơn đặt phòng';
                }
            },
            series: [{
                name: 'Population',
                data: data,
                dataLabels: {
                    enabled: true,
                    rotation: 0,
                    color: '#FFFFFF',
                    align: 'center',
                    x: 4,
                    y: 20,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });
    });
}

function moreColumn(container){
    var chart;
    jQuery(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: container,
                type: 'column'
            },
            title: {
                text: 'Monthly Average Rainfall'
            },
            subtitle: {
                text: 'Source: WorldClimate.com'
            },
            xAxis: {
                categories: [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec'
                ]
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Rainfall (mm)'
                }
            },
            legend: {
                layout: 'vertical',
                backgroundColor: '#FFFFFF',
                align: 'left',
                verticalAlign: 'top',
                x: 100,
                y: 70,
                floating: true,
                shadow: true
            },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.x +': '+ this.y +' mm';
                }
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
                series: [{
                name: 'Tokyo',
                data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]
    
            }, {
                name: 'New York',
                data: [83.6, 78.8, 98.5, 93.4, 0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3]
    
            }, {
                name: 'London',
                data: [48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2]
    
            }, {
                name: 'Berlin',
                data: [42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]
    
            }]
        });
    });    
}

function lineChart(container, title, subtitle, cats, data, name){

    var chart;
    jQuery(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: container,
                type: 'line'
            },
            title: {
                text: title
            },
            subtitle: {
                text: subtitle
            },
            xAxis: {
                categories: cats
            },
            yAxis: {
                title: {
                    text: 'Số lượng đơn đặt phòng'
                }
            },
            tooltip: {
                enabled: false,
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +'°C';
                }
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: false
                }
            },
            series: [{
                name: name,
                data: data
            }]
        });
    });
    
 
}