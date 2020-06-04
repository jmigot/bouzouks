<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function img_url($uri = '')
{
	$CI =& get_instance();
	return $CI->config->base_url('webroot/images/'.$uri);
}

function smileys_url()
{
	$CI =& get_instance();
	return $CI->config->base_url('webroot/images/smileys/');
}

function css_url($uri = '')
{
	$CI =& get_instance();
	return $CI->config->base_url('webroot/css/'.$uri);
}

function javascript_url($uri = '')
{
	$CI =& get_instance();
	return $CI->config->base_url('webroot/javascript/'.$uri);
}

function son_url($uri = '')
{
	$CI =& get_instance();
	return $CI->config->base_url('webroot/sons/'.$uri);
}

function img_path($uri = '')
{
	return BASEPATH.'../webroot/images/'.$uri;
}
