function addDocumentLabel(obj,id){
    var label = $(obj).pre("input[type=text]").get('value');
    //if( label )
    obj.disabled= true;
    $.Ajax( baseURL + '/Document/User/Label' ,{
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

function showFormEditDocumentLabel( obj, id){
    $('#'+id+'1,#'+id+'2')
    .toggle();
    obj.innerHTML = obj.innerHTML == "hủy bỏ" ? "sửa" : "hủy bỏ";

}

function editDocumentLabel( obj, postid, id ){
    var label = $(obj).pre("input[type=text]").get('value');

    obj.disabled= true;
    $.Ajax( baseURL + '/Document/User/Label' ,{
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

function deleteDocumentLabel( url, id ){
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