<?php

class profiler
{
	static function &init ($extension_name)
	{
		echo "\n".'
			<style type="text/css">
			'. file_get_contents(dirname(__FILE__) .'/profiler.css') .'
			</style>
		'."\n";

		if (!extension_loaded($extension_name))
		{
			echo '
				<div class="warningBox1">
					Cannot load <b>'. $extension_name .'</b> extension. Please check your PHP configuration.
				</div>
			';
		}

		$profiler_module_name = dirname(__FILE__) .'/profiler_'. basename($extension_name) .'.php';

		if (include($profiler_module_name))
		{
			$profiler_class_name = "profiler_{$extension_name}";
			$profiler_obj = new $profiler_class_name();
			return $profiler_obj;
		}
		else
		{
			trigger_error("Unsupported profiler extension: $extension_name", E_USER_ERROR);
		}
	}
}