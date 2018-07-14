<?php
/**
 *
 * This file is part of Roducks.
 *
 *    Roducks is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    Roducks is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with Roducks.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Roducks\Libs\Output;

class XML
{

/*
|============================|
|		PRIVATE METHODS
|============================|
*/

	const OVERWRITE = true;

	private $_DOM, $_xmlContent, $_root, $_nodeRoot, $_local, $_rootNS, $_rootAttrs = [];

	/**
	 *	XML name
	 */
	private $_xmlName = '';

	/**
	 *	The root element has a namespace
	 */
	private $_hasRootNS = false;

	/**
 	 *	Default encoding
	 */
	private $_encode_type = 'UTF-8';

	/**
	 *	W3C Standar namespace
	 */
	private $_atom = 'http://www.w3.org/2005/Atom';
	private $_w3c = 'http://www.w3.org/2000/xmlns';

	/*
	|============================|
	|			STATIC
	|============================|
	*/

	/**
	 *	find if an element has a namespace
	 *	@return bool
	 */
	static private function _hasNS($key)
	{
		if (preg_match('#:#', $key)) return true;

		return false;
	}

	/**
	*	Validates if file extension is set.
	*	@return string
	*/
    static private function _ext($str)
    {
        $ext = ".xml";
        $name = substr($str, -4);
        if ($name != $ext) return $str . $ext;

        return $str;
    }

    static private function _notFound()
    {
    	header("HTTP/1.1 404 Not Found");
			die("XML does not exist.");
    }

	/**
	*	Http header
	*/
    static function header()
    {
    	header("Content-type: text/xml; charset=utf-8");
    }

    static function init()
    {
    	return new XML();
    }

    static function create($name, $overwrite = false)
    {
    	$xml = self::init();
		$xml->file($name);

		if ($overwrite) {
			$xml->overwrite();
		}

		return $xml;
    }

    static function preview()
    {
    	return self::create(null, false);
    }

    static function parse($name, $overwrite = false)
    {
    	$xml = self::create($name);
		$xml->load();

		$ns = $xml->content()->getNamespaces(true);

		foreach ($ns as $key => $value) {
			$xml->setRootAttrs("xmlns:{$key}", $value);
		}

		if ($overwrite) {
			$xml->overwrite();
		}

		return $xml;
    }

   	/*
	|============================|
	|		    PRIVATE
	|============================|
	*/

    private function _cdata($value)
    {
    	return $this->_DOM->createCDATASection($value);
    }

	/**
	*	Create element
	*	@return object
	*/
	private function _createNode(array $obj = [])
	{

		$NS = $this->_rootNS;
		$name = $obj['name'];

		if (is_array($name)) :
			list($name, $NS) = $obj['name'];
		endif;

		if (isset($obj['value'])) :
			if (self::_hasNS($name)) :

				list($attr_ns, $attr_key) = explode(":", $name);

				if (isset($this->_rootAttrs["xmlns:{$attr_ns}"])) :
					$element = $this->_DOM->createElement($name, $obj['value']);
				else :
					$element = $this->_DOM->createElementNS($NS, $name, $obj['value']);
				endif;
			else:
				$element = $this->_DOM->createElement($name, $obj['value']);
			endif;
		else:

			if (self::_hasNS($name)) :
				list($attr_ns, $attr_key) = explode(":", $name);

				if (isset($this->_rootAttrs["xmlns:{$attr_ns}"])) :
					$element = $this->_DOM->createElement($name);
				else :
					$element = $this->_DOM->createElementNS($NS, $name);
				endif;

			else :
				$element = $this->_DOM->createElement($name);
			endif;

			if (isset($obj['cdata'])) :
				$cdata = $this->_cdata($obj['cdata']);
				$element->appendChild($cdata);
			endif;

		endif;

		// append attributes to this element
		if (isset($obj['attributes'])) :
			foreach ($obj['attributes'] as $key => $value) :
				if (self::_hasNS($key)) :
					// if attribute has its own namespace
					list($attr_ns, $attr_key) = explode(":", $key);

					if (is_array($value)) :
						$element->setAttributeNS($value[1],$key,$value[0]);
					else:

						if (isset($this->_rootAttrs["xmlns:{$attr_ns}"])) :
							$element->setAttribute($key,$value);
						else :
							$element->setAttributeNS($NS,$key,$value);
						endif;
					endif;

				else:
					$element->setAttribute($key,$value);
				endif;
			endforeach;
		endif;

		//$element->namespaceURI
		//$element->prefix
		//$element->localName

		return $element;

	}

	/**
	*	DOMDocument instance
	*/
	private function _init()
	{
		$this->_DOM = new \DOMDocument('1.0', $this->_encode_type);
		$this->_DOM->preserveWhiteSpace = false;
		$this->_DOM->formatOutput = true;
	}

	/**
	*	Update existing xml
	*/
	private function _update()
	{
		$this->_init();
		$this->_DOM->load($this->_xmlName);
		$this->_root = $this->_DOM->documentElement;
	}

    /**
     *
     */
    private function _read()
    {

		if (!$this->exists()) {
			self::_notFound();
		}

    	self::header();
    	readfile($this->_xmlName);
    }

	/**
	*	Append custom namespace into the root element
	*/
	private function _setRootNS($name)
	{
		$this->_rootNS = $name;
		$this->_hasRootNS = true;
	}

    private function _getChildNodes($parentNodeName, $node = null)
    {

    	if (is_null($node)) {
    		return $this->getElementsByTagName($parentNodeName)->item(0)->childNodes;
    	}

    	$nodes = [];

    	if ($node instanceof \DOMElement) {
    		if ($node->tagName == $parentNodeName) {
	    		foreach ($node->childNodes as $child) {
	    			$nodes[] = $child;
	    		}
    		}
    	}

    	return $nodes;

    }

    private function _getNode(\DOMElement $node, array $items = [], $type)
    {

    	switch ($type) {
    		case 1:
    			$items[$node->tagName][] = $node;
    			break;

    		case 2:
    			$items[] = $node;
    			break;
    	}

    	return $items;
    }

	private function _getChildNodesByPath($path, $domElement = null, array $items = [])
	{

		if (preg_match('/\//', $path)) {

			$tags = explode('/', $path);
			$parentTag = $tags[0];
			$child = $tags[1];

			unset($tags[0]);
			unset($tags[1]);

			$route = implode('/', $tags);

			foreach ($this->_getChildNodes($parentTag, $domElement) as $nodes) {

				foreach ($this->_getChildNodes($child, $nodes) as $node) {

					if (!empty($route)) {
						$items = $this->_getChildNodesByPath($route, $node, $items);
					} else {
						if ($node instanceof \DOMElement) {
							$items = $this->_getNode($node, $items, 1);
						} else {
							$items = [];
						}
					}
				}
			}

		} else {
			if ($domElement instanceof \DOMElement) {

				foreach ($this->_getChildNodes($path, $domElement) as $node) {
					$items = $this->_getNode($node, $items, 2);
				}

			} else {

				foreach ($this->_getChildNodes($path) as $node) {
					$items[$node->tagName] = $node;
				}
			}
		}

		return $items;

	}

	/*
	|============================|
	|			PUBLIC
	|============================|
	*/

	public function load()
	{

		if (!$this->exists()) {
			self::_notFound();
		}

		$this->_update();
	}

	/**
	*	@param: $type string
	*/
	public function encode($type) {

		$this->_encode_type = $type;
	}

	/**
	*	File will be overwritten every time it's execute it
	*/
	public function overwrite()
	{
		if ($this->exists()) {
			@unlink($this->_xmlName);
		}
	}

	/**
	*	Validates if xml exists
	*/
    public function exists()
    {
    	return file_exists($this->_xmlName);
    }

    /**
    *	@param $xml string
    */
	public function file($xml)
	{

		if (empty($xml)) {
			return;
		}

		$str = substr($xml, 0, 4);
		$this->_xmlName = self::_ext($xml);

		if ($this->exists()) {
			$this->_xmlContent = file_get_contents($this->_xmlName);
		}

	}

	/**
	*	@return object
	*/
	public function content()
	{

		if (!$this->exists()) {
			die("Invalid XML.");
		}

		return new \SimpleXmlElement($this->_xmlContent);

	}

    public function read()
    {
		$this->file($this->_xmlName);
		$this->_read();
    }

	/**
	*	Save xml
	*/
	public function save()
	{
		if (!empty($this->_xmlName)) {
			$this->_DOM->save($this->_xmlName);
		}
	}

	/**
	*	print xml output in browser
	*/
	public function output($clean = false)
	{
		self::header();
		if ($clean) {
			$xml = $this->_DOM->saveXML();

			$this->_init();
			$this->_DOM->loadXML($xml, LIBXML_NSCLEAN);
		}

		echo $this->_DOM->saveXML();
	}

	public function setRootAttrs($key, $value)
	{
		$this->_rootAttrs[$key] = $value;
	}

	/**
	*	Define root node
	*/
	public function root($root = "xml", array $attrs = [])
	{

		if (is_array($root) && isset($root[0]) && isset($root[1])) {
			list($root, $rootNS) = $root;
			$this->_setRootNS($rootNS);
		}

		$this->_nodeRoot = $root;

		// if xml does not exist
		if (!$this->exists()) :

			// load DOMDocument
			$this->_init();

			// create an element
			$element = ($this->_hasRootNS) ? $this->_DOM->createElementNS($this->_rootNS,$root) : $this->_DOM->createElement($root);

			// root node
			$this->_root = $this->_DOM->appendChild($element);

			// add attributes or namespaces into the root
			if (count($attrs) > 0) :

				$this->_rootAttrs = $attrs;

				foreach ($attrs as $key => $value) :
					if (self::_hasNS($key)) :
						list($attr_ns, $attr_key) = explode(":", $key);

						$NS = $this->_w3c."/";

						if ("xmlns" != $attr_ns) :
							if (isset($this->_rootAttrs["xmlns:{$attr_ns}"])) :
								$NS = $this->_rootAttrs["xmlns:{$attr_ns}"];
							endif;
						endif;
						$element->setAttributeNS($NS, $key, $value);
					else:
						$element->setAttribute($key,$value);
					endif;

				endforeach;
			endif;

		else:
			// update xml data for an existing xml
			$this->_update();
		endif;
	}

    public function getElementsByQuery($query)
    {
    	$xpath = new \DOMXPath($this->_DOM);
        return $xpath->query($query);
    }

    public function getElementByQuery($query)
    {
    	return $this->getElementsByQuery($query)->item(0);
    }

	/**
	*	Get elements by node given
	*/
    public function getElementsByTagName($nodeName)
    {
    	return $this->_DOM->getElementsByTagName($nodeName); // ->item($index);
    }

    /**
     * Returns the first node
     */
    public function getElementByTagName($nodeName)
    {
    	return $this->getElementsByTagName($nodeName)->item(0);
    }

	/**
	*	Count nodes
	*/
 	public function count($nodeName)
 	{
 		return $this->getElementsByTagName($nodeName)->length;
 	}

    public function getLastElementByTagName($nodeName)
    {
    	$total = $this->count($nodeName);
    	$index = ($total > 1) ? $total - 1 : 0;
    	return $this->getElementsByTagName($nodeName)->item($index);
    }

    public function getElementById($id)
    {
    	return $this->getElementByQuery("//*[@id='{$id}']");
    }

	public function getChildNodes($path)
	{
		return $this->_getChildNodesByPath($path);
	}

	/**
	*	Create Node
	*/
	public function createNode($obj)
	{
		return $this->_createNode($obj);
	}

	/**
	*	Append node into root node
	*/
	public function appendChild($node)
	{
		$this->_root->appendChild($node);
	}

	/**
	*	Prepend node
	*/
	public function prependChildNode($node, $newNode)
	{
		$node->parentNode->insertBefore($newNode, $node);
	}

	/**
	*	Prepend node in root
	*/
	public function prependChild($node)
	{
		$rootNodeName = $this->_root->nodeName;

		if (self::_hasNS($rootNodeName)) {
			list($ns, $rootNodeName) = explode(':', $rootNodeName);
		}

		$total = $this->_getChildNodes($rootNodeName)->length;

		if ($total > 0) {
			$firstNode = $this->_getChildNodes($rootNodeName)[0];
			$this->prependChildNode($firstNode, $node);
		} else {
			$this->appendChild($node);
		}

	}

	public function removeNode($node)
	{
		if ($node->nodeName != $this->_root->nodeName) {
			$node->parentNode->removeChild($node);
		}
	}

	public function removeNodeById($id)
	{
		$node = $this->getElementById($id);
		$this->removeNode($node);
	}

	public function replaceNode($node, $newNode)
	{
		$this->prependChildNode($node, $newNode);
		$this->removeNode($node);
	}

	public function replaceNodeById($id, $newNode)
	{
		$node = $this->getElementById($id);
		$this->prependChildNode($node, $newNode);
		$this->removeNode($node);
	}

    public function cdataSection($node, $value)
    {
    	$node->nodeValue = '';
		$node->appendChild($this->_cdata($value));
    }

}
