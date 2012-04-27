<?
/* Argv.class.php - Class for accessing the ARGV/ARGC
 * Copyright (C) 2007 Erik Osterman <e@osterman.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/* File Authors:
 *   Erik Osterman <e@osterman.com>
 */


class Argv implements IteratorAggregate
{
  protected $self;
	protected $args;
	protected $opts;
	
	public function __construct( $args = null )
	{
		global $argv;
		if( is_array($args) )
			$this->args = $args;
		else
			$this->args = $argv;
		$this->parse($args);
	}

  public function __destruct()
  {
    unset($this->args);
    unset($this->opts);
  }
	
	public function __unset( $property )
	{
		throw new Exception( get_class($this) . "::$property cannot be unset");
	}

	public function parse( $args )
	{
    $this->self = $args[0];
		$this->opts = Array();
		for(  $i = 1; $i < count($this->args); $i++ )
		{
			if( preg_match('/^-+(.*)$/', $this->args[$i], $matches ) )
			{
				$key = $matches[1];
				if( array_key_exists($key, $this->opts) )
				{
					end($this->opts[$key]);
					$index = key($this->opts);
				} else {
					$this->opts[$key] = Array();
					$index = 0;
				}
			} elseif( empty($key) )
				throw new Exception("Invalid argument {$this->args[$i]} passed to {$this->args[0]}");
			else 
				$this->opts[$key][$index][] = $this->args[$i];
		}
	}
	
	public function __get( $name )
	{
    if($name == 'self')
      return $this->self;

    elseif( array_key_exists( $name, $this->opts ))
		{
			switch( count($this->opts[$name]) )
			{
				case 0:
					return true;
				case 1:
					switch( count($this->opts[$name][0]) )
					{
						case 0:
							return true;
						case 1:
							return $this->opts[$name][0][0] ;
						default:
							return $this->opts[$name][0];
					}
				default:
					return $this->opts[$name];
			}
		} else {
      $key = self::getProthon($name);
      if( $key === $name )
 			  throw new Exception( get_class($this) . "::$name not passed");
      else
        return $this->__get($key);
     
    }
	}

  public static function getProthon( $string )
  {
    $prothon = strtolower(preg_replace('/([a-z])([A-Z0-9])/', '$1-$2', $string ));
    return $prothon;
  }

  public function count()
  {
    return count($this->opts);
  }

	public function passed( $name )
	{
    if( array_key_exists($name, $this->opts) )
      return true;
    elseif( array_key_exists( self::getProthon($name), $this->opts ) )
      return true;
    else
      return false;
	}
	
	// Required definition of interface IteratorAggregate
	public function getIterator() {
	          return new ArrayIterator($this->opts);
	}
}

/*
   // Usage example:
$args = new Argv();
print "server: " . $args->server . "\n";
print "auth: " . $args->auth . "\n";
*/

?>
