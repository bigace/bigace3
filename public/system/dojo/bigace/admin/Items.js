/* @license http://opensource.org/licenses/gpl-license.php GNU Public License
   @author Kevin Papst
   @copyright Copyright (C) Kevin Papst
   @version $Id: partial_header.phtml 2 2010-07-25 14:27:00Z kevin $ */

dojo.provide("bigace.admin.Items");

dojo.require("dijit._Widget");
dojo.require('bigace.util.Window');

/**
 * A base class, holding methods for Actions in Item administration.
 */
dojo.declare("bigace.admin.Items", dijit._Widget, {
    /**
     * Path where Icons can be found.
     *
     * @var string
     */
    iconFolder: '',
    /**
     * Translations used by the ItemPane.
     *
     * Should be injected through the constructor.
     *
     * @var array
     */
    translations: {
        errorMove:    'Failed moving Item',
        errorPreview: 'Error in preview: ',
    },
    /**
     * Required URLs by the ItemPane to work.
     */
    urls: {
        mode: "",
        move: "",
        edit: "",
    },

    /**
     * Opens the administration form in "mode" for the item with the given values.
     *
     * @param integer id
     * @param string language
     * @param string mode
     * @param string title
     */
    openScreenByModeRaw: function(id, language, mode, title)
    {
        var url = this.urls.mode;
        url = url.replace(/__ID__/g, id);
        url = url.replace(/__LANGUAGE__/g, language);
        url = url.replace(/__MODE__/g, mode);
        this.openItemAdmin(url, id, language, title);
    },

    /**
     * Opens a new tab for an item.
     *
     * @param string url
     * @param integer id
     * @param string language
     * @param string title
     */
    openItemAdmin: function(url, id, language, title)
    {
        location.href = url;
    },

    /**
     * Opens the admin screen with "url" for the active item with the id
     * and language.
     *
     * @param string url
     * @param integer id
     * @param string language
     */
    openUrlForActiveItem: function(url, id, language)
    {
        location.href = url;
    },

    /**
     * Item changed, reload content.
     */
    refresh: function()
    {
        // no need for any action here
    },

    /**
     * Returns the correct URL to the icon with the filename icon.
     *
     * @param string icon
     * @return string
     */
    getIconUrl: function(icon)
    {
        if (this.iconFolder != '') {
            icon = this.iconFolder + icon;
        }
        return icon;
    },

    /**
     * ITEM CATEGORIES
     *
     * Opens the categories form for the item with the given values.
     *
     * @param integer id
     * @param string language
     * @param string title
     */
    editCategoriesRaw: function(id, language, title)
    {
        this.openScreenByModeRaw(id, language, 'categories', title);
    },

    /**
     * ITEM ADMINISTRATION
     *
     * Opens the item attribute form for the given values.
     *
     * @param integer id
     * @param string language
     * @param string title
     */
    editItemRaw: function(id, language, title)
    {
        this.openScreenByModeRaw(id, language, 'edit', title);
    },

    /**
     * ITEM PERMISSIONS
     *
     * Opens the permission form for the item with the given values.
     *
     * @param integer id
     * @param string language
     * @param string title
     */
    editPermissionsRaw: function(id, language, title)
    {
        this.openScreenByModeRaw(id, language, 'permission', title);
    },

    /**
     * AJAX: Moves the page with the "id" to a "newParent" and shows an alert() if an error occured.
     *
     * @var integer id
     * @var string language
     * @var integer toId
     * @var string type
     * @return boolean
     */
     moveItem: function(id, language, toId, type)
     {
        var posturl = this.urls.move;
        posturl = posturl.replace(/__ID__/g, id);
        posturl = posturl.replace(/__LANGUAGE__/g, language);
        posturl = posturl.replace(/__PARENT__/g, toId);
        posturl = posturl.replace(/__TYPE__/g, type);
        var outer = this;

        var xhrArgs = {
            url: posturl,
            postData: '',
            handleAs: "json",
            load: function(data) {
                if (typeof data.result == 'undefined') {
                    alert(outer.translations.errorMove);
                } else {
                    if (data.result == false || data.type != 'success') {
                        alert(outer.translations.errorMove + ' = ' + data.message);
                    }
                }
                outer.refresh();
            },
            error: function(error) {
                alert(outer.translations.errorMove + ": " + error);
            }
        }
        var deferred = dojo.xhrPost(xhrArgs);
    },


    /**
     * Open a preview of the given menu.
     *
     * @var string url
     */
    preview: function(url)
    {
        try {
            var win = new bigace.util.Window({url: url, openOnInit: true, fullsize: true});
            win.getWindow().focus();
        } catch(ex) {
            alert(this.translations.errorPreview + ex);
        }
    }

});