
$(function() {
		   
	$("ul#navi li:has(ul)").mouseover(function(e) {
		$(this).find("a:first").addClass("active").next().show();
	}).mouseleave(function() {
		$(this).find("a:first").removeClass("active").next().hide();
	});
	
	$("table tr:odd").addClass("odd");
	
	$("#search-sbmt").mouseover(function() {
		$(this).addClass("sbmt-hover");
	}).mouseout(function() {
		$(this).removeClass("sbmt-hover");
	});
  
});​

