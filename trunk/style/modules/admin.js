(function( $ ){
    window.admin_reload_logo = function( data ){
        $(".logo").each(function(){
            this.src = this.src.split('?')[0]+'?'+ (new Date()).getTime();
        });
    };
})(Owl);

var optermizeLinks = ['User','Notice','Optermize'];
var AJAXOPTERMIZE = null;

function recruisive_optermize( elem, i ){

    if( i >= optermizeLinks.length ){
        return;
    }

    var url = baseURL +'/Admin/Optermize/' + optermizeLinks[i];

    AJAXOPTERMIZE = $.Ajax( url, {
        success: function(){
            $("#optermize_status")
            .css({
                width: Math.round((i/(optermizeLinks.length-1))*100) +'%'
            });
            recruisive_optermize( elem, ++i );
        }
    });
}

function admin_optermize( elem ){
    if( AJAXOPTERMIZE )
        AJAXOPTERMIZE.stop();

    $("#optermize_status")
    .css({
        width:'0%'
    });

    //elem.disabled = true;
    recruisive_optermize( elem, 0 )
}