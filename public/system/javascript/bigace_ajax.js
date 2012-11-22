/*
   @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
   @license    http://www.bigace.de/license.html     GNU Public License
   @version    $Id: bigace_ajax.js 484 2010-04-30 12:32:32Z bigace $
   
   TODO make me a jquery plugin
*/

// ------------------------------------------------------------------------
// simple api to send ajax xml requests browser independent

// request object
var BIGACEAjaxXmlRequest = function()
{}

BIGACEAjaxXmlRequest.prototype.GetHttpRequest = function()
{
	if ( window.XMLHttpRequest )		// Gecko
		return new XMLHttpRequest() ;
	else if ( window.ActiveXObject )	// IE
		return new ActiveXObject("MsXml2.XmlHttp") ;
}

BIGACEAjaxXmlRequest.prototype.PostUrl = function( urlToCall, parameters, asyncFunctionPointer )
{
	var oAjaxXmlRequest = this ;

	var bAsync = ( typeof(asyncFunctionPointer) == 'function' ) ;

	var oXmlHttp = this.GetHttpRequest() ;

	oXmlHttp.open( "POST", urlToCall, bAsync ) ;
    oXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    oXmlHttp.setRequestHeader("Content-Length", parameters.length);
    oXmlHttp.setRequestHeader("Connection", "close");

	if ( bAsync )
	{
		oXmlHttp.onreadystatechange = function()
		{
			if ( oXmlHttp.readyState == 4 )
			{
				oAjaxXmlRequest.DOMDocument = oXmlHttp.responseXML ;
				if ( oXmlHttp.status == 200 || oXmlHttp.status == 304 ) {
					asyncFunctionPointer( oAjaxXmlRequest ) ;
				} else {
					alert( 'XML request error: ' + oXmlHttp.statusText + ' (' + oXmlHttp.status + ')' ) ;
				}
			}
		}
	}

	oXmlHttp.send(parameters);

	if ( ! bAsync )
	{
		if ( oXmlHttp.status == 200 || oXmlHttp.status == 304 )
		{
			this.DOMDocument = oXmlHttp.responseXML ;
        }
		else
		{
			alert( 'XML request error: ' + oXmlHttp.statusText + ' (' + oXmlHttp.status + ')' ) ;
		}
	}

}

BIGACEAjaxXmlRequest.prototype.LoadUrl = function( urlToCall, asyncFunctionPointer )
{
	var oAjaxXmlRequest = this ;

	var bAsync = ( typeof(asyncFunctionPointer) == 'function' ) ;

	var oXmlHttp = this.GetHttpRequest() ;

	oXmlHttp.open( "GET", urlToCall, bAsync ) ;

	if ( bAsync )
	{
		oXmlHttp.onreadystatechange = function()
		{
			if ( oXmlHttp.readyState == 4 )
			{
				oAjaxXmlRequest.DOMDocument = oXmlHttp.responseXML ;
				if ( oXmlHttp.status == 200 || oXmlHttp.status == 304 ) {
					asyncFunctionPointer( oAjaxXmlRequest ) ;
				} else {
					alert( 'XML request error: ' + oXmlHttp.statusText + ' (' + oXmlHttp.status + ')' ) ;
				}
			}
		}
	}

	oXmlHttp.send( null ) ;

	if ( ! bAsync )
	{
		if ( oXmlHttp.status == 200 || oXmlHttp.status == 304 )
		{
			this.DOMDocument = oXmlHttp.responseXML ;
        }
		else
		{
			alert( 'XML request error: ' + oXmlHttp.statusText + ' (' + oXmlHttp.status + ')' ) ;
		}
	}
}

BIGACEAjaxXmlRequest.prototype.SelectNodes = function( xpath )
{
	if ( document.all )		// IE
		return this.DOMDocument.selectNodes( xpath ) ;
	else					// Gecko
	{
		var aNodeArray = new Array();

		var xPathResult = this.DOMDocument.evaluate( xpath, this.DOMDocument,
				this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), XPathResult.ORDERED_NODE_ITERATOR_TYPE, null) ;
		if ( xPathResult )
		{
			var oNode = xPathResult.iterateNext() ;
 			while( oNode )
 			{
 				aNodeArray[aNodeArray.length] = oNode ;
 				oNode = xPathResult.iterateNext();
 			}
		}
		return aNodeArray ;
	}
}

