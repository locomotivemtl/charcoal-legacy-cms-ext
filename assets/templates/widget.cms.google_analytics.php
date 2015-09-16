{{!
	Widget: Google Analytics Snippet
	================================

	The `<head>` of the HTML document.

	@package CMS\Templates
	@since   2015-08-19
}}
{{# cfg.google_analytics }}
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	{{# filter_google_analytics }}
			ga('create', '{{ cfg.google_analytics }}', 'auto');
			ga('send', 'pageview');
	{{/ filter_google_analytics }}

</script>
{{/ cfg.google_analytics }}
