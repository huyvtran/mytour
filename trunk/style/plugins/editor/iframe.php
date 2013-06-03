<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9">
        <link rel="stylesheet" href="../../layout.css"/>
        <script src="../../owl.js"></script>
        <script src="rangy.js"></script>
        <script src="rangy.css.js"></script>
        <script src="owl.editor.js"></script>
        <script type="text/javascript">
            $(function(){
                try{
                    rangy.init();
                }catch(e){

                }
                //setTimeout(function(){
                var win = window.parent, id;
                if(!win) return;
                if( location.href.match(/id=([a-z0-9_\-]+)(&|$)/i)){
                    id = RegExp.$1;
                }else{
                    return;
                }

                var editor = $(win.document).find('#'+id).k(0);
                var area = $(editor).find('textarea.plain').k(0);

                document.body.innerHTML = area.value;
                $('body').empty(".elem-except");
                area.value = document.body.innerHTML;
                new Editor(editor);

                if( 'contentEditable' in document.body){
                    document.body.contentEditable = true;
                }else{
                    document.designMode = 'On';
                }

                // },2000);
                document.body.focus();
            });
        </script>
        <style>
            body{
                background:#fff
            }
        </style>
    </head>
    <body autofocus="true">

    </body>
</html>