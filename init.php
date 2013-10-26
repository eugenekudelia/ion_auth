<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Translation/internationalization function for Ion Auth as Kohana module.
 *
 *    ion__('Welcome back, Ben);
 * 
 * @uses    I18n::get
 * @uses    Lang::module
 * @param   string  $string text to translate
 * $param   array   $path   local path to lang file
 * @param   array   $values values to replace in the translated text
 * @param   string  $lang   target language
 * @return  string
 */
function ion__($string, array $values = NULL, $path = '', $lang = NULL)
{
	$lang = Lang::module('ion_auth', $path, $lang);
	$string = I18n::get($string, $lang);

	return empty($values) ? $string : strtr($string, $values);
}
