<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_clickable_smileys($image_url, $alias = '', $smileys = NULL)
{
	// For backward compatibility with js_insert_smiley

	if (is_array($alias))
	{
		$smileys = $alias;
	}

	if ( ! is_array($smileys))
	{
		if (FALSE === ($smileys = _get_smiley_array()))
		{
			return $smileys;
		}
	}

	// Add a trailing slash to the file path if needed
	$image_url = rtrim($image_url, '/').'/';

	$CI =& get_instance();
	
	$used = array();
	foreach ($smileys as $key => $val)
	{
		// Smileys secrets de la TeamBouzouk :)
		if ($smileys[$key][4] == '1' && ! $CI->bouzouk->is_admin())
			continue;
			
		// Keep duplicates from being used, which can happen if the
		// mapping array contains multiple identical replacements.  For example:
		// :-) and :) might be replaced with the same image so both smileys
		// will be in the array.
		if (isset($used[$smileys[$key][0]]))
		{
			continue;
		}

		$link[] = "<a href=\"javascript:void(0);\" onclick=\"insert_smiley('".$key."', '".$alias."')\"><img src=\"".$image_url.$smileys[$key][0]."\" width=\"".$smileys[$key][1]."\" height=\"".$smileys[$key][2]."\" alt=\"".$smileys[$key][3]."\" title=\"".$smileys[$key][5]."\" style=\"border:0;\" /></a>";

		$used[$smileys[$key][0]] = TRUE;
	}

	return $link;
}

function parse_smileys($str = '', $image_url = '', $smileys = NULL)
{
	if ($image_url == '')
	{
		return $str;
	}

	if ( ! is_array($smileys))
	{
		if (FALSE === ($smileys = _get_smiley_array()))
		{
			return $str;
		}
	}

	// Add a trailing slash to the file path if needed
	$image_url = preg_replace("/(.+?)\/*$/", "\\1/",  $image_url);

	foreach ($smileys as $key => $val)
	{
		$str = str_replace(' '.$key, "<img src=\"".$image_url.$smileys[$key][0]."\" width=\"".$smileys[$key][1]."\" height=\"".$smileys[$key][2]."\" alt=\"".$smileys[$key][3]."\" title=\"".$smileys[$key][5]."\" style=\"border:0;\" />", $str);
	}

	return $str;
}
