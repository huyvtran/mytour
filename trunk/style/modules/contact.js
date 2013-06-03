function addContactLabel(obj,id){
    var label = $(obj).pre("input[type=text]").get('value');
    //if( label )
    obj.disabled= true;
    $.Ajax( baseURL + '/Contact/Index/Label' ,{
        type: 'POST',
        data: {
            label: label,
            act:	'add'
        },
        error: function(){
            obj.disabled= false;
        },
        success: function( data ){
            $('#'+id)
            .replace(data);
            obj.disabled= false;
            app_load();
        }
    });
}

function showFormEditContactLabel( obj, id){
    $('#'+id+'1,#'+id+'2')
    .toggle();
    obj.innerHTML = obj.innerHTML == "hủy bỏ" ? "sửa" : "hủy bỏ";

}

function editContactLabel( obj, postid, id ){
    var label = $(obj).pre("input[type=text]").get('value');

    obj.disabled= true;
    $.Ajax( baseURL + '/Contact/Index/Label' ,{
        type: 'POST',
        data: {
            label: label,
            act:	'edit',
            ID:	postid
        },
        error: function(){
            obj.disabled= false;
        },
        success: function( data ){
            $('#'+id)
            .replace(data);
            obj.disabled= false;
            app_load();
        }
    });
}

function deleteContactLabel( url, id ){
    $.Ajax( url ,{
        type: 'POST',
        data: {
            act: 'delete'
        },
        success: function( data ){
            $('#'+id)
            .replace(data);
            app_load();
        }
    });
}