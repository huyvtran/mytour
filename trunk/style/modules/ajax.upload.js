function user_form_upload_photo(){
    var form = $('#user_form_photo').k(0),
    progress = $('#user_form_photo_progress').k(0);
    var settings = {
        flash_url: 			baseURL+"/style/modules/upload/swfupload.swf",
        flash9_url: 		baseURL+"/style/modules/upload/swfupload_fp9.swf",
        upload_url: 		baseURL+"/User/Photo?PHPSESSID="+PHPSESSID,
        file_size_limit: 	'2 MB',
        file_types : 		'*.jpeg;*.gif;*.png;*.jpg',
        file_types_description : 'Định dạng ảnh',
        //file_upload_limit : 1,
        file_queue_limit : 0,
        custom_settings : {	},
        debug: false,
        post_params:{
            tcache: (new Date()).getTime()
        },
        file_post_name: 	'photo',

        // Button settings
        button_width:50,
        button_height: 20,
        button_text: '<b>ĐỔI ẢNH</b>',
        button_text_style: 'cursor:pointer;font-size:10px',
        button_cursor: SWFUpload.CURSOR.HAND,
        button_text_left_padding: 0,
        button_text_top_padding: 0,
        button_window_mode: 'transparent',

        // The event handler functions are defined in handlers.js
        swfupload_preload_handler : preLoad,
        swfupload_load_failed_handler : loadFailed,
        file_queued_handler : fileQueued,
        //file_queue_error_handler : fileQueueError,
        file_dialog_complete_handler : fileDialogComplete,
        upload_start_handler : function uploadStart(file) {
            $(form).css({
                visibility:'hidden'
            });
            $('#home-photo').css({
                opacity:0.6
            });
            $(progress)
            .css({
                display:'block'
            })
            .htm("0%");
        },
        upload_progress_handler : function(file, bytesLoaded, bytesTotal){
            var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
            $(progress).htm(percent+"%");
        },
        upload_error_handler : uploadError,
        upload_success_handler : function(file, data){
            $(form).css({
                visibility:'hidden'
            });
            $('#uphoto').css({
                opacity:0.6
            });

            try{
                eval("var result = "+ data);
            }catch(e){
                return $.Alert( data );
            }

            if( 'error_login' in result ){
                $.Alert( result.error_login,function(){
                    open_login();
                });
                $(this.movieElement.parentNode).remove();
            }else if( 'callback' in result ){
                if( !result.close )
                    $(this.movieElement).parent(".lightbox").remove();
                eval(result['callback']);
            }
        },
        upload_complete_handler : function(){
            $(form).css({
                visibility:'hidden'
            });
            $('#uphoto').css({
                opacity:1
            });
            $(progress).css({
                display:'none'
            });
        }
    //queue_complete_handler : queueComplete	// Queue plugin event
    };

    var swfu = new SWFUpload(settings);
    $(form).htm( swfu.getFlashHTML() )
}

