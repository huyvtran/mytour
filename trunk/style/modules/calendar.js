function calendar_check_day( obj ){
    $("#cday input[type=checkbox]").set('disabled', obj.value == 'day' ? false :true );
    $("input[name=repeat_date_end],input[name=no_date_end]").set('disabled', obj.value != 'none' ? false :true );
}

function calendar_fill_date_end(obj){
    var value = obj.value;
    if( value == '' ) return;
    $("input[name=repeat_date_end]").each(function(){
        if( this.value =='' && !this._focused){
            this.value = value;
        }
    });
}

var CALENDAR_ROW_HIDEN = true;
function calendar_expand_day(obj){
    obj.innerHTML = CALENDAR_ROW_HIDEN ? '[thu gọn]' : '[mở rộng]';
    $("tr.cday-hide").css({
        display: CALENDAR_ROW_HIDEN ? 'table-row' : 'none'
    });
    CALENDAR_ROW_HIDEN = !CALENDAR_ROW_HIDEN;
}