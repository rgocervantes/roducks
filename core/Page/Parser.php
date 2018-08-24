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

use Request;
use Helper;

class Parser
{

  private static function _rules($type, $args)
  {
    switch ($type) {
      case 'format':
        $regexp = '/{{% ([a-z_|]+[|])?\$'.$args[0].'(->[a-zA-Z_]+)?(\[[a-zA-Z0-9_\']+\])?([a-zA-Z0-9_\-\/,|\':*\s]+)? %}}/sm';
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
    $tpl = preg_replace_callback('/{{% @while \$('.$key.')->fetch\(([a-z\']+)?\) in \$([a-zA-Z_]+) %}}(.*?){{% @endwhile %}}/sm', function ($matches) use($query) {

      $loop = "";
      $content = $matches[4];
      $type = (!empty($matches[2])) ? str_replace("'", '', $matches[2]) : 'default';

      while($row = $query->fetch($type)):
        $loop .= preg_replace_callback(Parser::_rules('format', [$matches[3]]), function ($m) use($row) {
          return Parser::_format($m, $row);
        }, $content);
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
        case 'user_logged_in':
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

  private static function _aggregator($tpl, $key, $value)
  {

    $tpl = preg_replace_callback('/{{% (\$i [+\-]) \$'.$key.'(->[a-zA-Z_]+)?(\[[a-zA-Z0-9_\']+\])? %}}/', function($dis) use ($value){
      $var = Parser::_var($dis, $value);
      if (!is_integer($var)) {
        $var = 0;
      }
      return '{{% '.$dis[1].' '.$var.' %}}';
    }, $tpl);

    return $tpl;
  }

  private static function _condition($condition, $k, $v)
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
            return $condition[6];
          } else {
            return (isset($condition[8])) ? $condition[8] : '';
          }

        break;
      case '>':

          if ($var > $_value) {
            return $condition[6];
          } else {
            return (isset($condition[8])) ? $condition[8] : '';
          }

        break;
      case '>=':

          if ($var >= $_value) {
            return $condition[6];
          } else {
            return (isset($condition[8])) ? $condition[8] : '';
          }

        break;
      case '<':

          if ($var < $_value) {
            return $condition[6];
          } else {
            return (isset($condition[8])) ? $condition[8] : '';
          }

        break;
      case '<=':

          if ($var <= $_value) {
            return $condition[6];
          } else {
            return (isset($condition[8])) ? $condition[8] : '';
          }

        break;
      case '!=':
      case '<>':

          if ($var != $_value) {
            return $condition[6];
          } else {
            return (isset($condition[8])) ? $condition[8] : '';
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

      $var = $value[$i];
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

      $var = $value->$i;
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

  private static function _block($block, $data)
  {
    $ret = [];

    if (isset($block[2])) {
      $m = preg_replace('/\$([a-zA-Z_]+)/', '"$1"', $block[2]);
      $m = preg_replace('/,\s?(\[[a-zA-Z0-9_:{}\s\'",\$\[\]]+\])/', '$1', $m);
      $m = json_decode($m, true);

      if (count($m) == 1) {
        $m = $m[0];
      }

      foreach ($m as $x => $y) {
        $v = (!is_array($y) && isset($data[$y])) ? $data[$y] : $y;
        $ret[$x] = $v;
      }
    }

    $name = str_replace("'", '', $block[1]);

    ob_start();
    \Roducks\Page\Block::load($name, $ret);
    $content = ob_get_contents();
    ob_end_clean();

    return $content;

  }

  public static function get($file, array $data)
  {

    $tpl = Request::getContent($file);
    $ret = [];

    /*
  	|----------------------------------------------------------------------
  	|	Group variables in "Dimensional" or "Lineal"
  	|----------------------------------------------------------------------
  	*/
		foreach ($data as $key => $value) {

      $tpl = preg_replace_callback(self::_rules('format', [$key]), function($matches2) use ($value){
        return Parser::_format($matches2, $value);
			}, $tpl);

			if (is_array($value)) {
				$ret['dimensional'][$key] = $value;

      } else if($value instanceof \Roducks\Libs\ORM\ORM) {
        $tpl = Parser::_query($tpl, $key, $value);
			} else {
        $tpl = Parser::_aggregator($tpl, $key, $value);

				$ret['lineal'][$key] = $value;
			}
		}

    /*
  	|----------------------------------------------------------------------
  	|	Dimensional
  	|----------------------------------------------------------------------
  	*/
		foreach ($ret['dimensional'] as $key => $value) {

			$tpl = preg_replace_callback('/{{% @if \$('.$key.')(->[a-zA-Z_]+)?(\[[a-zA-Z0-9_\']+\])? ([=<>!]+) ([a-zA-Z0-9_\']+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($value) {
        return Parser::_condition($condition, null, $value);
			}, $tpl);

			$tpl = preg_replace_callback('/{{% @each \$'.$key.' in \$([a-z]+) %}}(.*?){{% @endeach %}}/sm', function($matches) use (&$ret, $key, $value){
				$content = $matches[2];
				$loop = "";

				foreach ($value as $k => $v) {

					$row = preg_replace_callback('/{{% \$i( (?P<SIGN>[+\-]) (?P<COUNTER>[0-9]+))? %}}/', function($indexes) use ($k){
            return Parser::_counter($indexes, $k);
					}, $content);

					$row = preg_replace_callback('/{{% @if \$([a-zA-z_]+)(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])? ([=<>!]+) ([a-zA-Z0-9_\']+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($k, $v) {
            return Parser::_condition($condition, $k, $v);
					}, $row);

					$loop .= preg_replace_callback(self::_rules('format', [$matches[1]]), function($matches2) use ($v){
            return Parser::_format($matches2, $v);
					}, $row);

				}

				return $loop;

			}, $tpl);

		}

    /*
  	|----------------------------------------------------------------------
  	|	Lineal
  	|----------------------------------------------------------------------
  	*/
		foreach ($ret['lineal'] as $key => $value) {

			$tpl = preg_replace_callback(self::_rules('format', [$key]), function($matches) use ($value){
        return Parser::_format($matches, $value);
			}, $tpl);

			$tpl = preg_replace_callback('/{{% @if\((!)?(isset|empty)\(\$('.$key.')(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])?\)\) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($functions) use ($value) {
        return Parser::_issetEmpty($functions, $value);
			}, $tpl);

			$tpl = preg_replace_callback('/{{% @if \$('.$key.')(->[a-zA-Z_]+)?(\[[a-zA-Z_]+\])? ([=<>!]+) ([a-zA-Z0-9_\']+) %}}(.*?)({{% @else %}}(?P<ELSE>.*?))?{{% @endif %}}/sm', function($condition) use ($value) {
        return Parser::_condition($condition, null, $value);
			}, $tpl);

		}

    $tpl = preg_replace_callback('/{{% @block\(([a-z\-\/\']+)(,[a-zA-Z0-9:\s\'",\$\[\]{}]+)?\) %}}/sm', function($block) use ($data) {
      return Parser::_block($block, $data);
    }, $tpl);

    /*
  	|----------------------------------------------------------------------
  	|	End
  	|----------------------------------------------------------------------
  	*/
    $tpl = self::_functions($tpl);
		$tpl = self::_clean($tpl);

    return $tpl;

  }

}
