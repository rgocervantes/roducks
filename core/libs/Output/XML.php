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

namespace rdks\core\libs\Output;

class XML{

/*
|============================|
|		PRIVATE METHODS
|============================|
*/	

	protected $DOM, $xmlName, $xmlContent, $element, $root, $nodeRoot, $local, $NS, $rootAttrs = array();
	
	/*
	*	The root element has a namespace
	*/	
	protected $is_ns = false;	

	/*
	*	The root element has a namespace
	*/	
	protected $ns_root = false;	

	/*
	*	Default encoding
	*/	
	protected $encode_type = 'UTF-8';

	/*
	*	W3C Standar namespace
	*/
	protected $atom = 'http://www.w3.org/2005/Atom';
	protected $w3c = 'http://www.w3.org/2000/xmlns/';


	/**
	*	Http header
	*/
    static function header(){
    	header("Content-type: text/xml; charset=utf-8");
    }

    /**
     *
     */
    static function read($xml){
    	if(!file_exists($xml)) return;
    	self::header();
    	readfile($xml);
    }

	/**
	*	Validates if file extension is set.
	*	@return string
	*/
    static function ext($str){
        $ext = ".xml";
        $name = substr($str, -4);
        if($name != $ext) return $str . $ext;

        return $str;
    }

	/**
	*	Create element
	*	@return object
	*/
	private function addElements($obj = array()){

		$NS = (isset($obj['ns'])) ? $obj['ns'] : $this->NS;

		if(isset($obj['value'])):
			if($this->has_ns($obj['name'])):
				$element = $this->DOM->createElementNS($NS,$obj['name'],$obj['value']);
			else:
				$element = $this->DOM->createElement($obj['name'],$obj['value']);
			endif;	
		else:
			
			if($this->has_ns($obj['name'])):	
				$element = $this->DOM->createElementNS($NS,$obj['name']);
			else:
				$element = $this->DOM->createElement($obj['name']);
			endif;	

			if(isset($obj['cdata'])):
				$cdata = $this->DOM->createCDATASection($obj['cdata']);
				$element->appendChild($cdata);
			endif;

		endif;

		// append attributes to this element
		if(isset($obj['attributes'])):
			foreach($obj['attributes'] as $key => $value):
				if($this->has_ns($key)):
					// if attribute has its own namespace
					list($attr_ns, $attr_key) = explode(":", $key);

					if($attr_ns != $NS):
						if(isset($this->rootAttrs['xmlns:'.$attr_ns])):
							$NS = $this->rootAttrs['xmlns:'.$attr_ns];
						endif;	
					endif;	

					if(is_array($value)):
						$element->setAttributeNS($value[1],$key,$value[0]);
					else:
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
	*	find if an element has a namespace
	*	@return bool
	*/
	private function has_ns($key){
		if(preg_match('#:#', $key)) return true;

		return false;
	}

	/**
	*	Remove child element by xpath query
	*/
    private function _removeNode($query){

    	$this->load();
    	$xpath = new \DOMXPath($this->DOM);
        $element = $xpath->query("//*[@".$query."]")->item(0);

        $dom = $this->DOM->documentElement;
        $dom->removeChild($element);
    }

/*
|============================|
|		PUBLIC METHODS
|============================|
*/  

	/**
	*	@param: $type string
	*/
	public function encode($type){
		$this->encode_type = $type;
	}

	/**
	*	File will be overwritten every time it's execute it
	*/
	public function overWrite(){
		if($this->exists()){
			@unlink($this->xmlName);
		}
	}

	/**
	*	Validates if xml exists
	*/
    public function exists(){
    	return file_exists($this->xmlName);
    }

    /**
    *	@param $xml string
    */
	public function file($xml){

		$str = substr($xml, 0, 4);
		$this->xmlName = self::ext($xml);

		if($str == "http" || $str != "<?xm"){
			$this->local = true;
			$this->xmlContent = ($this->is_ns) ? file_get_contents($this->xmlName) : $this->xmlName;
		}else{
			$this->xmlContent = file_get_contents($this->xmlName);
		}

	}	

	public function nameSpaces(){
		$this->is_ns = true;
	}

	/**
	*	DOMDocument instance
	*/
	public function init(){
		$this->DOM = new \DOMDocument('1.0', $this->encode_type);
		$this->DOM->preserveWhiteSpace = false;
		$this->DOM->formatOutput = true;
	}	

	/**
	*	@return object
	*/
	public function content(){

		if(!$this->exists()){
			die("Invalid XML.");
		}		

		if($this->is_ns){
			return new \SimpleXmlElement($this->xmlContent);
		}

		if($this->local){
			return simplexml_load_file($this->xmlContent);
		}else{
			return simplexml_load_string($this->xmlContent);
		}		
	}	

	/**
	*	Load xml in DOM
	*/
	public function load(){
		$this->init();
		$this->DOM->load($this->xmlName);
	}

	/**
	*	Save xml 
	*/
	public function save(){
		$this->DOM->save($this->xmlName);
	}

	/**
	*	print xml output in browser
	*/
	public function output(){
		self::header();
		echo $this->DOM->saveXML();
	}	

	/**
	*	Append xmlns atom into the root element
	*/
	public function namespaceRootAtom(){
		$this->namespaceRoot($this->atom);
	}

	/**
	*	Append custom namespace into the root element
	*/
	public function namespaceRoot($name){
		$this->NS = $name;
		$this->ns_root = true;
	}

	/**
	*	Define root node
	*/
	public function root($root = "root", $attrs = array()){

		$this->nodeRoot = $root;
		
		// if xml does not exist
		if(!$this->exists()):
			
			// load DOMDocument
			$this->init();

			// create an element
			$element = ($this->ns_root) ? $this->DOM->createElementNS($this->NS,$root) : $this->DOM->createElement($root);
			
			// root node
			$this->root = $this->DOM->appendChild($element);
			
			// add attributes or namespaces into the root
			if(count($attrs) > 0):

				$this->rootAttrs = $attrs;

				foreach($attrs as $key => $value):
					if($this->has_ns($key)):
						list($attr_ns, $attr_key) = explode(":", $key);
						$NS = $this->w3c;
						if("xmlns" != $attr_ns) $NS = $this->rootAttrs['xmlns:'.$attr_ns];
						$element->setAttributeNS($NS, $key, $value);
					else:
						$element->setAttribute($key,$value);
					endif;	
					
				endforeach;	
			endif;	
		else:
			// update xml data for an existing xml
			$this->update();
		endif;	
	}

	/**
	*	Update existing xml
	*/
	public function update(){
		$this->load();
		$this->root = $this->DOM->documentElement;
	}

	/**
	*	Remove node by search query
	*/
	public function removeNodeBySearch($query){
		$this->_removeNode("//*[@".$query."]");
	}

	/**
	*	Remove node by id (if exists)
	*/
 	public function removeNodeById($id)
    {
    	$this->_removeNode("id='$id'");
    }

	/**
	*	Remove child element by xpath query
	*/
    public function removeNode($query, $x = "", $ns = ""){

    	$xpath = new \DOMXpath($this->DOM);

		if(!empty($x) && !empty($ns)){
			$xpath->registerNamespace($x, $ns);
		}

        $element = $xpath->query("//" . $this->nodeRoot . $query)->item(0);
        $element->parentNode->removeChild($element);
    }

	/**
	*
	*/
	public function insertBefore($addPath, $beforeNode, $addNode, $x = "", $ns = ""){
		
		// XPath-Querys 
		$parent_path = "//" . $this->nodeRoot . $addPath; 

		// Instance
		$xpath = new \DOMXpath($this->DOM); 

		if(!empty($x) && !empty($ns)){
			$xpath->registerNamespace($x, $ns);
		}
		 
		// Find parent node 
		$parent = $xpath->query($parent_path); 

		// new node will be inserted before this node 
		$next = $xpath->query($parent_path . $beforeNode); 		
		 
		// Insert the new element 
		$parent->item(0)->insertBefore($addNode, $next->item(0)); 

	}

	/**
	*	Get elements by node given
	*/
    public function getElementByTagName($node, $index = 0){
    	return $this->DOM->getElementsByTagName($node)->item($index);
    }

	/**
	*	Get element by id search
	*/
    public function getElementByIdSearch($id){
    	
    	$xpath = new \DOMXPath($this->DOM);
        $element = $xpath->query("//*[@id='".$id."']")->item(0);

        return $element;
    }  

	/**
	*	Count root's nodes
	*/
 	public function count($node){
 		return $this->DOM->getElementsByTagName($node)->length;
 	} 	

	/**
	*	Create Node
	*/
	public function createNode($obj){
		$element = $this->addElements($obj);

		return $element;
	}

	/**
	*	Append node into root node
	*/
	public function appendChild($element){
		$this->root->appendChild($element);
	}


}


?>