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

	private $_DOM, $_xmlName, $_xmlContent, $_root, $_nodeRoot, $_local, $_NS, $_rootAttrs = [];
	
	/*
	*	The root element has a namespace
	*/	
	private $_ns_root = false;	

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
	*	Http header
	*/
    static function header()
    {
    	header("Content-type: text/xml; charset=utf-8");
    }

	/**
	*	Validates if file extension is set.
	*	@return string
	*/
    static function ext($str)
    {
        $ext = ".xml";
        $name = substr($str, -4);
        if ($name != $ext) return $str . $ext;

        return $str;
    }

	/**
	*	Create element
	*	@return object
	*/
	private function addElements(array $obj = [])
	{

		$NS = (isset($obj['ns'])) ? $obj['ns'] : $this->_NS;

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
	*	Remove child element by xpath query
	*/
    private function _removeNode($query)
    {

    	$this->_update();
    	$xpath = new \DOMXPath($this->_DOM);
        $element = $xpath->query("//*[@".$query."]")->item(0);

        $this->_root->removeChild($element);
    }

/*
|============================|
|		PUBLIC METHODS
|============================|
*/  

	public function load()
	{

		if (!$this->exists()) {
			die("XML does not exist.");
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
		$this->_xmlName = self::ext($xml);
		
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

    /**
     *
     */
    public function read()
    {
		
		if (!$this->exists()) {
			die("XML does not exist.");
		}

    	self::header();
    	readfile($this->_xmlName);
    }

    public function expose($xml)
    {
		$this->file($xml);
		$this->read();
    }

	/**
	*	Save xml 
	*/
	public function save($overwrite = false)
	{
		$this->_DOM->save($this->_xmlName);
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
		$this->_NS = $name;
		$this->_ns_root = true;
	}

	/**
	*	Append xmlns atom into the root element
	*/
	public function rootNSAtom()
	{
		$this->rootNS($this->_atom);
	}

	/**
	*	Define root node
	*/
	public function root($root = "root", array $attrs = [])
	{

		$this->_nodeRoot = $root;
		
		// if xml does not exist
		if (!$this->exists()) :
			
			// load DOMDocument
			$this->_init();

			// create an element
			$element = ($this->_ns_root) ? $this->_DOM->createElementNS($this->_NS,$root) : $this->_DOM->createElement($root);
			
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

	/**
	*
	*/
	public function prependChild($node, $newNode)
	{
		// Insert the new element 
		$node->parentNode->insertBefore($newNode, $node); 

	}

	public function removeNode($node)
	{
		$node->parentNode->removeChild($node);
	}

	public function replaceNode($node, $newNode)
	{
		$this->prependChild($node, $newNode);
		$this->removeNode($node, $newNode);
	}

	/**
	*	Count root's nodes
	*/
 	public function count($node)
 	{
 		return $this->_DOM->getElementsByTagName($node)->length;
 	}

	/**
	*	Get elements by node given
	*/
    public function getElementByTagName($node)
    {
    	return $this->_DOM->getElementsByTagName($node); // ->item($index);
    }

	/**
	*	Get elements by node given
	*/
    public function getElementByTagNameNS($NS, $node)
    {
    	return $this->_DOM->getElementsByTagNameNS($NS, $node); // ->item($index);
    }

    public function getLastElementByTagName($node)
    {
    	$index = $this->count($node) - 1;
    	return $this->getElementByTagName($node)->item($index);
    }

    public function getElementById($id)
    {
    	return $this->_DOM->getElementById($id)->item(0);
    }

    public function getElementByQuery($query)
    {
    	$xpath = new \DOMXPath($this->_DOM);
        $element = $xpath->query($query)->item(0);

        return $element;
    }

	/**
	*	Create Node
	*/
	public function createNode($obj)
	{
		$element = $this->addElements($obj);

		return $element;
	}

	/**
	*	Append node into root node
	*/
	public function appendChild($element)
	{
		$this->_root->appendChild($element);
	}

}
