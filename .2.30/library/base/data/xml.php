<?php
class bv30v_data_xml implements Iterator, ArrayAccess, Countable {
  public static function reduce(&$array)
    {
    	if(!isset($array['count']))
    	{
			$new=array();
    		foreach($array as $key=>$value)
			{
				if($value)
				{
					$new[$key]=$key;
				}
			}
			$array=$new;
    	}
    	else
    	{
    		$cnt=0;
			foreach($array as $key=>$value)
			{
				if(is_numeric($key))
				{
					$cnt++;
					if($cnt>$array['count'])
					{
						unset($array[$key]);
					}
				}
			}
    	}
		unset($array['count']);
    }
	public static function merge_replace_recursive() {
	    $params = func_get_args ();
		$arrays = array();
		foreach($params as $param)
		{
			if(is_array($param))
			{
				$arrays[]=$param;
			}
		}
		$return = array_shift($arrays);
		foreach($arrays as $array)
		{
			foreach($array as $key=>$value)
			{
				$overwrite = false;
				if(isset($return['__overwrite__']))
				{
					$overwrite = in_array($key,(array)$return['__overwrite__']);
				}
				if(isset($return[$key]) && is_array($return[$key]) && !$overwrite)
				{
					$return[$key] =self::merge_replace_recursive($return[$key],(array)$value);
				}
				else
				{
					$return[$key]=$value;
				}
			}
		}
		return $return;
	}
	public static function settings($home, $extra_xml = array()) {
		$files = scandir ( $home . '/library' );
		foreach ( $files as $key => $value ) {
			if (! is_dir ( "{$home}/library/{$value}" ) || strpos ( $value, '.' ) === 0) {
				unset ( $files [$key] );
			} else {
				$files [$key] = "{$home}/library/{$value}/settings.xml";
			}
		
		}
		$files [] = "{$home}/application/settings.xml";
		foreach ( $extra_xml as $file ) {
			if (file_exists ( $file )) {
				$files [] = $file;
			} else {
				$files [] = "{$home}/{$file}";
			}
		}
		$xml_data = array ();
		foreach ( $files as $file ) {
			if (file_exists ( $file )) {
				$xml_data [$file] = self::load ( $file );
				if (! isset ( $xml_data [$file] ['application'] ['priority'] )) {
					$xml_data [$file] ['application'] ['priority'] = 2000;
					if ($file == "{$home}/application/settings.xml") {
						$xml_data [$file] ['application'] ['priority'] = 1000;
					}
				}
			}
		}
		uasort ( $xml_data, array ('self', 'sort_xml_data' ) );
		$combined_xml = array ();
		foreach ( $xml_data as $xml_dataum ) {
			$combined_xml = self::merge_replace_recursive ( $combined_xml, $xml_dataum );
		}
		unset ( $combined_xml ['application'] ['priority'] );
		return $combined_xml;
	}
	public static function sort_xml_data($a, $b) {
		if ($a ['application'] ['priority'] == $b ['application'] ['priority']) {
			return 0;
		}
		return ($a ['application'] ['priority'] < $b ['application'] ['priority']) ? - 1 : 1;
	}
	
