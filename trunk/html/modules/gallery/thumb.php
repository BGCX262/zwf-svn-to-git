<?php
/**
 * Thumbnail list generator
 * Adapted from Westcoe Property listing library
 *
 * @author Justin Johnson <johnsonj>, justin@zebrakick.com
 * @author Andrew Murphy 
 * @version 2.5.6 20080317 JJ
 * @version 2.5.4 20080316 JJ
 * @version 1.1.0 20080125 JJ
 * @version 1.0.0 20071116 AM
 */


function build_thumbnails_list($images, $template=false, $columns=1) {
	$count = count($images);

	if ( $count < 1 ) {
		return '';
	}

	if ( empty($template) ) {
		$template = multiform(PATH_TEMPLATE_STUBS.'stubs/examples/details.gform.html');
	}

	$search = array('{listing_id}', '{photo_path}', '{photo_id}', '{title}', '{description}', '{index}');
	$list   = array_fill(0, $columns, '');

	for ( $i = 0; $i<$count; ++$i ) {
		$list[$i % $columns] .= str_replace(
			$search,
			array(
				$images[$i]['listing_id'],
				get_path('imageGateway', true).'?id=' . $images[$i]['photo_id'],
				$images[$i]['photo_id'],
				$images[$i]['title'],
				$images[$i]['description'],
				$i
			),
			$template
		);
	}

	return "\n<ul>\n".implode("</ul>\n<ul>\n", $list)."</ul>\n";
}

?>