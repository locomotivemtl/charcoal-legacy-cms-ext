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
		{{# filter_document_head }}
		<meta charset="UTF-8">

		<base href="{{ URL }}">

		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="HandheldFriendly" content="True">

		{{> widget.cms.metadata }}

		<link rel="canonical" href="{{ current_url }}">

		{{> widget.cms.typekit }}

		{{> widget.cms.google_analytics }}
		{{/ filter_document_head }}


	</head>
	<body{{&html_class.body}}>
