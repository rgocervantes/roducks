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

namespace Roducks\Page;

use Roducks\Framework\UI;
use Roducks\Framework\Error;
use Roducks\Framework\Environment;
use Request;
use Helper;
use Path;
use URL;

class Duckling
{

  private static function _rules($type, $args)
  {
    switch ($type) {
      case 'format':
        $regexp = '/{{% ([a-z_|]+[|])?\$'.$args[0].'(->[a-zA-Z_]+)?(\[[a-zA-Z0-9_\']+\])?([,|]\s?[a-zA-Z0-9_\-\/,|\':*\s]+)? %}}/sm';
        break;
      case 'isset_empty':
        $regexp = '/{{% @if (!)?(isset|empty) \$('.$args[0].')(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])? %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm';
        break;
    }

    return $regexp;
  }

  private static function _clean($tpl)
  {
    $tpl = preg_replace('/^\n/', "", $tpl);
    $tpl = preg_replace('/(\n\n|\s\s+\n)/', "\n", $tpl);

    return $tpl;
  }

  private static function _var($condition, $v)
  {

    if (is_array($v)) {
      $i = str_replace(['[',']',"'"], '', $condition[3]);

      if (!isset($v[$i])) {
        return '';
      }

      $var = $v[$i];
    } else if (is_object($v)) {
      $i = str_replace(['->'], '', $condition[2]);

      if (!isset($v->$i)) {
        return '';
      }

      $var = $v->$i;
    } else {
      $var = $v;
    }

    return $var;
  }

  private static function _php($func, $var, $commas = "")
  {
    $params = [];

    if (preg_match('/^\d+$/', $var)) {
      $var = intval($var);
    }

    array_push($params, $var);

    if (!empty($commas)) {
      $extra = (substr($commas, 0, 1) == ',') ? substr($commas, 1) : $commas;
      $args = explode(',', $extra);

      if (preg_match('#[*]#', $extra)) {
        $params = [];

        foreach ($args as $arg) {
          if (trim($arg) == '*') {
            array_push($params, $var);
          } else {
            if (preg_match('/^\d+$/', $arg)) {
              $arg = intval($arg);
            }
            array_push($params, $arg);
          }
        }

      } else {
        foreach ($args as $arg) {
          if (preg_match('/^\d+$/', $arg)) {
            $arg = intval($arg);
          }
          array_push($params, $arg);
        }
      }

    }

    if (function_exists($func)) {
      return call_user_func_array($func, $params);
    }

    return ($func == 'hide') ? '' : $var;
  }

  private static function _format($matches2, $value)
  {

    $var = self::_var($matches2, $value);

    if (!empty($matches2[1])) {
      // strtoupper|substr|$name,0,3
      // substr|date|$content->date,Y-m-d H:i:s,*|0,4
      $pipes = explode('|', $matches2[1]);
      $commas = (!empty($matches2[4])) ? explode('|', $matches2[4]) : '';

      $functions = array_reverse($pipes);
      unset($functions[0]);
      $functions = array_merge(array(), $functions);

      foreach ($functions as $f => $func) {
        $params = (isset($commas[$f])) ? $commas[$f] : '';
        $var = self::_php($func, $var, $params);
      }
    }

    return $var;
  }

  private static function _query($tpl, $key, $query)
  {
    $tpl = preg_replace_callback('/{{% @query \$([a-zA-Z_]+) in \$('.$key.') %}}(.*?){{% @endquery %}}/sm', function ($matches) use($query) {

      $k = 0;
      $loop = "";
      $content = $matches[3];

      while($row = $query->fetch('object')):

        $loop .= preg_replace_callback(Duckling::_rules('format', [$matches[1]]), function ($m) use($row) {
          return Duckling::_format($m, $row);
        }, $content);

        $loop = preg_replace_callback('/{{% @if \$([a-zA-z_]+)(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])? ([=<>!]+) ([a-zA-Z0-9_\']+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($k, $row) {
          return Duckling::_condition($condition, $k, $row);
        }, $loop);

        $loop = preg_replace_callback('/{{% \$i(\s\+\s(?P<INC>\d+))? %}}/', function ($i) use (&$k) {
          if (isset($i['INC'])) {
            return $k + intval($i['INC']);
          }
          return $k;
        }, $loop);

        $k++;
      endwhile;

      return $loop;

    }, $tpl);

