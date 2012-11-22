/* @license http://opensource.org/licenses/gpl-license.php GNU Public License
   @author Kevin Papst
   @copyright Copyright (C) Kevin Papst
   @version $Id: partial_header.phtml 2 2010-07-25 14:27:00Z kevin $ */ 
   
dojo.provide("bigace.admin.MenuPane");
dojo.require("dijit.layout.ContentPane");

/**
 * A ContentPane that automatically converts all forms from the external 
 * content into xhrPost's and replaces the content area with the response body.
 */
dojo.declare("bigace.admin.MenuPane", dijit.layout.ContentPane, {

    /**
     * If existing, strip content outside <body>.
     * 
     * @var boolean
     */
    extractContent: true,
    /**
     * We want all scripts within the content to be executed.
     *
     * @var boolean
     */
    executeScripts: true, 
    /**
     * We want declarative dojoTypes to be parsed.
     *
     * @var boolean
     */
    parseOnLoad: true, 
    /**
     * And the pane should be closable.
     *
     * @var boolean
     */
    closable: true,
    /**
     * Array of all registered action handler.
     *
     * @var array
     */
    handler: [],
    
    /**
     * Called when download is finished.
     *
     * Connects to all static forms and replaces their default behaviour with dojo.xhrPost 
     * so we can replace the ContentPane instead of reloading the page.
     */
	onDownloadEnd: function()
	{
    	this.onLoadDeferred.then(
    	    dojo.hitch(this, this.connectFormAndLinks),
    	    dojo.hitch(this, function(error) {
    	        console.log(error);
    	    })
    	);
	},
	
	connectFormAndLinks: function(value)
	{
        // convert forms to xhrPost
        dojo.query("form", this.containerNode).forEach(
            dojo.hitch(this, function(node, index, arr) {
                this.handler.push(
                    dojo.connect(node, "onsubmit", dojo.hitch(this, "_sendFormViaXhr"))
                );  
            })
        );	    
        
        // convert links that do not have a target attribute to xhrGet
        dojo.query('a:not([target])', this.containerNode).filter(function(item) {
            return ( item.href != '' && item.href != '#' );
        }).forEach(
            dojo.hitch(this, function(node, index, arr) {
                this.handler.push(
                    dojo.connect(node, "onclick", dojo.hitch(this, "_openLinkViaXhr"))
                );  
            })
        );
    },

	/**
	 * Loads the URL via dojo.xhrGet() and replaces the content
	 * of this pane with the payload of the GET answer.
	 */
	_openLinkViaXhr: function(e)
	{
        // prevent the link from actually being loaded in the window
        e.preventDefault();

		// Cancel possible prior in-flight request
		this.cancel();

        // callback before the link is loaded
        this.onLinkLoad(e.target);
        
        // display the loading message
		this._setContent(this.onDownloadStart(), true);
		
		// remove all event handler
		this._disconnectHandler();
				
		// open the link in the background   
        dojo.xhrGet({
            url: e.target.href,
            handleAs: "text",
            handle: dojo.hitch(this, function(data, args){
                if(typeof data == "error"){
                    console.warn(data,args);
                    alert("Sorry, data could not be loaded: " + args);
                } else {
                    // set the content
                    this.set('content', data);
                    // notify about the new content
    				this.onDownloadEnd();                    
                    // callback after the content was replaced
                    this.afterLinkLoad(data);
                }
            })
        });
	},
		
	/**
	 * Receives the onSubmit event of a form inside this pane. 
	 * Takes it and sends th form via dojo.xhrPost() and replaces the content
	 * of this pane with the payload of the POST answer.
	 */
	_sendFormViaXhr: function(e)
	{
        // prevent the form from actually submitting
        e.preventDefault();

		// Cancel possible prior in-flight request
		this.cancel();
		
        // callback before the form is posted
        this.onFormSubmit(e.target);
        
        // display the loading message
		this._setContent(this.onDownloadStart(), true);
		
		// remove all event handler
		this._disconnectHandler();
		
		// submit the form in the background   
        dojo.xhrPost({
            form: e.target, // the form itself is always the target of the submit event
            handleAs: "text",
            error:  dojo.hitch(this, function(error, ioArgs){
                this.set('content', '<h2>Sorry, an error occured</h2><div>' + error + '</div>');
            }),
            load: dojo.hitch(this, function(data, args){
                if(typeof data == "error" || data == 'undefined'){
                    console.warn(data, args);
                    this.set('content', '<h2>Sorry, an error occured</h2><div>' + data+ '</div>');
                } else {
                    // set the content
                    this.set('content', data);
                    // notify about the new content
    				this.onDownloadEnd();                    
                    // callback after the content waqs replaced
                    this.afterFormSubmit(data);
                }
            })
        });
	},
	
    /**
     * Callback, executed just before a link without target attribute is loaded.
     *
     * @param DOMNode link
     */	
	onLinkLoad: function(link) {},

    /**
     * Callback, executed after the form was submitted and the content replaced.
     *
     * @param string content
     */	
	afterLinkLoad: function(content) {},
	
    /**
     * Callback, executed just before the passed form is submitted.
     *
     * @param DOMNode form
     */	
	onFormSubmit: function(form) {},
	
    /**
     * Callback, executed after the form was submitted and the content replaced.
     *
     * @param string content
     */	
	afterFormSubmit: function(content) {},

    /**
     * Disconnects all handler from dojo.
     */
    _disconnectHandler: function()
    {
        dojo.forEach(this.handler, dojo.disconnect);
    },
    
    /**
     * Overwritten to parse the new dom.
     */
    _setContent: function(cont, isFakeContent)
    {
        this.inherited(arguments);
        parseDomForBigace('#'+this.domNode.id);
    }
    
});