(function($){
    /*
		Replacce <div class="x-files"></div> to upload
	*/
    APP_CALLBACK["ajax_upload"] = function(){
        $(".x-files")
        .each(function(){
            var url = this.getAttribute('url');
            var filename = this.getAttribute('filename')||'file';
            var params = {
                "PHPSESSID" : window.PHPSESSID,
                _json: 'yes'
            };

            var settings = {
                flash_url : baseURL+"/style/modules/upload/swfupload.swf",
                flash9_url : baseURL+"/style/modules/upload/swfupload_fp9.swf",
                upload_url: url,
                post_params: params,
                file_size_limit : "600 MB",
                file_types : "*.*",
                file_types_description : "All Files",
                file_upload_limit : 100,
                file_queue_limit : 0,
                custom_settings : {	},
                debug: false,
                file_post_name: filename,

                // Button settings
                button_width: "200",
                button_height: "20",
                button_text: '<b><u>Đính kèm</u></b>',
                button_text_style: 'color:blue;cursor:pointer;font-size:14px',
                button_cursor: SWFUpload.CURSOR.HAND,
                button_text_left_padding: 0,
                button_text_top_padding: 0,
                button_window_mode: 'transparent',

                // The event handler functions are defined in handlers.js
                swfupload_preload_handler : preLoad,
                swfupload_load_failed_handler : loadFailed,
                file_queued_handler : fileQueued,
                file_queue_error_handler : fileQueueError,
                file_dialog_complete_handler : fileDialogComplete,
                upload_start_handler : uploadStart,
                upload_progress_handler : uploadProgress,
                upload_error_handler : uploadError,
                upload_success_handler : function(file, data){
                    try{
                        eval("var result = "+ data);
                    }catch(e){
                        return $.Alert( data );
                    }

                    if( 'error_login' in result ){
                        $.Alert( result.error_login,function(){
                            open_login();
                        });
                        $(this.movieElement.parentNode).remove();
                    }else if( 'alert' in result ){
                        $.Alert( result.alert );
                        $(this.movieElement.parentNode).remove();
                    }else if( 'message' in result ){
                        $.Alert( result.message );
                        $(this.movieElement.parentNode).remove();
                    }else if( 'content' in result ){
                        var obj = this.movieElement.parentNode.parentNode;
                        $( this.movieElement.parentNode ).replace( result.content );
                        app_callback.call(obj);
                    }else if( 'redirect' in result ){
                        app_clean();
                        if( location.hash == result['redirect'] ){
                            app_load();
                        }else{
                            location.hash = result['redirect'];
                        }
                    }else if( 'reload' in result ){
                        app_load();
                    }else if( 'callback' in result ){
                        if( !result.close )
                            $(this.movieElement).parent(".lightbox").remove();
                        $(this.movieElement.parentNode).remove();
                        eval(result['callback']);
                    }
                },
                upload_complete_handler : uploadComplete,
                queue_complete_handler : queueComplete	// Queue plugin event
            };

            var swfu = new SWFUpload(settings);
            this.innerHTML = swfu.getFlashHTML();
            this.settings  = settings;
        });
    };

    /*
		Replacce <div class="x-file"></div> to upload
	*/
    APP_CALLBACK["ajax_upload_single"] = function(){
        $(".x-file")
        .each(function(){

            var settings = {
                flash_url : baseURL+"/style/modules/upload/swfupload.swf",
                flash9_url : baseURL+"/style/modules/upload/swfupload_fp9.swf",
                upload_url: $(this).getAttr('url'),
                post_params: {
                    "PHPSESSID" : window.PHPSESSID,
                    '_json': 'yes'
                },
                file_size_limit: parseInt($(this).getAttr('maxsize'))||0,
                file_types: $(this).getAttr('filetypes')||"*.*",
                file_types_description : "All Files",
                //file_upload_limit: 1,
                file_queue_limit: 0,
                custom_settings: {
                    cancelButtonId: "btnCancel"
                },
                debug: false,
                file_post_name: this.getAttribute('filename')||'file',

                // Button settings
                button_width: "200",
                button_height: "20",
                button_text: '<b><u>'+ $(this).text()+'</u></b>',
                buttonTextStyle: 'color:blue;cursor:pointer;font-size:14px',
                button_text_left_padding: 0,
                button_text_top_padding: 0,
                button_window_mode: 'transparent',

                // The event handler functions are defined in handlers.js
                swfupload_preload_handler : preLoad,
                swfupload_load_failed_handler : loadFailed,
                file_queued_handler : fileQueued,
                file_queue_error_handler : fileQueueError,
                file_dialog_complete_handler : fileDialogComplete,

                upload_start_handler : function uploadStart(file) {
                    var self = this;
                    $(this.movieElement).css({
                        visibility:'hidden'
                    });
                    var div = $(
                        "<table cellpadding='3' border='0'><tr>"
                        +"<td>" + file['name'] + ' ' + get_bytes(file['size']) + "</td>"
                        +"<td><div class='x-progress'><div class='x-progress-bar'></div></div></td>"
                        +"<td><a class='cancel'><u>Hủy</u></a></td></tr></table>").beforeTo(this.movieElement).k(0);

                    $(div)
                    .find(".cancel")
                    .onClick( function(){
                        self.cancelUpload();
                    });

                    this.movieElement.progress = div;
                    return true;
                },

                upload_progress_handler : function uploadProgress(file, bytesLoaded, bytesTotal) {
                    var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
                    $(this.movieElement.progress)
                    .set('title', percent +'%')
                    .find("div.x-progress-bar")
                    .css({
                        width: percent +'%'
                    });
                },
                upload_error_handler: uploadError,
                upload_success_handler: function(file, data){
                    try{
                        eval("var result = "+ data);
                    }catch(e){
                        return $.Alert(data);
                    }

                    $(this.movieElement).css({
                        visibility:'visible'
                    });
                    $(this.movieElement.progress).remove();


                    if( 'error_login' in result ){
                        $.Alert( result.error_login,function(){
                            open_login();
                        } );
                        $(this.movieElement).css({
                            visibility:'visible'
                        });
                        $(this.movieElement.progress).remove();
                    }else if( 'alert' in result ){
                        $.Alert( result.alert );
                        $(this.movieElement).css({
                            visibility:'visible'
                        });
                        $(this.movieElement.progress).remove();
                    }else if( 'content' in result ){

                        settings.button_text = '<b><u>Tải lại</u></b>';
                        $( this.movieElement.parentNode.parentNode )
                        .replace("<div>"
                            + result.content
                            + "<div>"+(new SWFUpload(settings).getFlashHTML())
                            +"</div></div>");

                    }else if( 'redirect' in result ){
                        app_clean();
                        if( location.hash == result['redirect'] ){
                            app_load();
                        }else{
                            location.hash = result['redirect'];
                        }
                    }else if( 'reload' in result ){
                        app_load();
                    }else if( 'callback' in result ){
                        if( !result.close )
                            $(this.movieElement).parent(".lightbox").remove();
                        //$(this.movieElement.parentNode).remove();
                        eval(result['callback']);
                    }
                },
                upload_complete_handler : function(){

                },
                queue_complete_handler : queueComplete	// Queue plugin event
            };

            var swfu = new SWFUpload(settings);
            this.innerHTML =swfu.getFlashHTML();
        });
    };


    /*
		Replacce <div class="x-file"></div> to upload
	*/
    APP_CALLBACK["ajax_upload_single2"] = function(){
        $(".fileupload")
        .each(function(){
            var settings = {
                flash_url : baseURL+"/style/modules/upload/swfupload.swf",
                flash9_url : baseURL+"/style/modules/upload/swfupload_fp9.swf",
                upload_url: $(this).getAttr('url'),
                post_params: {
                    "PHPSESSID" : window.PHPSESSID,
                    '_json': 'yes'
                },
                file_size_limit: parseInt($(this).getAttr('maxsize'))||0,
                file_types: $(this).getAttr('filetypes')||"*.*",
                file_types_description : "All Files",
                //file_upload_limit: 1,
                file_queue_limit: 0,
                custom_settings: {
                    cancelButtonId: "btnCancel"
                },
                debug: false,
                file_post_name: this.getAttribute('filename')||'file',

                // Button settings
                button_width: "200",
                button_height: "20",
                button_text: '<b><u>'+ $(this).text()+'</u></b>',
                buttonTextStyle: 'color:blue;cursor:pointer;font-size:14px',
                button_text_left_padding: 0,
                button_text_top_padding: 0,
                button_window_mode: 'transparent',

                // The event handler functions are defined in handlers.js
                swfupload_preload_handler : preLoad,
                swfupload_load_failed_handler : loadFailed,
                file_queued_handler : fileQueued,
                file_queue_error_handler : fileQueueError,
                file_dialog_complete_handler : fileDialogComplete,

                upload_start_handler : function uploadStart(file) {

                    $(this.movieElement).css({
                        visibility:'hidden'
                    });
                    var div = $(
                        "<table cellpadding='3' border='0'><tr>"
                        +"<td>" + file['name'] + ' ' + get_bytes(file['size']) + "</td>"
                        +"<td><div class='x-progress'><div class='x-progress-bar'></div></div></td>"
                        +"<td>H\u1ee7y</td></tr></table>").beforeTo(this.movieElement).k(0);
                    this.movieElement.progress = div;
                    return true;
                },

                upload_progress_handler : function uploadProgress(file, bytesLoaded, bytesTotal) {
                    var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
                    $(this.movieElement.progress)
                    .set('title', percent +'%')
                    .find("div.x-progress-bar")
                    .css({
                        width: percent +'%'
                    });
                },
                upload_error_handler: uploadError,
                upload_success_handler: function(file, data){
                    try{
                        eval("var result = "+ data);
                    }catch(e){
                        return $.Alert(data);
                    }

                    $(this.movieElement).css({
                        visibility:'visible'
                    });

                    $(this.movieElement.progress).remove();

                    if( 'reupload' in result ){
                        settings.button_text = '<b><u>T\u1ea3i lại</u></b>';
                        $( this.movieElement.parentNode.parentNode )
                        .replace("<div>"
                            + result.content
                            + "<div>"+(new SWFUpload(settings).getFlashHTML())
                            +"</div></div>");
                    }else{

                        updateContent(null, result);
                    }
                },
                upload_complete_handler : function(){

                },
                queue_complete_handler : queueComplete	// Queue plugin event
            };

            var swfu = new SWFUpload(settings);
            this.innerHTML = swfu.getFlashHTML();
        });
    };
})(Owl);