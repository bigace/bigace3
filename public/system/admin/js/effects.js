/*
   @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
   @license    http://www.bigace.de/license.html     GNU Public License
   @version    $Id: effects.js 712 2010-05-12 12:39:43Z bigace $
*/

$(document).ready(function() { 
    parsePageForBigace();
});

function parsePageForBigace()
{
	/* Show dropdown submenus */
    $("ul.group li").hover(function(){
        $(this).addClass("hover");
        $('ul:first',this).css('visibility', 'visible');
    }, function(){
        $(this).removeClass("hover");
        $('ul:first',this).css('visibility', 'hidden');
    });

	/* Display the language-switcher menu */
	$(".langcloser").click(function() { 
		$("#lchooser").slideToggle("fast");	
	});	
	$("#helpbox").hover(function() { 
    		$("#inline_help").toggle();	
        },
        function(){	
    		$("#inline_help").toggle();
        }
	);	
    
    parseDomForBigace('#portlets');
}

function parseDomForBigace(selector)
{
    /* Select all checkboxes listings */	
	$(selector + " :checkbox.bulkcheck").click(function()				
	{
		var checked_status = this.checked;
 		$(this).parents('form:first').find(":checkbox").each(function() {
			this.checked = checked_status;
		});
	});	

    /* Small tooltips for every item with the class .tooltip */
	$(selector + " .tooltip").hover(function(e){
		    this.t = this.title;
		    this.title = "";
		    $("body").append('<p id="tooltip">'+ this.t +"</p>");
		    $("#tooltip").css("top",(e.pageY - 10) + "px")
                         .css("left",(e.pageX + 20) + "px")
                         .fadeIn("fast");
        },
	    function(){
		    this.title = this.t;
		    $("#tooltip").remove();
        });
	    $(selector + " .tooltip").mousemove(function(e){
		    $("#tooltip").css("top",(e.pageY - 10) + "px")
                         .css("left",(e.pageX + 20) + "px");
	});	

	/* Smooth removal of informal messages */
	$(selector + " .message").click(function() {
		$(this).slideUp("fast");							 	  
	});
	
	/* Rely on global jQuery UI class instead of self hacked statements */
	$(selector + " fieldset").toggleClass("ui-corner-all"); 
	/* Make .portlet-header act as handler for moving portlets */
	$(".column").sortable({
		connectWith: '.column',
		handle: '.portlet-header'
	});

	/* Create and style portlets */
	$(selector + " .portlet").find(".portlet-header")
		.prepend('<span class="ui-icon ui-icon-triangle-1-n"></span>');

	/* Arrow button in portlet header toggles portlet content */
	$(selector + " .portlet-header .ui-icon").click(function() {
//		$(this).parents(".portlet:first").find(".portlet-content").slideToggle("fast");
		$(this).parent().next().slideToggle("fast");
		$(this).toggleClass("ui-icon-triangle-1-s"); 
		return false;	
	});

    /* Support pre-closed portlets */
	$(selector + " .portlet-closed .ui-icon").toggleClass("ui-icon-triangle-1-s")
    	.parent().next().hide();
	
	/* Disable mouse selection on .column */
	$(selector + " .column").disableSelection();
}

function popup(url, popTitle, width, height)
{
    popupWindow = open(url,popTitle,"menubar=no,toolbar=no,statusbar=no,directories=no,location=no,scrollbars=yes,resizable=no,height="+height+",width="+width+",screenX=0,screenY=0");
    sWidth=screen.width;
    sHeight=screen.height;
    popupWindow.moveTo((sWidth-width)/2,(sHeight-height)/2);
	return popupWindow;
}
