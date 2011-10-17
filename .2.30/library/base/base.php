<?php
if (! class_exists ( 'bv30v_base' )) :
	abstract class bv30v_base {
		
		protected static $_instance = null;
		public function __construct($application = null) {
			$this->_application = $application;
		}
		private $_application = null;
		public function set_application($application = null) {
			$this->_application = $application;
		}
		public function app($application = null)
		{
			return $this->application($application);
		}
		public function application($application = null) {
			if (null !== $application) {
				$this->_application = $application;
			}
			
			if (null === $this->_application) {
				throw new Exception ( "Application not set \n" );
			}
			return $this->_application;
		}
		public function settings() {
			return $this->application ();
		}
		public function dodebug() {
			return (getenv ( 'DEBUG' ) == 'yes' );
		}
		public $trace = false;
		private function trace() {
			$ret = debug_backtrace ();
			array_shift ( $ret );
			foreach ( $ret as $key => $value ) {
				unset ( $ret [$key] ['object'] );
				unset ( $ret [$key] ['args'] );
			}
			return $ret;
		}
		public function debug() {
			if (! $this->dodebug ()) {
				return;
			}
			$values = func_get_args ();
			$return = '';
			$ret = debug_backtrace ();
			$file = substr ( $ret [0] ['file'], strlen ( $this->application ()->directory () ) + 1 );
			$file .= "\nline: {$ret[0]['line']}]";
			foreach ( $values as $value ) {
				$title = 'Inspect Element...';
				ob_start ();
				var_dump ( $value );
				$got = ob_get_contents ();
				$got = str_replace ( "=>\n", '=>', $got );
				$got = trim ( $got, "\n" );
				while ( strpos ( $got, "=> " ) !== false ) {
					$got = str_replace ( "=> ", "=>", $got );
				}
				if (strpos ( $got, "\n" ) === false && strlen ( $got ) < 45) {
					$title = "... [line:{$ret[0]['line']}]: " . $got;
				}
				ob_end_clean ();
				$line = "-------------------------------------\n";
				$trace='';
				if ($this->trace) {
					$trace = print_r ( $this->trace (), true );
				}
				$class=get_class($this);
				$return .= "<div title='{$title}' class=bv30v_debug>\n\n{$line}class: {$class}\nfile: {$file}\n{$line}{$got}\n{$line}\n<p>{$trace}</p></div>";
			}
			$return = str_replace("\n","<br/>\n",$return);
			echo $return;
		}
	}















endif;