BIGACEAjaxXmlRequest.prototype.SelectSingleNode = function( xpath )
{
	if ( document.all )		// IE
		return this.DOMDocument.selectSingleNode( xpath ) ;
	else					// Gecko
	{
		var xPathResult = this.DOMDocument.evaluate( xpath, this.DOMDocument,
				this.DOMDocument.createNSResolver(this.DOMDocument.documentElement), 9, null);

		if ( xPathResult && xPathResult.singleNodeValue )
			return xPathResult.singleNodeValue ;
		else
			return null ;
	}
}

// failsafe reading of singlenode values
// @access private
function readXmlValue(node) {
    if (node != null && node.firstChild != null && node.firstChild.data != null)
        return node.firstChild.data;
    return '';
}

function readXmlBooleanValue(node) {
    s = readXmlValue(node);
    if(s != '' && (s == 'TRUE' || s == 'true'))
        return true;
    return false;
}

// -----------------------------------------------------------------------------
// load the item from the given URL, see classes.util.links.AjaxItemInfoLink

// @access public
function loadItem(requestUrl, asynchronous)
{
    var oXML = new BIGACEAjaxXmlRequest() ;

    if ( asynchronous ) {
        // Asynchronous load.
        oXML.LoadUrl(requestUrl, "_prepareItemFromXml");
    }
    else
    {
        oXML.LoadUrl(requestUrl);
        return _prepareItemFromXml( oXML );
    }
}


// this creates an JS BigaceItem Object from the XML Answer
// @access private
function _prepareItemFromXml(itemXml) {
    if(itemXml != null && itemXml.DOMDocument != null) {
        var item = new BigaceItem();
        item._initFromXml(itemXml);
        return item;
    }
    return null;
}

// -----------------------------------------
// Item definition
// -----------------------------------------

// definition of a BigaceItem - not all possible values from the PHP Class are available
var BigaceItem = function() {
    this.itemtype = '';
    this.id = '';
    this.name = '';
    this.language = '';
    this.description = '';
    this.catchwords = '';
    this.parent = '';
    this.languages = new Array();
    this.leaf = true;
    this.hidden = false;
    this.readable = false;
    this.writeable = false;
    this.deletable = false;
}

// define public getters to access Item values
BigaceItem.prototype.getItemtype = function () { return this.itemtype; }
BigaceItem.prototype.getID = function () { return this.id; }
BigaceItem.prototype.getName = function () { return this.name; }
BigaceItem.prototype.getLanguage = function () { return this.language; }
BigaceItem.prototype.getDescription = function () { return this.description; }
BigaceItem.prototype.getCatchwords = function () { return this.catchwords; }
BigaceItem.prototype.getParent = function () { return this.parent; }
BigaceItem.prototype.getLanguages = function () { return this.languages; }
BigaceItem.prototype.canRead = function () { return this.readable; }
BigaceItem.prototype.canWrite = function () { return this.writeable; }
BigaceItem.prototype.canDelete = function () { return this.deletable; }
BigaceItem.prototype.isHidden = function () { return this.hidden; }
BigaceItem.prototype.isLeaf = function () { return this.leaf; }

// read all values from the xml answer and fill variables
// @access private
BigaceItem.prototype._initFromXml = function (xmlAnswer) {
    this.itemtype = readXmlValue(xmlAnswer.SelectSingleNode('Item/Itemtype'));
    this.id = readXmlValue(xmlAnswer.SelectSingleNode('Item/ID'));
    this.name = readXmlValue(xmlAnswer.SelectSingleNode('Item/Name'));
    this.language = readXmlValue(xmlAnswer.SelectSingleNode('Item/Language'));
    this.description = readXmlValue(xmlAnswer.SelectSingleNode('Item/Description'));
    this.catchwords = readXmlValue(xmlAnswer.SelectSingleNode('Item/Catchwords'));
    this.parent = readXmlValue(xmlAnswer.SelectSingleNode('Item/Parent'));
    this.readable = readXmlBooleanValue(xmlAnswer.SelectSingleNode('Item/Right/Read'));
    this.writeable = readXmlBooleanValue(xmlAnswer.SelectSingleNode('Item/Right/Write'));
    this.deletable = readXmlBooleanValue(xmlAnswer.SelectSingleNode('Item/Right/Delete'));
    this.hidden = readXmlBooleanValue(xmlAnswer.SelectSingleNode('Item/IsHidden'));
    this.leaf = readXmlBooleanValue(xmlAnswer.SelectSingleNode('Item/IsLeaf'));

	var langNodes = xmlAnswer.SelectNodes('Item/Languages/Language') ;
	for ( var i = 0 ; i < langNodes.length ; i++ )
	{
	    this.languages.push(readXmlValue(langNodes[i]));
	}
}

