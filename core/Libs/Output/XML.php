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

	private $_DOM, $_xmlName, $_xmlContent, $_root, $_nodeRoot, $_local, $_rootNS, $_rootAttrs = [];
	
	/*
	*	The root element has a namespace
	*/	
	private $_hasRootNS = false;	

	/*
	*	Default encoding
	*/	
	private $_encode_type = 'UTF-8';

	/*
	*	W3C Standar namespace
	*/
	private $_atom = 'http://www.w3.org/2005/Atom';
	private $_w3c = 'http://www.w3.org/2000/xmlns/';

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

    static function parse($name)
    {
    	$xml = self::init();
		$xml->file($name);
		$xml->load();

		return $xml;
    }

	/**
	*	Create element
	*	@return object
	*/
	private function _addElements(array $obj = [])
	{

		$NS = (isset($obj['ns'])) ? $obj['ns'] : $this->_rootNS;

		if (isset($obj['value'])) :
			if (self::_hasNS($obj['name'])) :
				$element = $this->_DOM->createElementNS($NS,$obj['name'],$obj['value']);
			else:
				$element = $this->_DOM->createElement($obj['name'],$obj['value']);
			endif;	
		else:

			if (self::_hasNS($obj['name'])) :	
				$element = $this->_DOM->createElementNS($NS, $obj['name']);	
			else :
				$element = $this->_DOM->createElement($obj['name']);
			endif;

			if (isset($obj['cdata'])) :
				$cdata = $this->_DOM->createCDATASection($obj['cdata']);
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
						$element->setAttribute($key,$value[0]);
					else:

						if (isset($this->_rootAttrs['xmlns:'.$attr_ns])) :
							$NS = $this->_rootAttrs['xmlns:'.$attr_ns];
						endif;

						$element->setAttributeNS($NS,$key,$value);
					endif;	

				else:
					$element->setAttribute($key,$value);
				endif;	
			endforeach;	
		endif;

		//$element->namespaceURI, "\n"; // Outputs: http://base.google.com/ns/1.0
		//$element->prefix, "\n";       // Outputs: g
		//$element->localName, "\n";    // Outputs: item_type

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

/*
|============================|
|		PUBLIC METHODS
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

		$str = substr($xml, 0, 4);
		$this->_xmlName = self::_ext($xml);

		if (empty($xml)) {
			return;
		}
		
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

    public function read($xml)
    {
    	if (!empty($xml)) {
			$this->file($xml);
			$this->_read();
    	} else {
    		die("Invalid XML.");
    	}
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
	public function output()
	{
		self::header();
		echo $this->_DOM->saveXML();
	}

	/**
	*	Append custom namespace into the root element
	*/
	public function rootNS($name)
	{
		$this->_rootNS = $name;
		$this->_hasRootNS = true;
	}

	/**
	*	Append xmlns atom into the root element
	*/
	public function rootNSAtom()
	{
		$this->rootNS($this->_atom);
	}

	/**
	*	Append xmlns atom into the root element
	*/
	public function rootNSW3c()
	{
		$this->rootNS($this->_w3c);
	}

	/**
	*	Define root node
	*/
	public function root($root = "root", array $attrs = [])
	{

		if (is_array($root) && isset($root[0]) && isset($root[1])) {
			list($root, $rootNS) = $root;
			$this->rootNS($rootNS);
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
						$NS = $this->_w3c;
						if ("xmlns" != $attr_ns) $NS = $this->_rootAttrs['xmlns:'.$attr_ns];
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

    public function getElementByQuery($query)
    {
    	$xpath = new \DOMXPath($this->_DOM);
        $element = $xpath->query($query)->item(0);

        return $element;
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

    public function getLastElementByTagName($node)
    {
    	$total = $this->count($node);
    	$index = ($total > 0) ? $total - 1 : 0;
    	return $this->getElementsByTagName($node)->item($index);
    }

    public function getElementById($id)
    {
    	return $this->getElementByQuery("//*[@id='{$id}']");
    }

    public function getChildNodes($parentNodeName)
    {
    	return $this->getElementsByTagName($parentNodeName)->item(0)->childNodes;
    }

	/**
	*	Create Node
	*/
	public function createNode($obj)
	{
		$element = $this->_addElements($obj);

		return $element;
	}

	/**
	*	Append node into root node
	*/
	public function appendChild($element)
	{
		$this->_root->appendChild($element);
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
	public function prependChild($newNode)
	{
		$rootNodeName = $this->_root->nodeName;

		if (self::_hasNS($rootNodeName)) {
			list($ns, $rootNodeName) = explode(':', $rootNodeName);
		}

		$total = $this->getChildNodes($rootNodeName)->length;

		if ($total > 0) {
			$firstNode = $this->getChildNodes($rootNodeName)[0];
			$this->prependChildNode($firstNode, $newNode);
		} else {
			$this->appendChild($newNode);
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
		$this->removeNode($node, $newNode);
	}

	public function replaceNodeById($id, $newNode)
	{
		$node = $this->getElementById($id);
		$this->prependChildNode($node, $newNode);
		$this->removeNode($node, $newNode);
	}

    public function cdata($value)
    {
    	return $this->_DOM->createCDATASection($value);
    }

    public function cdataSection($node, $value)
    {
    	$node->nodeValue = '';
		$node->appendChild($this->cdata($value));
    }

}
