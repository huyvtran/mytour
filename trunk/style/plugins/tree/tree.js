/*
	Tree functions
	Lastupdate: 26/03/2011
*/

(function( $ ){
    function tree_collapse( elem ){
        $(elem.parentNode)
        .child(".tree-folder")
        .child(".tree-list-outer")
        .toggle();

        if( $(elem).hasClass("tree-open") ){
            $(elem).removeClass("tree-open");
        }else{
            $(elem).addClass("tree-open")
        }
    }

    function tree_check_all(elem){
        $(elem.parentNode.parentNode)
        .find("input[type=checkbox]")
        .set("checked", elem.checked );
    }


    /* register for window */
    window.tree_collapse = tree_collapse;
    window.tree_check_all = tree_check_all;


})( Owl );

