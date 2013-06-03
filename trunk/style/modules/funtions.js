/*
 * All functions use for modules
 * Date: 28/06/2012
 * Modified: 28/06/1012
 * Author: Minhnd
 */

/**
 * Call a ajax from form in progress
 */
function formProgress(elem,step,data){
    var f = arguments.callee;
    if( step === undefined ) step = 0;

    elem.disabled = true;

    if(!data){
        data = $(elem).query();
    }

    $.Ajax( elem.getAttribute('action') ,{
        type: 'POST',
        data:data,
        success: function(js){
            var data;
            try{
                eval("data="+js);
            }catch(e){
                alert(js);
            }

            $(rel).htm('<div class="x-progress"><div class="x-progress-bar" style="width: '+data.progress+'%; "></div></div>');

            if( data.status == 'complete' ){
                obj.disabled = false;
                return;
            }
            f(month,year,obj,i+1)
        },
        error: function(){
            setTimeout(function(){
                f(month,year,obj,i+1);
            },5000);
        }
    })




}


