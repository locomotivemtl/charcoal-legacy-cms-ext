{{!
	Template: HTML Document Head
	============================

	The `<head>` of the HTML document.

	@package CMS\Templates
	@since   2015-05-04
}}
<!DOCTYPE html>
<!--[if lte IE 9]>    <html lang="{{ lang }}"{{# html_class.root }}lt-ie10{{/ html_class.root }}> <![endif]-->
<!--[if gt IE 9]><!--><html lang="{{ lang }}"{{&html_class.root}}><!--<![endif]-->
	<head>
		<meta charset="UTF-8">

		<base href="{{ URL }}">

		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="HandheldFriendly" content="True">
		{{# error_code }}
		<meta name="robots" content="noindex">
		{{/ error_code }}
		{{^ error_code }}

		{{> widget.cms.metadata }}

		<link rel="canonical" href="{{ current_url }}">

		{{# all_translations }}
		<link rel="alternate" hreflang="{{ locale }}" href="{{ full_url }}" title="{{ localized.label }}">
		{{/ all_translations }}

		{{/ error_code }}
		<?php echo Charcoal::get_js('head'); ?>
		<?php echo Charcoal::get_css(); ?>

		{{> widget.cms.typekit }}

		{{> widget.cms.google_analytics }}
	</head>
	<body{{&html_class.body}} data-template="{{ template_ident }}">
