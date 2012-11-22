/* @license http://opensource.org/licenses/gpl-license.php GNU Public License
   @author Kevin Papst
   @copyright Copyright (C) Kevin Papst
   @version $Id: partial_header.phtml 2 2010-07-25 14:27:00Z kevin $ */ 
   
dojo.provide("bigace.admin.Menus");

dojo.require("bigace.admin.Items");
dojo.require("bigace.admin.MenuPane");
dojo.require('bigace.util.Window');

/**
 * Class to handle the tabbed Menu administration.
 */
dojo.declare("bigace.admin.Menus", bigace.admin.Items, {
    /**
     * Dom ID of the Tab Container.
     *
     * @var string
     */
    tabContainerId: 'tabsAdminPages',
    /**
     * The Dom ID, where the Tree is injected.
     *
     * @var string
     */
    treeId: 'tree-data',
    /**
     * The Dom ID of the welcome tab.
     *
     * @var string
     */
    welcomeId: 'welcomePanelTab',
    /**
     * Whether we are in multiselect mode or not.
     *
     * @var boolean
     */
    multi: false,
    /**
     * Root ID of the tree.
     *
     * @var integer
     */
    rootID: -1,
    /**
     * Root ID of the tree.
     *
     * @var integer
     */
    rootParent: -9999,
    /**
     * Language of the root node.
     *
     * @var string
     */
    rootLang: "en",
    /**
     * Object which only exists between the start of a page update and a callback
     * to update the tree and tabpane accordingly.
     *
     * @var object
     */
    updatedItem: null,
    /**
     * Reference to a ContentPane, that holds the "create page" form.
     *
     * @var dijit.layout.ContentPane
     */
    newPageTab: null,
    /**
     * Translations used by the ItemPane.
     *
     * @var object
     */
    translations: {
        errorDefault:      'Error occured: ',
        errorDelete:       'Failed deleting Item',
        errorMove:         'Failed moving Item',
        errorPreview:      'Error in preview: ',
        loading:           'Loading ...',
        confirmDeletePage: 'Do you really want to delete this page: ',
        confirmDeleteTree: 'If you delete this page, all children will also be deleted!',
        createPage:        'Create page',
        newTreeNode:       'New page',
        confirmChangeLang: 'Do you want to change the language? Unsaved changes will be lost!'
    },
    /**
     * Required URLs by the ItemPane to work.
     *
     * @var object
     */
    urls: {
        remove:  "",
        move:    "",
        info:    "",
        create:  "",
        sort:    "",
        mode:    "",
        edit:    "",
        wysiwyg: "",
        tree:    ""
    },    
    /**
     * The Trees context-menu, available on right-click on a TreeNode.
     *
     * @var object
     */
    contextNormal: [],
    /**
     * Configuration options for the Tree.
     *
     * @var array
     */
    treeConfig: {},    
    /**
     * Array of all open tabs, where a page is edited.
     *
     * @var array(dijit.layout.ContentPane)
     */
    openTabs: [],
    
    /**
     * Returns the TabContainer used for Menu-administration.
     *
     * @return dijit.layout.TabContainer
     */
    getTabPane: function()
    {
        return dijit.byId(this.tabContainerId);
    },
    
    /**
     * Returns the active tabs (here a ContentPane).
     *
     * @return dijit.layout.ContentPane
     */
    getActiveTab: function() 
    {
        return this.getTabPane().selectedChildWidget;
    },
    
    /**
     * Returns the currently selected item from the tabs.
     * If the currently selected tab is not an item admin page, this returns null.
     *
     * @return array|null
     */
    getItemInfoForActiveTab: function() 
    {
        var currentTab = this.getActiveTab();
        for (var i = 0; i < this.openTabs.length; i++) {
            if (this.openTabs[i].tab == currentTab) {
                return this.openTabs[i];
            }
        }        
        return null;
    },
    
    /**
     * Returns either the tab for the given item id or null if no tab could be found.
     * If null is passed as language, the first tab with the id is returned.
     *
     * @var integer id
     * @var string language
     * @return dijit.layout.ContentPane
     */
    getTabForItem: function(id, language) 
    {
        for (var i = 0; i < this.openTabs.length; i++) {
            if (this.openTabs[i].id == id) {
                if (language == null || this.openTabs[i].language == language) {
                    return this.openTabs[i].tab;
                }
            }
        }             
        return null;
    },
    
    /**
     * AJAX: Deletes a page and shows an alert() if an error occured.
     *
     * Refreshes the tree after deletion.
     *
     * @param integer id
     * @param string language
     */
    deletePageById: function(id, language, NODE)
    {
        var posturl = this.urls.remove;
        posturl = posturl.replace(/__ID__/g, id);
        posturl = posturl.replace(/__LANGUAGE__/g, language);
        
        var outer = this;
        
        var xhrArgs = {
            url: posturl,
            postData: '',
            handleAs: "json",
            load: function(data) {
                if (typeof data.result == 'undefined') {
                    alert(outer.translations.errorDelete);
                } else {
                    if (data.result == false || data.type != 'success') {
                        alert(outer.translations.errorDelete + ' = ' + data.message);
                    } else {
                        // remove item admin panel
                        var tab = outer.getTabForItem(id, language);
                        if (tab != null) {
                            outer.getTabPane().removeChild(tab);
                        }
                        outer.refresh();
                    }
                }
            },
            error: function(error) {
                alert(outer.translations.errorDelete + ": " + error);
            }
        }
        var deferred = dojo.xhrPost(xhrArgs);
    },

    /**
     * AJAX: Loads information about the page with the "itemid" and "languageid".
     *
     * @var integer itemid
     * @var integer languageid
     * @var boolean asynchronous
     */
    requestItemInfo: function(itemid, languageid, asynchronous)
    {
        var url = this.urls.info;
        url = url.replace(/__ID__/g, itemid);
        url = url.replace(/__LANGUAGE__/g, languageid);                
        return loadItem(url, asynchronous);
    },
    
    /**
     * Returns whether a new admin tab should be opened on click on a tree-node.
     *
     * @return boolean
     */
    openAdminTabOnSelect: function()
    {
        return dijit.byId("openOnSelect").checked;
    },
    
    /**
     * Opens the "create page" form in a new tab, with the parent preselected.
     *
     * @param integer parentid
     * @param string locale
     */
    createPageById: function(parentid, locale)
    {
        // var selected = this.getSelectedPage();
        // this.getTree().create(false, (selected != null ? selected[0] : $("#-1")));
        // TREE_OBJ.create(false, TREE_OBJ.selected);
        
        if (this.newPageTab != null) {
            this.getTabPane().selectChild(this.newPageTab);
            return;
        }
            
        var url = this.urls.create;
        url = url.replace(/__PARENT__/g, parentid);
        url = url.replace(/__LANGUAGE__/g, locale);
        this.newPageTab = this.openTreeRefreshingTab(url, this.translations.createPage);
    },
        
    /**
     * Opens the "create page" form in a new tab.
     * Uses the root item as preselected new parent.
     *
     * @see createPageById()
     */
    createMenu: function()
    {
        this.createPageById(this.rootID, this.rootLang);
    },

    /**
     * Opens the "create page" form in a new tab.
     * Uses the given TreeNode as preselected parent.
     *
     * @see createPageById()
     * @param object(?) NODE
     */
    createMenuByNode: function(NODE)
    {
        var data = NODE.metadata();
        this.createPageById(data.id, data.language);
        // this.getTree().create(false, (selected != null ? selected[0] : $("#-1")));
        // TREE_OBJ.create(false, TREE_OBJ.selected);
    },
        
    /**
     * Returns the first tab, which holds the welcome page and all quick infos.
     *
     * @return dijit.layout.ContentPane
     */
    getWelcomeTab: function()
    {
        return dijit.byId(this.welcomeId);
    },
    
    /**
     * Panel API.
     *
     * Reloads the tree.
     */
    refresh: function()
    {
        // prevent "faked" click on refresh
        // the selected node would raise an "onselect" event after refresh otherwise and 
        // open or reselect the tab (which should not happen in case one item is 
        // selected and another focused in the tabpane).
        if (this.isPageSelected()) {
            this.getTree().deselect_branch(this.getSelectedPage());
        }
        
        this.getTree().refresh();
    },
        
    /**
     * ITEM PERMISSIONS 
     *
     * Opens the permission form in a new tab for the given node.
     *
     * @param object(?) NODE
     */
    editPermissions: function(NODE)
    {
        var data = NODE.metadata();
        this.openScreenByMode(data, 'permission');
    },

    /**
     * ITEM CATEGORIES
     *
     * Opens the categories form in a new tab for the given node.
     *
     * @param object(?) node
     */
    editCategories: function(NODE)
    {
        var data = NODE.metadata();
        this.openScreenByMode(data, 'categories');
    },
    
    /**
     * Switches the tree language and reloads all items to show up in the 
     * selected language.
     */
    loadLanguageTree: function(lang)
    {
        if (this.isPageSelected()) {
            this.getTree().deselect_branch(this.getSelectedPage());
        }
	    this.rootLang = lang;
        this.getTree().destroy();
        this.getTree().init($("#"+this.treeId), $.extend({},this.getTreeConfig()));
    },
    
    /**
     * Returns the used tree-instance.
     *
     * @return object(?) jsTree
     */
    getTree: function()
    {
        return this.jsTree;
    },

    /**
     * Returns the tree configuratiopns.
     *
     * @return array
     */
    getTreeConfig: function()
    {
        return this.treeConfig;
    },
    
    /**
     * Returns whether a TreeNode is selected in the tree.
     *
     * @return boolean
     */
    isPageSelected: function()
    {
        return this.getTree().selected;
    },
    
    /**
     * Returns the first selected TreeNode or null.
     *
     * @return TreeNode|null
     */
    getSelectedPage: function()
    {
        if (this.isPageSelected()) {
            return this.getTree().selected[0];
        }
        return null;
    },
    
    /**
     * Switches the tree mode, either from normal to multi-selection or vice-versa.
     */
    switchTreeMode: function()
    {
        if(this.getTreeConfig().ui.theme_name == 'bigace') {
            this.getTreeConfig().ui.theme_name = 'checkbox';
        }
        else {
            if (this.isPageSelected()) {
                this.multi = true;
            }
            this.getTreeConfig().ui.theme_name = 'bigace';
        }

        this.getTree().destroy();
        this.getTree().init($("#"+this.treeId), $.extend({},this.getTreeConfig()));
    },

    /**
     * Open a dialog to edit the menus attributes.
     *
     * @var object(?) NODE
     */
    adminPage: function(NODE)
    {
        var data = NODE.metadata();
        this.openMenuAdministration(data);
    },

    /**
     * Open a preview of the given menu.
     *
     * @var string url
     */
    preview: function(url)
    {
        try {
            var win = new bigace.util.Window({url:url, openOnInit:true, fullsize:true});
            win.getWindow().focus();
        } catch(ex) {
            alert(this.translations.errorPreview + ex);
        }
    },

    /**
     * Deletes the given TreeNode and the underlying page. If the page is not a 
     * leaf, a confirm from the user is requested.
     *
     * @var object(?) NODE
     */     
    deletePage: function(NODE)
    {
        var data = NODE.metadata();    
        var itemid = data.id;
        var langid = data.language;
        try {
            var ditem = this.requestItemInfo(itemid, langid, false);
            msg = this.translations.confirmDeletePage;
            if(!ditem.isLeaf()) {
                msg = this.translations.confirmDeleteTree + '\r\n' + msg;
            }
            if(confirm(msg + ' ' + ditem.getName())) {
                this.deletePageById(ditem.getID(), langid, NODE);
            }
        } catch(ex) {
            alert(this.translations.errorDelete + ex);
        }
    },

    /**
     * Opens the WYSIWYG editor for the given TreeNode.
     *
     * @var object(?) NODE
     */
    editPageContent: function(NODE)
    {
        var data = NODE.metadata();
        this.editPageContentRaw(data.id, data.language, data.name);
    },

    /**
     * Opens the WYSIWYG editor for the given TreeNode.
     *
     * @var object(?) NODE
     */
    editPageContentRaw: function(id, language, title)
    {
        var url = this.urls.wysiwyg;
        url = url.replace(/__ID__/g, id);
        url = url.replace(/__LANGUAGE__/g, language);
        
        var options = {
            url:url, 
            title: title, 
            name: 'Edit' + id + language, 
            openOnInit:true, 
            fullsize:true
        };
        var win = new bigace.util.Window(options);
        win.getWindow().focus();
    },
        
    /**
     * Opens a new administration tab for the given menu and mode.
     *
     * @param array data
     * @param string mode
     */
    openScreenByMode: function(data, mode)
    {
        this.openScreenByModeRaw(data.id, data.language, mode, data.name);
    },
    
    /**
     * Opens the admin screen in "mode" for the active item.
     *
     * @param string url
     * @param integer id
     * @param string language
     */
    openUrlForActiveItem: function(url, id, language)
    {
        var data = this.getItemInfoForActiveTab();
        if (data == null) {
            return false;
        }
        this.openItemAdmin(url, data.id, data.language, data.title);
    },
    
    /**
     * Opens a new tab for an item.
     *
     * Allows only one tab per item, if opening another language a confirm dialog is shown.
     *
     * @param string url
     * @param integer id
     * @param string language
     * @param string title
     */
    openItemAdmin: function(url, id, language, title)
    {
        // find out if the page is already opened, if so, select it and stop
        for (var i = 0; i < this.openTabs.length; i++)
        {
        	// only match, if both id and language match
            if (this.openTabs[i].id == id && this.openTabs[i].language == language) {
            	// if the wrong url (admin mode) is open, then reload it with the new url
                if (this.openTabs[i].url != url) {
                    this.openTabs[i].url = url;
                    this.openTabs[i].tab.set('href', url);
                    // title would be "permission" and "category"
                    //this.openTabs[i].tab.set('title', title);
                } else {
                	// otherwise
                	this.getTabPane().selectChild(this.openTabs[i].tab);
                }
                return;
            }
        	/*
        	 * do not ask for changing the language, just open a new tab
            if (this.openTabs[i].id == id) {
                this.getTabPane().selectChild(this.openTabs[i].tab);
                var doReload = false;
                if(this.openTabs[i].language != language) {
                    if(confirm(this.translations.confirmChangeLang)) {
                        doReload = true;                    
                    }
                }
                if (doReload || this.openTabs[i].url != url) {
                    this.openTabs[i].url = url;
                    this.openTabs[i].language = language;
                    this.openTabs[i].tab.set('href', url);
                    this.openTabs[i].tab.set('title', title);
                }
                return;
            }
            */
        }        
    
        var pane = this.openAdministrationTab(url, title);
        this.openTabs.push({
            id: id, 
            language: language, 
            url: url, 
            tab: pane,
            title: title
        });    
    },

    /**
     * ITEMS ATTRIBUTES
     *
     * Opens the item attribute form for the given values.
     *
     * @params integer id
     * @params string language
     * @params string title
     */    
    openMenuAdministrationRaw: function(id, language, title)
    {
        if(title.length > 23) {
            title = title.substr(0,20) + '...';
        }
    	this.editItemRaw(id, language, title);
    },

    /**
     * ITEMS ATTRIBUTES
     *
     * Opens the item attribute form for the given data.
     *    
     * @param array data
     */
    openMenuAdministration: function(data)
    {
        this.openMenuAdministrationRaw(data.id, data.language, data.name);
    },

    
    /**
     * Opens a new Tab and refreshes the tree, every time the inner form is submitted.
     *
     * @param string url
     * @return dijit.layout.ContentPane the added tab
     */    
    openTreeRefreshingTab: function(url, tabTitle)
    {
        return this.openAdministrationTabCallback(
            url, 
            tabTitle,
            function(form) {},
            dojo.hitch(this, function() {
                // update the tree item
                this.refresh();
                // update the overview tab
                dijit.byId('welcomePanelTab').refresh();
            })
        );
    },
        
    /**
     * Opens a new Tab and inside the remote content from the url.
     * This tab is specialized in item administration/attributes.
     *
     * @param string url
     * @return dijit.layout.ContentPane the added tab
     */    
    openAdministrationTab: function(url, tabTitle)
    {
        return this.openAdministrationTabCallback(
            url, 
            tabTitle,
            dojo.hitch(this, function(form) {
                // only if itemAttributes are to be submitted, we remember the values
                // and update tree and tabpane accordingly
                if (form.name == 'itemAttributes') {
                    var title    = dojo.query('input[name="data[name]"]', form);
                    var id       = dojo.query('input[name="data[id]"]', form);
                    var language = dojo.query('input[name="data[langid]"]', form);
                    if (id.length > 0 && title.length > 0 && language.length > 0) {
                        this.updatedItem = {
                            id: id.attr('value'), 
                            title: title.attr('value'),
                            language: language.attr('value')
                        };
                    }
                }
            }),
            dojo.hitch(this, function() {
                if (this.updatedItem != null) {
                    // update tab title
                    var tab = this.getTabForItem(this.updatedItem.id, this.updatedItem.language);
                    if (tab != null) {
                        tab.set('title', this.updatedItem.title);
                    }
                    // and update the tree item as well
                    this.refresh();
                    // and update the overview tab
                    dijit.byId('welcomePanelTab').refresh();
                    this.updatedItem = null;
                }
            })
        );
    },

    /**
     * Opens a new Tab and inside the remote content from the url.
     *
     * @param string url
     * @return dijit.layout.ContentPane the added tab
     */    
    openAdministrationTabCallback: function(url, tabTitle, preHook, afterHook)
    {
        var tabs  = this.getTabPane();
        var outer = this;
        
        var pane = new bigace.admin.MenuPane({ 
            title: tabTitle, 
            href: url, 
            onClose: function() {
		        this._disconnectHandler();
                for (var i = 0; i < outer.openTabs.length; i++) {
                    if (outer.openTabs[i].tab == this) {
                        outer.openTabs.splice(i,1);
                        return true;
                    }
                }
                
                if (outer.newPageTab == this) {
                    outer.newPageTab = null;
                    return true;
                }
                
                return true;
            },
            onFormSubmit: preHook,
            afterFormSubmit: afterHook
        });
        
        tabs.addChild(pane);
        tabs.selectChild(pane);
        return pane;
    }
    
});

