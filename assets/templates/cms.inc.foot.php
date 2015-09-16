{{!
	Template: HTML Document Foot
	============================

	The content before the closing `</body>` tag of the HTML document.

	@package CMS\Templates
	@since   2015-05-04
}}

		{{# filter_document_foot }}<?php

			echo Charcoal::get_js();

		?>{{/ filter_document_foot }}

    </body>
</html>
