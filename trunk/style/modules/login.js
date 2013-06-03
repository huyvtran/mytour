window.load_html = function( selector, options ){
    options = options||{};
    $.createLightBox($.Extend({
        title: options.title||'Trình duyệt nhanh: ',
        callback: function(){
            if( $(selector).size() > 0 ){
                $(this)
                .htm( $(selector).k(0).innerHTML );
            }
        }
    },options||{} ));
};


$(".bb")
    .onClick(function send_email(){
        $.Ajax(baseURL+'/Login/Getpass',{
            type: 'POST',
            data:{
                _json: 'yes',
                email: $('#email').value()
            },
            create: function(){
                $('.bb')
                .set('value','Xin chờ')
                .set('disabled',true);
            },
            success: function( txt ){
                try{
                    eval("var data="+txt);
                }catch(e){
                    alert('Có lỗi xảy ra');
                    $('.bb')
                    .set('value','Thử lại')
                    .set('disabled',false);
                }

                $('.bb')
                .set('value','Xin chờ')
                .set('disabled',true);
            }
        });
    });
