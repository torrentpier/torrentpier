<?php
/**
 * A class for validating method parameters to allowed types via reflection.
 *
 * Purpose
 *   Used as a more convenient multiple assert(), standing after the declaration of the methods.
 *
 * Features and advantage
 *   * Very easy to use
 *   * Ability to turn off on the production server
 *
 * WARNING
 *   On a production server, it is important to disable assert, that would save server resources.
 *   For this, use the assert_options(ASSERT_ACTIVE, false) or INI setting "assert.active 0".
 *   In this case ReflectionTypeHint::isValid() always returns TRUE!
 *
 * Useful links
 *   http://www.ilia.ws/archives/205-Type-hinting-for-PHP-5.3.html
 *   http://php.net/manual/en/language.oop5.typehinting.php
 * 
 * @example  ReflectionTypeHint_example.php
 * @link     http://code.google.com/p/php5-reflection-type-hint/
 * @license  http://creativecommons.org/licenses/by-sa/3.0/
 * @author   Nasibullin Rinat
 * @version  1.1.0
 */
class ReflectionTypeHint
{
	protected static $hints = array(
		'int'      => 'is_int',
		'integer'  => 'is_int',
		'digit'    => 'ctype_digit',
		'number'   => 'ctype_digit',
		'float'    => 'is_float',
		'double'   => 'is_float',
		'real'     => 'is_float',
		'numeric'  => 'is_numeric',
		'str'      => 'is_string',
		'string'   => 'is_string',
		'char'     => 'is_string',
		'bool'     => 'is_bool',
		'boolean'  => 'is_bool',
		'null'     => 'is_null',
		'array'    => 'is_array',
		'obj'      => 'is_object',
		'object'   => 'is_object',
		'res'      => 'is_resource',
		'resource' => 'is_resource',
		'scalar'   => 'is_scalar',  #integer, float, string or boolean
		'cb'       => 'is_callable',
		'callback' => 'is_callable',
	);

	#calling the methods of this class only statically!
	private function __construct() {}

	public static function isValid()
	{
		if (! assert_options(ASSERT_ACTIVE)) return true;
		$bt = self::debugBacktrace(null, 1);
		extract($bt);  //to $file, $line, $function, $class, $object, $type, $args
		if (! $args) return true; #speed improve
		$r = new ReflectionMethod($class, $function);
		$doc = $r->getDocComment();
		$cache_id = $class. $type. $function;
		preg_match_all('~	[\r\n]++ [\x20\t]++ \* [\x20\t]++
							@param
							[\x20\t]++
							\K #memory reduce
							( [_a-z]++[_a-z\d]*+
								(?>[|/,][_a-z]+[_a-z\d]*)*+
							) #1 types
							[\x20\t]++
							&?+\$([_a-z]++[_a-z\d]*+) #2 name
						~sixSX', $doc, $params, PREG_SET_ORDER);
		$parameters = $r->getParameters();
		//d($args, $params, $parameters);
		if (count($parameters) > count($params))
		{
			$message = 'phpDoc %d piece(s) @param description expected in %s%s%s(), %s given, ' . PHP_EOL
					 . 'called in %s on line %d ' . PHP_EOL
					 . 'and defined in %s on line %d';
			$message = sprintf($message, count($parameters), $class, $type, $function, count($params), $file, $line, $r->getFileName(), $r->getStartLine());
			trigger_error($message, E_USER_NOTICE);
		}
		foreach ($args as $i => $value)
		{
			if (! isset($params[$i])) return true;
			if ($parameters[$i]->name !== $params[$i][2])
			{
				$param_num = $i + 1;
				$message = 'phpDoc @param %d in %s%s%s() must be named as $%s, $%s given, ' . PHP_EOL
						 . 'called in %s on line %d ' . PHP_EOL
						 . 'and defined in %s on line %d';
				$message = sprintf($message, $param_num, $class, $type, $function, $parameters[$i]->name, $params[$i][2], $file, $line, $r->getFileName(), $r->getStartLine());
				trigger_error($message, E_USER_NOTICE);
			}

			$hints = preg_split('~[|/,]~sSX', $params[$i][1]);
			if (! self::checkValueTypes($hints, $value))
			{
				$param_num = $i + 1;
				$message = 'Argument %d passed to %s%s%s() must be an %s, %s given, ' . PHP_EOL
						 . 'called in %s on line %d ' . PHP_EOL
						 . 'and defined in %s on line %d';
				$message = sprintf($message, $param_num, $class, $type, $function, implode('|', $hints), (is_object($value) ? get_class($value) . ' ' : '') . gettype($value), $file, $line, $r->getFileName(), $r->getStartLine());
				trigger_error($message, E_USER_WARNING);
				return false;
			}
		}
		return true;
	}

	/**
	 * Return stacktrace. Correctly work with call_user_func*()
	 * (totally skip them correcting caller references).
	 * If $return_frame is present, return only $return_frame matched caller, not all stacktrace.
	 *
	 * @param   string|null  $re_ignore     example: '~^' . preg_quote(__CLASS__, '~') . '(?![a-zA-Z\d])~sSX'
	 * @param   int|null     $return_frame
	 * @return  array
	 */
	public static function debugBacktrace($re_ignore = null, $return_frame = null)
	{
		$trace = debug_backtrace();

		$a = array();
		$frames = 0;
		for ($i = 0, $n = count($trace); $i < $n; $i++)
		{
			$t = $trace[$i];
			if (! $t) continue;

			// Next frame.
			$next = isset($trace[$i+1])? $trace[$i+1] : null;

			// Dummy frame before call_user_func*() frames.
			if (! isset($t['file']) && $next)
			{
				$t['over_function'] = $trace[$i+1]['function'];
				$t = $t + $trace[$i+1];
				$trace[$i+1] = null; // skip call_user_func on next iteration
			}

			// Skip myself frame.
			if (++$frames < 2) continue;

			// 'class' and 'function' field of next frame define where this frame function situated.
			// Skip frames for functions situated in ignored places.
			if ($re_ignore && $next)
			{
				// Name of function "inside which" frame was generated.
				$frame_caller = (isset($next['class']) ? $next['class'] . $next['type'] : '')
							  . (isset($next['function']) ? $next['function'] : '');
				if (preg_match($re_ignore, $frame_caller)) continue;
			}

			// On each iteration we consider ability to add PREVIOUS frame to $a stack.
			if (count($a) === $return_frame) return $t;
			$a[] = $t;
		}
		return $a;
	}

	/**
	 * Checks a value to the allowed types
	 *
	 * @param   array  $types
	 * @param   mixed  $value
	 * @return  bool
	 */
	public static function checkValueTypes(array $types, $value)
	{
		foreach ($types as $type)
		{
			$type = strtolower($type);
			if (array_key_exists($type, self::$hints) && call_user_func(self::$hints[$type], $value)) return true;
			if (is_object($value) && @is_a($value, $type)) return true;
			if ($type === 'mixed') return true;
		}
		return false;
	}
}