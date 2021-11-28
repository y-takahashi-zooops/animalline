$(function(){

	$(".item-list.type01 dt").click(function(){
		// $(this).parent("div").toggleClass("on");
		$(this).next("dd").slideToggle(500,function(){
			$(this).parent("div").toggleClass("on");
		});
	});

});