    return $tpl;
  }

  private static function _functions($tpl)
  {
    $tpl = preg_replace_callback('/{{% @if ([!])?([a-z_]+)\(\) \s*%}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($functions) {

      $condition = false;

      switch ($functions[2]) {
        case 'super_admin':
          $condition = true;
          break;
        case 'logged_in':
          $condition = true;
          break;
      }

      if ($condition) {
        return $functions[3];
      } else {
        return (isset($functions[5])) ? $functions[5] : '';
      }

    }, $tpl);

    return $tpl;

  }

  private static function _aggregator($tpl, $key, $value)
  {

    $tpl = preg_replace_callback('/{{% (\$i [+\-]) \$'.$key.'(->[a-zA-Z_]+)?(\[[a-zA-Z0-9_\']+\])? %}}/', function($dis) use ($value){
      $var = Duckling::_var($dis, $value);
      if (!is_integer($var)) {
        $var = 0;
      }
      return '{{% '.$dis[1].' '.$var.' %}}';
    }, $tpl);

    $tpl = preg_replace_callback('/([=<>!]+) \$('.$key.')(->[a-zA-Z_]+)?(\[[a-zA-Z0-9_\']+\])?/', function($dis) use ($value){
      $var = Duckling::_var($dis, $value);
      return $dis[1]." ".$var;
    }, $tpl);

    return $tpl;
  }

  private static function _return($content, $return)
  {
    if ($return) {
      return true;
    }

    return $content;
  }

  private static function _multipleCondition($row, $k, $v)
  {

    return preg_replace_callback('/{{% @if ([a-zA-Z_0-9\-$=<>!\s\']+)(([&|]+[a-zA-Z_0-9\-$=<>!\s\']+)+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($k, $v) {
      $type = 'none';
      $total = 0;
      $conds = [];
      $statment = $condition[1]." ".$condition[2];

      if (preg_match('/&&/', $statment) && !preg_match('/[|]+/', $statment)) {
        $conds = explode(" && ", $statment);
        $type = 'and';
      } else if(preg_match('/[|]+/', $statment) && !preg_match('/&&/', $statment)) {
        $conds = explode(" || ", $statment);
        $type = 'or';
      }

      foreach ($conds as $cond) {
        preg_replace_callback('/\$([a-zA-z_]+)(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])? ([=<>!]+) ([a-zA-Z0-9_\']+)/', function($cnd) use (&$total, $condition, $k, $v) {
          array_push($cnd, "empty");
          $return = Duckling::_condition($cnd, $k, $v, true);
          if ($return) {
            $total++;
          }
        }, $cond);
      }

      switch ($type) {
        case 'and':
            if (count($conds) == $total) {
              return $condition[4];
            } else {
              if (isset($condition[6])) {
                return $condition[6];
              }
            }
          break;
        case 'or':
            if ($total > 0) {
              return $condition[4];
            } else {
              if (isset($condition[6])) {
                return $condition[6];
              }
            }
          break;
      }

      return "";

    }, $row);

  }

  private static function _condition($condition, $k, $v, $return = false)
  {

    if ($condition[1] == 'i') {
      $v = $k;
    }

    $_value = str_replace("'", '', $condition[5]);
    $var = self::_var($condition, $v);

    switch ($condition[4]) {
      case '==':

          if ($_value == 'true') {
            $_value = true;
          } else if($_value == 'false') {
            $_value = false;
          }

          if ($var == $_value) {
            return self::_return($condition[6], $return);
          } else {
            return (isset($condition[8])) ? self::_return($condition[8], $return) : '';
          }

        break;
      case '>':

          if ($var > $_value) {
            return self::_return($condition[6], $return);
          } else {
            return (isset($condition[8])) ? self::_return($condition[8], $return) : '';
          }

        break;
      case '>=':

          if ($var >= $_value) {
            return self::_return($condition[6], $return);
          } else {
            return (isset($condition[8])) ? self::_return($condition[8], $return) : '';
          }

        break;
      case '<':

          if ($var < $_value) {
            return self::_return($condition[6], $return);
          } else {
            return (isset($condition[8])) ? self::_return($condition[8], $return) : '';
          }

        break;
      case '<=':

          if ($var <= $_value) {
            return self::_return($condition[6], $return);
          } else {
            return (isset($condition[8])) ? self::_return($condition[8], $return) : '';
          }

        break;
      case '!=':
      case '<>':

          if ($var != $_value) {
            return self::_return($condition[6], $return);
          } else {
            return (isset($condition[8])) ? self::_return($condition[8], $return) : '';
          }

        break;
      default:
        return "";
        break;
    }
  }

  private static function _issetEmpty($functions, $value)
  {
    $func = $functions[2];

    if (is_array($value)) {
      $i = str_replace(['[',']',"'"], '', $functions[5]);

      if ($func == 'isset') {
        if (!empty($functions[1]) && !isset($value[$i])) {
          return $functions[6];
        } else {
          if (!isset($value[$i])) {
            return '';
          }
        }
      }

      $var = (!empty($functions[5])) ? $value[$i] : $value;
    } else if (is_object($value)) {
      $i = str_replace(['->'], '', $functions[4]);

      if ($func == 'isset') {
        if (!empty($functions[1]) && !isset($value->$i)) {
          return $functions[6];
        } else {
          if (!isset($value->$i)) {
            return '';
          }
        }
      }

      $var = (!empty($functions[4])) ? $value->$i : $value;
    } else {
      $var = $value;
    }

    if (!empty($functions[1])) {
      if (!empty($var) && $func == 'empty') {
        return $functions[6];
      }
    } else {

      if ($func == 'isset') {
        return $functions[6];
      }

      if (empty($var)) {
        return $functions[6];
      }
    }

    return "";
  }

  private static function _counter($indexes, $k)
  {
    if (isset($indexes['COUNTER'])) {
      $inc = intval($indexes['COUNTER']);
      if ($indexes['SIGN'] == '+') {
        $k+= $inc;
      } else if ($indexes['SIGN'] == '-') {
        $k-= $inc;
      }
    }

    return $k;
  }

  private static function _blocks($tpl, $key, $value)
  {
    $tpl = preg_replace_callback('/{{% @block\((\$'.$key.')(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])?(,[a-zA-Z0-9:\s\'",\$\[\]{}]+)?\) %}}/sm', function($block) use ($key, $value) {
      $b2 = (isset($block[2])) ? $block[2] : '';
      $b3 = (isset($block[3])) ? $block[3] : '';
      $var = Duckling::_var([null, null, $b2, $b3], $value);
      $params = (isset($block[4])) ? $block[4] : '';
      return '{{% @block('.$var.$params.') %}}';
    }, $tpl);

    return $tpl;
  }

  private static function _getParams($json, $data)
  {
    $ret = [];

    $m = preg_replace('/\$([a-zA-Z_]+)/', '"$1"', $json);
    $m = preg_replace('/,\s?([a-zA-Z0-9_:{}\s\'",\$\[\]]+)/', '$1', $m);
    $m = json_decode($m, true);

    foreach ($m as $x => $y) {
      $v = (!is_array($y) && isset($data[$y])) ? $data[$y] : $y;
      $ret[$x] = $v;
    }

    return $ret;
  }

  private static function _block($block, $data)
  {
    $ret = (isset($block[2])) ? self::_getParams($block[2], $data) : [];
    $name = str_replace("'", '', $block[1]);

    ob_start();
    \Roducks\Page\Block::load($name, $ret);
    $content = ob_get_contents();
    ob_end_clean();

    return $content;

  }

  public static function formatter($tpl, $key, $value)
  {

    $tpl = preg_replace_callback(self::_rules('format', [$key]), function($matches) use ($key, $value){
      return Duckling::_format($matches, $value);
    }, $tpl);

    return $tpl;
  }

  public static function parser($file, array $data)
  {

    $tpl = Request::getContent($file);
    $ret = [];

    if (empty($data)) {
      return $tpl;
    }

    /*
  	|----------------------------------------------------------------------
  	|	Group variables in "Dimensional" or "Lineal"
  	|----------------------------------------------------------------------
  	*/
		foreach ($data as $key => $value) {
      $tpl = Duckling::_blocks($tpl, $key, $value);
      $tpl = Duckling::formatter($tpl, $key, $value);

      $tpl = preg_replace_callback(self::_rules('isset_empty', [$key]), function($functions) use ($value) {
        return Duckling::_issetEmpty($functions, $value);
			}, $tpl);

			if (is_array($value)) {
				$ret['dimensional'][$key] = $value;
      } else if($value instanceof \Roducks\Libs\ORM\ORM) {
        $tpl = Duckling::_query($tpl, $key, $value);
			} else {
        $tpl = Duckling::_aggregator($tpl, $key, $value);
				$ret['lineal'][$key] = $value;
			}
		}

    /*
  	|----------------------------------------------------------------------
  	|	Dimensional
  	|----------------------------------------------------------------------
  	*/
    if (isset($ret['dimensional'])) {

		  foreach ($ret['dimensional'] as $key => $value) {

			$tpl = preg_replace_callback('/{{% @if \$('.$key.')(->[a-zA-Z_]+)?(\[[a-zA-Z0-9_\']+\])? ([=<>!]+) ([a-zA-Z0-9_\']+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($value) {
        return Duckling::_condition($condition, null, $value);
			}, $tpl);

			$tpl = preg_replace_callback('/{{% @each \$([a-z]+) in \$'.$key.' %}}(.*?){{% @endeach %}}/sm', function($matches) use ($key, $value){
				$content = $matches[2];
				$loop = "";

				foreach ($value as $k => $v) {

					$row = preg_replace_callback('/{{% \$i( (?P<SIGN>[+\-]) (?P<COUNTER>[0-9]+))? %}}/', function($indexes) use ($k){
            return Duckling::_counter($indexes, $k);
					}, $content);

          $row = Duckling::_multipleCondition($row, $k, $v);

					$row = preg_replace_callback('/{{% @if \$([a-zA-z_]+)(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])? ([=<>!]+) ([a-zA-Z0-9_\']+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($k, $v) {
            return Duckling::_condition($condition, $k, $v);
					}, $row);

					$loop .= preg_replace_callback(self::_rules('format', [$matches[1]]), function($matches2) use ($v){
            return Duckling::_format($matches2, $v);
					}, $row);

				}

				return $loop;

			}, $tpl);

		}

    }
    /*
  	|----------------------------------------------------------------------
  	|	Lineal
  	|----------------------------------------------------------------------
  	*/
    if (isset($ret['lineal'])) {

		  foreach ($ret['lineal'] as $key => $value) {

  			$tpl = preg_replace_callback('/{{% @if \$('.$key.')(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])? ([=<>!]+) ([a-zA-Z0-9_\']+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($value) {
          return Duckling::_condition($condition, null, $value);
  			}, $tpl);

        $tpl = Duckling::_multipleCondition($tpl, $key, $value);

		  }
    }
    /*
  	|----------------------------------------------------------------------
  	|	Block
  	|----------------------------------------------------------------------
  	*/
    $tpl = preg_replace_callback('/{{% @block\(([a-z0-9_\-\/\']+)(,[a-zA-Z0-9:\s\'",\$\[\]{}]+)?\) %}}/sm', function($block) use ($data) {
      return Duckling::_block($block, $data);
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Asset
  	|----------------------------------------------------------------------
  	*/
    $tpl = preg_replace_callback('/{{% @asset:([a-z]+)\(([a-zA-Z0-9_\.\-\'\"\/]+)([0-9,\s]+)?\) %}}/', function($asset) {
      $src = str_replace(['"',"'"], '', $asset[2]);
      $size = (isset($asset[3])) ? intval(trim(str_replace(',','', $asset[3]))) : '';

      switch ($asset[1]) {
        case 'image':
          $resource = UI::getImage($src, $size);
          break;
        case 'icon':
          $resource = UI::getIcon($src, $size);
          break;
      }

      return $resource;

    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Template
  	|----------------------------------------------------------------------
  	*/
    //{{% @template('title', {"rod": "developer"}) %}}
    $tpl = preg_replace_callback('/{{% @template\(([a-zA-Z0-9_\.\-\'\"]+)(,[a-zA-Z0-9\s\'",:\$\[\]{}]+)?\) %}}/', function($template) use($data) {

      $file = str_replace(['"',"'"], '', $template[1]);
      $vars = [];

      if (isset($template[2])) {
        $vars = self::_getParams($template[2], $data);
      }

      $merge = (count($vars) > 0);

      return Template::tpl($file, $vars, $merge);
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Translations
  	|----------------------------------------------------------------------
  	*/
    $tpl = preg_replace_callback('/{{% __\((.+)\) %}}/sm', function($str) {
      $text = str_replace(['"',"'"], '', $str[1]);
      return __($text);
    }, $tpl);
    $tpl = preg_replace_callback('/{{% _text\((.+)\) %}}/sm', function($str) {
      $text = str_replace(['"',"'"], '', $str[1]);
      return _text($text);
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Form
  	|----------------------------------------------------------------------
  	*/
    $tpl = preg_replace_callback('/{{% @form:key %}}/sm', function($str) {
      return \Roducks\Framework\Form::getKey();
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Menu
  	|----------------------------------------------------------------------
  	*/
    $tpl = preg_replace_callback('/{{% @menu:([a-z]+)\(([a-z\-\']+),\s?([a-z\-\']+)\) %}}/sm', function($menu) {

      $config = str_replace(['"',"'"], '', $menu[2]);
      $name = str_replace(['"',"'"], '', $menu[3]);

      ob_start();
      \Roducks\Page\Block::load("menu/{$menu[1]}", ['items' => Template::menu($config), 'tpl' => $name]);
      $content = ob_get_contents();
      ob_end_clean();

      return $content;
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Languages
  	|----------------------------------------------------------------------
  	*/
    $tpl = preg_replace_callback('/{{% @lang\(\) %}}/sm', function($lang) use($data) {

      ob_start();
      \Roducks\Page\Block::load("language", ['id' => $data['_PAGE_ID'], 'tpl' => "nav"]);
      $content = ob_get_contents();
      ob_end_clean();

      return $content;
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Environment
  	|----------------------------------------------------------------------
  	*/
    $tpl = preg_replace_callback('/{{% @env\(([a-zA-Z\']+)\) %}}(.*?){{% @endenv %}}/sm', function($env) {

      $display = false;
      $type = str_replace(['"',"'"], '', $env[1]);
      $type = strtoupper($type);

      switch ($type) {
        case 'DEV':
          $display = Environment::inDEV();
          break;
        case 'QA':
          $display = Environment::inQA();
          break;
        case 'PRO':
          $display = Environment::inPRO();
          break;
      }

      return ($display) ? $env[2] : '';
    }, $tpl);

    $tpl = preg_replace_callback('/{{% @url:([a-z]+)\(([a-zA-Z0-9_\-\/\.\'\"]+)?(,\s?\{[a-zA-Z0-9:,\[\]\s\'\"]+\})?(,\s?false)?\) %}}/', function($url){
      $type = $url[1];
      $param = (isset($url[2])) ? str_replace(['"',"'"], '', $url[2]) : '';
      $query = (isset($url[3])) ? json_decode(trim(substr($url[3], 1)), true) : [];
      $override = (isset($url[4])) ? trim(substr($url[4],1)) : 'true';
      $merge = ($override == 'true');

      switch ($type) {
        case 'host';
          return URL::getDomainName();
          break;
        case 'absolute':
          if (!empty($param) && $param != 'false') {
            return URL::setAbsoluteURL($param, $query, $merge);
          }
          return (!empty($param) && $param == 'false') ? URL::getAbsoluteURL(false) : URL::getAbsoluteURL();
          break;
        case 'relative':
          return (!empty($param) && $param == 'false') ? URL::getRelativeURL(false) : URL::getRelativeURL();
          break;
        case 'admin':
          return (!empty($param)) ? URL::getAdminURL($param, $query, $merge) : URL::getAdminURL();
          break;
        case 'front':
          return (!empty($param)) ? URL::getFrontURL($param, $query, $merge) : URL::getFrontURL();
          break;
        case 'set':
          return (!empty($param) && isset($url[3])) ? URL::setURL($param, $query, $merge) : URL::setURL($param);
          break;
      }
    }, $tpl);

    $tpl = preg_replace_callback('/{{% @url:([a-z]+)\((\{[a-zA-Z0-9:,\[\]\s\'\"]+\})(,\s?false)?\) %}}/', function($url){
      $type = $url[1];
      $query = (isset($url[2])) ? json_decode(trim($url[2]), true) : '';
      $override = (isset($url[3])) ? trim(substr($url[3],1)) : 'true';
      $merge = ($override == 'true');

      switch ($type) {
        case 'get':
          return URL::getURL($query, $merge);
          break;
        case 'query':
          return URL::setQueryString($query, $merge);
          break;
      }
    }, $tpl);

    $tpl = preg_replace_callback('/{{% @template\(([a-zA-Z0-9_\-\/\.\'\"]+)?(,\s?\{[a-zA-Z0-9:,\[\]\s\'\"]+\})?(,\s?false)?\) %}}/', function($url){
      $param = (isset($url[1])) ? str_replace(['"',"'"], '', $url[1]) : '';
      $query = (isset($url[2])) ? json_decode(trim(substr($url[2], 1)), true) : [];
      $override = (isset($url[3])) ? trim(substr($url[3],1)) : 'true';
      $merge = ($override == 'true');

      return template::view($param, $query, $merge);
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	End
  	|----------------------------------------------------------------------
  	*/
    $tpl = self::_functions($tpl);
		$tpl = self::_clean($tpl);

    /*
  	|----------------------------------------------------------------------
  	|	Detects uncaught expression
  	|----------------------------------------------------------------------
  	*/
    if (
      preg_match('/{{% (endif|endforeach|else|enwhile) %}}/sm', $tpl, $errors) ||
      preg_match('/{{% ([a-zA-Z0-9_!=<>$\s@|+]+) %}}/sm', $tpl, $errors)
    ) {
      if (Environment::inDev()) {
        $url = \Roducks\Framework\URL::getAbsoluteURL();
        return Error::block("Uncaught expression", 0, $url, $file, $errors[0], true);
      }

      return '';
    }

    return $tpl;

  }

}
