/*(function( $ ){
	$(window)
		.onScroll(function(){
			if( document.body.scrollTop > $('#menu').top() ){
				$('#menu').css({
					position:'fixed',
					top: '0px',
					zIndex:2000,
					width:$('#header').width()-20+'px'
				});
			}else{
				$('#menu').css({
					position:'static',
					top: '2px',
					width:'auto'
				});			
			}	
		});
})(Owl);*/