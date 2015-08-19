{{!
	Widget: TypeKit Snippet
	================================

	The `<head>` of the HTML document.

	@package CMS\Templates
	@since   2015-08-19
}}
{{#cfg.typekit}}
<script src="//use.typekit.net/{{cfg.typekit}}.js"></script>
<script>try{Typekit.load();}catch(e){}</script>
{{/cfg.typekit}}
