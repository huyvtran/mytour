/*
	@Manager labels
	Update 2/4/2012
	Create by ducminh_ajax
*/
(function($){
    function render_data(){
        var inpts = $(this)
        .find("input[type=checkbox]"),
        box  = this,
        list = this.elemList;

        inpts
        .each(function(){

            var e = this;
            $(list)
            .find("input[value="+ e.value +"]")
            .each(function(){
                simulatedClick(e)
            })
        })
        .onClick(function(){
            $(list).htm('');
            $(box)
            .find("input:checked")
            .each(function(){
                var label = this.getAttribute("dt-name"),
                id = this.getAttribute("dt-id");
                $("<span class='tag1'><span class='tag-title'>"
                    +label
                    +"</span><span class='tag-remove'>x</span>"
                    +"<input type='hidden' name='labels[]' value='"+id+"'/>"
                    +"</span>")
                .appendTo(list)
                .find(".tag-remove")
                .set('elemBox',box)
                .onClick(function(){
                    $(this.parentNode).remove();
                    $(this.elemBox)
                    .find("input[value="+ id +"]")
                    .each(function(){
                        simulatedClick(this)
                    })
                })
            });
        },'_check_label_');

        $(list)
        .find(".tag1 input")
        .each(function(){
            var id = this.value;

            if( $("input[dt-id="+id+"]").size() == 0 ){
                $(this).parent(".tag1").remove()
            }else{
                var label = $("input[dt-id="+id+"]").getAttr("dt-name")
                $(this).parent(0).find(".tag-title").htm(label)
            }
        })
    }


    window.delete_label = function( url, elem ){
        $.Ajax( url ,{
            type: 'POST',
            create: 	ajax_show,
            complete:	ajax_hide,
            error:		ajax_error,
            success: function( txt ){
                try {
                    eval("var data = " + txt + ";");
                } catch (e) {
                    $.Alert(txt);
                    return;
                }

                var box = $(elem).parent(".lightbox-content").k(0);
                updateContent(box, data);
                render_data.call(box);
            }
        });
    }


    window.add_label = function(e){
        var label = $(e).pre("input.var-name").get('value'),
        url   = $(e).pre("input.var-url").get('value'),
        box   = $(e).parent(".lightbox-content").k(0);

        e.disabled= true;
        $.Ajax( url ,{
            type: 'POST',
            data: {
                label: label,
                t:	'post'
            },
            create:ajax_show,
            error: function(){
                e.disabled= false;
                ajax_error();
            },
            complete: function(){
                e.disabled= false;
                ajax_hide();
            },
            success: function( txt ){
                try {
                    eval("var data = " + txt + ";");
                } catch (e) {
                    $.Alert(txt);
                    return;
                }

                updateContent(box, data);
                render_data.call(box);
            }
        });
    };

    window.edit_label = function (e){
        var label = $(e).pre("input.var-name").get('value'),
        id 	  = $(e).pre("input.var-id").get('value'),
        url   = $(e).pre("input.var-url").get('value'),
        box   = $(e).parent(".lightbox-content").k(0);

        e.disabled= true;
        $.Ajax( url ,{
            type: 'POST',
            data: {
                label: label,
                ID: id,
                t:	'post'
            },
            create:ajax_show,
            error: function(){
                e.disabled= false;
                ajax_error();
            },
            complete: function(){
                e.disabled= false;
                ajax_hide();
            },
            success: function( txt ){
                try {
                    eval("var data = " + txt + ";");
                } catch (e) {
                    $.Alert(txt);
                    return;
                }

                updateContent(box, data);
                render_data.call(box);
            }
        });
    };

    window.show_edit_label = function(e){
        $(e.parentNode.parentNode.parentNode)
        .find(".edit-form-label")
        .css("display:none");

        $(e.parentNode.parentNode)
        .find(".edit-form-label")
        .css("display:inline-block")
        .each(function(){
            var elem = $(this).parent(".lightbox-content").k(0);

            $(this).css({
                left:	(elem.clientWidth - this.offsetWidth)/2 + 'px',
                top: '20px'
            });
        });
    };


    //Render form add labels
    APP_CALLBACK['render_add_label'] = function(){
        $(this)
        .find(".add-labels")
        .each(function(){
            var list = $(this).find(".add-label-lists");

            $(this)
            .find(".small-button")
            .onClick(function(e){
                e.preventDefault();
                var url = $(".var-url").get('value');
                var id = $(".var-id").get('value');
                ajax_load(url,function(){
                    this.elemList = list;
                    render_data.call(this)
                });
            });

            $(list)
            .find(".tag-remove")
            //.set('elemBox',box)
            .onClick(function(){
                $(this.parentNode).remove();
                $(this.elemBox)
                .find("input[value="+ id +"]")
                .each(function(){
                    simulatedClick(this)
                })
            });
        });
    };



    window.doLabel = function(url,id,elem){
        if (elem.xhr) {
            elem.xhr.stop();
        }

        var ids = $('#' + id).query(),
        labels = $(elem).parent("form").query();

        if (isEmpty(ids)) {
            $.Alert("Chưa có bản ghi nào được chọn");
            return;
        }

        if (isEmpty(labels)) {
            $.Alert("Chưa có nhãn nào được chọn");
            return;
        }

        var params = $.Extend(ids,labels);
        params['_json'] = 'yes';

        elem.xhr = $.Ajax(baseURL+'/'+url, {
            type : 'POST',
            data : params,
            create : ajax_show,
            complete : ajax_hide,
            error : ajax_error,
            success : function (txt) {
                try {
                    eval("var data = " + txt + ";");
                } catch (e) {
                    $.Alert(txt);
                    return false;
                }
                updateContent(null,data);
            }
        });
        return false;
    };

})(Owl);