	public static function load($file) {
		if (! file_exists ( $file )) {
			return false;
		}
		$data = file_get_contents ( $file );
		$xml_parser = xml_parser_create ();
		xml_parser_set_option ( $xml_parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option ( $xml_parser, XML_OPTION_SKIP_WHITE, 0 );
		xml_parse_into_struct ( $xml_parser, $data, $vals, $index );
		xml_parser_free ( $xml_parser );
		foreach ( self::decode ( $vals ) as $return ) {
			return $return->regular ();
		}
	}
	private static function decode($xml) {
		$array = new self ();
		$sub = array ();
		$complete = new self ();
		$tag = null;
		$level = null;
		$id = null;
		foreach ( $xml as $index => $xml_elem ) {
			if ($xml_elem ['type'] == 'open' && is_null ( $level ) && is_null ( $tag )) {
				$tag = $xml_elem ['tag'];
				$level = $xml_elem ['level'];
				$id = $index;
			} elseif ($xml_elem ['type'] == 'close' && $xml_elem ['level'] == $level && $xml_elem ['tag'] = $tag) {
				$data = self::decode ( $sub );
				$complete->true_keys = true;
				foreach ( $complete as $key => $value ) {
					$data [$key] = $value;
				}
				$complete->true_keys = false;
				$array [array ($tag, $id )] = $data;
				$tag = null;
				$level = null;
				$sub = array ();
				$complete = new self ();
			} elseif ($xml_elem ['type'] == 'complete' && $xml_elem ['level'] == $level + 1) {
				if (isset ( $xml_elem ['attributes'] ['xml_key_id'] )) {
					$xml_elem ['tag'] = $xml_elem ['attributes'] ['xml_key_id'];
					unset ( $xml_elem ['attributes'] ['xml_key_id'] );
				}
				if (array_key_exists ( 'value', $xml_elem )) {
					$complete [array ($xml_elem ['tag'], $index )] = $xml_elem ['value'];
				} else {
					$complete [array ($xml_elem ['tag'], $index )] = '';
				}
				if (array_key_exists ( 'attributes', $xml_elem )) {
				}
			} else {
				$sub [$index] = $xml_elem;
			}
		}
		return $array;
	}
	private $values = array ();
	private $keys = array ();
	public $true_keys = false;
	public static function is($value) {
		return (is_object ( $value ) && get_class ( $value ) == __CLASS__);
	}
	public function __construct($array = null) {
		if (null !== $array) {
			foreach ( $array as $key => $value ) {
				$this [$key] = $value;
			}
		}
	}
	private function offset($offset) {
		if (null !== $offset) {
			if (is_array ( $offset )) {
				return serialize ( $offset );
			}
			return $offset;
		}
		return null;
	}
	public function rewind() {
		reset ( $this->keys );
		return reset ( $this->values );
	}
	public function current() {
		return current ( $this->values );
	}
	public function key() {
		if ($this->true_keys) {
			return key ( $this->keys );
		} else {
			return current ( $this->keys );
		}
	}
	public function next() {
		next ( $this->keys );
		return next ( $this->values );
	}
	public function valid() {
		return key ( $this->values ) !== null;
	}
	private function true_key($key) {
		if (is_string ( $key )) {
			$new_key = unserialize ( $key );
			if (false !== $new_key) {
				return $new_key;
			}
		}
		return $key;
	}
	public function offsetSet($offset, $value) {
		$offset = $this->true_key ( $offset );
		$key = $offset;
		$offset = $this->offset ( $offset );
		if (is_array ( $value ) && ! $attribute) {
			$value = new self ( $value );
		}
		if (null === $offset) {
			$this->values [] = $value;
			$ak = array_keys ( $this->values );
			$offset = $ak [count ( $ak ) - 1];
			$key = $offset;
		} else {
			$this->values [$offset] = $value;
		}
		if (is_array ( $key )) {
			$key = $key [0];
		}
		$this->keys [$offset] = $key;
	}
	public function offsetExists($offset) {
		$offset = $this->offset ( $offset );
		return $this->values->offsetExists ( $offset );
	}
	public function offsetUnset($offset) {
		$offset = $this->offset ( $offset );
		$this->values->offsetUnset ( $offest );
	}
	public function offsetGet($offset) {
		$return = null;
		if (is_array ( $offset )) {
			$offset = $this->offset ( $offset );
			$return = $this->values->offsetGet ( $offset );
		} else {
			$keys = array_keys ( $this->keys, $offset, true );
			if (count ( $keys ) == 1) {
				$return = $this->values [$keys [0]];
			} else {
				$return = new self ();
				foreach ( $keys as $key ) {
					$return [] = $this->values [$key];
				}
			}
		}
		return $return;
	}
	public function count() {
		return count ( $this->values );
	}
	public function regular() {
		$keys = array_unique ( $this->keys );
		$return = array ();
		foreach ( $keys as $key ) {
			$value = $this [$key];
			if (self::is ( $value )) {
				$value = $value->regular ();
			}
			$return [$key] = $value;
		}
		return $return;
	}
}