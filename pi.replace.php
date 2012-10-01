<?php
class Plugin_replace extends Plugin {

  var $meta = array(
    'name'       => 'Regular Expression Replacement Plugin',
    'version'    => '0.3',
    'author'     => 'Ian Pitts',
    'author_url' => 'http://iso-100.com'
    // NOTE: This code copied and modified from Low's low_replace addon
    // for ExpressionEngine 2 found at:
    // https://github.com/lodewijk/low.replace.ee2_addon/blob/master/low_replace/pi.low_replace.php
  );

  public function index() {
    $caseinsens       = ($this->fetch_param('casesensitive') == 'no');
    //$multiple         = ($this->fetch_param('multiple') == 'yes');
    $regex            = ($this->fetch_param('regex') == 'yes');
    $flags            = $this->fetch_param('flags');
    $needle           = $this->fetch_param('find');
    $replace          = $this->fetch_param('replace');

    $this->content = $this->parser->parse($this->content, Statamic_View::$_dataStore, 'Statamic_View::callback');

    $haystack = $this->content;

    // clean up in aisle param
    $dirty    = array('SPACE', 'QUOTE', 'NEWLINE');
    $clean    = array(' ', '"', "\n");
    $needle   = str_replace($dirty, $clean, $needle);
    $replace  = str_replace($dirty, $clean, $replace);

    // for regex replacements
    if ($regex) {

    	// if multiple, explode on pipe character
    	//if ($multiple) {
    	//	$needles = explode('|', $needle);
			//	$replacements = explode('|', $replace);
    	//} else // no explosions needed
      	$needles = array($needle);
      	$replacements = array($replace);
    	//}

      // Replace PIPE with |
      $needles = $this->_replace_pipe($needles);
      $replacements = $this->_replace_pipe($replacements);

      // loop through needles and replace
      foreach ($needles AS $i => $nee)
      {
        // prep needle first
        $nee = $this->_prep_regex($nee, $caseinsens, $flags);

        // If there isn't a paired replacement, use empty string
        $rep = isset($replacements[$i]) ? $replacements[$i] : '';

        // replace the haystack
        $haystack = preg_replace($nee, $rep, $haystack);
      }

      // Return haystack
      $this->content = $haystack;

    } else {

    	//if ($multiple) {
    		// convert needle to array
				//$needle  = explode('|', $needle);

				// Replace PIPE with |
				//$needle = $this->_replace_pipe($needle);

				// convert replace to array if vertical bar is found
				//$replace = (substr_count($replace,'|') == 0) ? $replace : explode('|', $replace);
    	//}

      // Normal String Replace
      $function = ($caseinsens) ? 'str_ireplace' : 'str_replace';
      $this->content = $function($needle, $replace, $haystack);
    }

    // Send the content back to Statamic's parser
    return $this->content;
  }

  // --------------------------------------------------------------------
  /********************************************
  * Prep string for regular expression pattern
  *
  * @access private
  * @param  string
  * @param  bool
  * @return string
  */
  public function _prep_regex($str, $caseinsens = FALSE, $flags = FALSE)
  {
    // check needle for first and last character
    if (substr($str,0,1)  != '/') { $str  = '/'.$str; }
    if (substr($str,-1,1) != '/') { $str .= '/'; }

    // add case insensitive flag
    if ($flags) { $str .= str_replace('i', '', $flags); }
    if ($caseinsens) { $str .= 'i'; }

    return $str;
  }

  // --------------------------------------------------------------------
  /****************************
  * Replace pipe key character
  *
  * @access private
  * @param  mixed
  * @return mixed
  */
  public function _replace_pipe($str)
  {
    $key = 'PIPE';
    $val = '|';

    if (is_array($str))
    {
      foreach ($str AS &$item)
      {
        $item = str_replace($key, $val, $item);
      }
    }
    else
    {
      $str = str_replace($key, $val, $str);
    }

    return $str;
  }

  /*
  public function words() {
    $limit  = $this->fetch_param('limit', null);
    $ending = $this->fetch_param('ending', '...');

    $this->content = $this->parser->parse($this->content, Statamic_View::$_dataStore, 'Statamic_View::callback');

    $words = preg_split("/[\n\r\t ]+/", $this->content, $limit + 1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);
    if (count($words) > $limit) {
      end($words);
      $last_word = prev($words);
         
      $this->content =  substr($this->content, 0, $last_word[1] + strlen($last_word[0])) . $ending;
    }
    return $this->content;
  }*/
    
}