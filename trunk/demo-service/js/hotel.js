/**
 * Comment
 */
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
            alert(1111)
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