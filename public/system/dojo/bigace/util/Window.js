/* @license http://opensource.org/licenses/gpl-license.php GNU Public License
   @author Kevin Papst
   @copyright Copyright (C) Kevin Papst
   @version $Id: partial_header.phtml 2 2010-07-25 14:27:00Z kevin $ */

dojo.provide("bigace.util.Window");

/**
 * Creates a popup window.
 */
dojo.declare("bigace.util.Window", null, {
    /**
     * The name of the window and how it is referenced.
     *
     * @var string
     */
    name:"bigace_util_Window",
    /**
     * The name in the title bar .
     *
     * @var string
     */
    title:"",
    /**
     * Position and size of the window on open.
     *
     * @var integer
     */
    x:10,
    y:10,
    w:200,
    h:300,
    /**
     * Shows or hides the elements of the popup window.
     *
     * @var integer(0,1)
     */
    statusBar: 0,
    menuBar: 0,
    addressBar: 0,
    scrollBar: 1,
    resizable: 0,
    /**
     * Whether the popup should be opened in fullsize.
     *
     * @var boolean
     */
    fullsize:false,
    /**
     * The HTML to be inserted in the body.
     * Only applies if no url is supplied.
     *
     * @var string
     */
    content: '',
    /**
     * The URL to load.
     * If you do not want to load a URL, you can pass in content as well.
     *
     * @var string
     */
    url: '',
    /**
     * Whether the window opens on init or waits for the open() method.
     *
     * @var boolean
     */
    openOnInit: false,

    constructor: function(options)
    {
        // calculate default size and position
        this.w = screen.width / 2;
        this.h = screen.height / 2;
        this.x = (screen.width - (screen.width / 2) ) / 2;
        this.y = (screen.height - (screen.height / 2) ) / 2;

        // apply user defined properties
        dojo.mixin(this, options);

        // resize if requested
        if (this.fullsize) {
            this.w = screen.width;
            this.h = screen.height;
            this.x = 0;
            this.y = 0;
        }

        if (this.openOnInit) {
            this.open();
        }
        dojo.connect(window, "unload", this, "destroy");
    },

    /**
     * Fires on popup resize.
     */
    onResize: function()
    {
    },

    /**
     * Fires on popup close.
     */
    onClose: function()
    {
    },

    /**
     * Fires on popup load.
     */
    onLoad: function()
    {
    },

    /**
     * Closes the window.
     */
    close: function()
    {
        if (!this.win){
            return;
        }
        this.disconnect();
        this.win.close();
        this.win = null;
    },

    /**
     * Sets innerHTML content in a node specified by the id.
     */
    setContent: function(id, str)
    {
        if (!this.win){
            return;
        }
        this.byId(id).innerHTML = str;
    },

    /**
     * Connecting to elements of the popup.
     */
    connect: function(id, event, scope, method)
    {
        if (!this.win){
            return;
        }
        var c = dojo.withGlobal(this.win, "connect", dojo, [this.byId(id), event, scope, method]);
        if (!this._connects){
            this._connects = [];
        }
        this._connects.push(c);
    },

    /**
     * Disconnects the popups.
     * If a pointer is passed, just discconects that one.
     *
     * @param integer id
     */
    disconnect: function(pointer)
    {
        if (pointer) {
            dojo.disconnect(pointer);
        } else if (this._connects) {
            dojo.forEach(this._connects, dojo.disconnect, dojo);
            this.win.onunload = null;
            this.win.onresize = null;
            this.win.onload = null;
        }
    },

    /**
     * Convenience method for getting elements of the popup.
     *
     * @param integer id
     */
    byId: function(id)
    {
        if (!this.win){
            return false;
        }
        return dojo.withGlobal(this.win, "byId", dojo, [id]);
    },

    /**
     * Returns the window (if already opened) or false.
     *
     * @return boolean|window
     */
    getWindow: function()
    {
        if (!this.win){
            return false;
        }
        return this.win;
    },

    /**
     * Opens the popup.
     */
    open: function()
    {
        if (this.win){
            return;
        }
        var features = "status="+this.statusBar+",menubar="+this.menuBar+",resizable="+this.resizable+",top=" + this.y + ",left=" + this.x + ",width=" + this.w + ",height=" + this.h + ",scrollbars="+this.scrollBar+",addressbar="+this.addressBar;
        var url = this.url;
        if (url == null) {
            url = '';
        }
        var win = window.open(url, this.name, features);
        if (!win) {
            alert("Could not open a pop-up window. Using a PopUp blocker?");
            return;
        }
        this.win = win;
        this.doc = this.win.document;

        // we want to show content instead of a URL
        if (url == '' && this.content != '') {
            var HTMLstring = '<html style="height:100%;"><head><title>' + this.title + '</title>'+
                            '</head>\n' +
                            '<body style="height:97%;" >\n' +
                            this.content +
                            '</body></html>';
            this.doc.write(HTMLstring);
            this.doc.close();
        }

        this.win.onunload = this.onClose;
        this.win.onresize = this.onResize;
        this.win.onload = this.onLoad;
    },

    /**
     * Destroy the popup.
     */
    destroy: function()
    {
        this.close();
        delete this;
    }


});