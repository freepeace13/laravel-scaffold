<?php

private function setEnv($key, $value)
{
	file_put_contents(app()->environmentFilePath(), str_replace(
		$key . '=' . env($value),
		$key . '=' . $value,
		file_get_contents(app()->environmentFilePath())
	));
}
