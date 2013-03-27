<?php
class VideoFrames extends Plugin {

	function about() {
		return array(0.3,
			"Enable the playback of embedded videos from well-known sites",
			"dxbi",
			false);
	}

	function init($host) {
		$host->add_hook($host::HOOK_SANITIZE, $this);
	}

	function hook_sanitize($doc, $site_url, $allowed_elements = null, $disallowed_attributes = null) {
		// array of whitelisted iframes
		// [key]   full hostname of the src attribute
		// [value] start of the path must match this string
		$allowed_iframes = array(
			'www.youtube.com' => '/embed/',
			'www.youtube-nocookie.com' => '/embed/',
			'player.vimeo.com' => '/video/',
			'www.myvideo.de' => '/embed/',
			'www.dailymotion.com' => '/embed/video/'
		);

		// array of <object><embed></object> style
		// embedded flash videos that should be transformed
		// to an iframe
		// [key] full hostname in the src attribute
		// [value][0] regex that the path must match against
		//            or, if it starts with a '?' name of the
		//            query string argument that is needed to
		//            build the iframe src
		// [value][1] path of the iframe src, $1 is replaced
		//            by the first grouped expression in the
		//            regex or the value of the query string
		//            variable, respectively
		// [value][2] (optional) hostname for iframe src
		$transform_objects = array(
			'www.youtube.com' => array(
				'#^/v/([a-zA-Z0-9_]+)(&.*)?$#',
				'/embed/$1'
			),
			'www.youtube-nocookie-com' => array(
				'#^/v/([a-zA-Z0-9_]+)(&.*)?$#',
				'/embed/$1'
			),
			'vimeo.com' => array(
				'?clip_id',
				'/video/$1',
				'player.vimeo.com'
			),
			'www.myvideo.de' => array(
				'#^/movie/([a-zA-Z0-9_]+)(&.*)?$#',
				'/embed/$1'
			),
			'www.dailymotion.com' => array(
				'#^/swf/video/([a-zA-Z0-9_]+)(&.*)?$#',
				'#/embed/video/$1#'
			)
		);

		if (!is_null($allowed_elements) && !is_null($disallowed_attributes)) {
			if (!array_search('iframe', $allowed_elements)) {
				$remove_unknown_iframes = true;
				$allowed_elements[] = 'iframe';
			}
		}

		$xpath = new DOMXPath($doc);

		// remove sandbox from whitelisted iframes and force https
		$entries = $xpath->query('//iframe');
		foreach ($entries as $entry) {
			$src = $entry->getAttribute('src');
			$url = parse_url($src);

			if ($url &&
			    array_key_exists($url['host'], $allowed_iframes) &&
			    strpos($url['path'], $allowed_iframes[$url['host']]) === 0
			) {
				// force https
				// http_build_url would be the nice solution,
				// but that's apparently not available everywhere
				$src = preg_replace('#^[a-z]+://#i', 'https://',
					$src, -1, $rcount);
				if ($rcount < 1) continue; // if this happens the url is really strange
				$entry->setAttribute('src', $src);

				// remove sandbox attribute
				while ($entry->hasAttribute('sandbox')) {
					$entry->removeAttribute('sandbox');
				}
			} elseif ($remove_unknown_iframes) {
				$entry->parentNode->removeChild($entry);
			}
		}

		// replace <object><embed> style flash videos with iframe
		$entries = $xpath->query('//object/embed[@src]');
		foreach ($entries as $entry) {
			$src        = $entry->getAttribute('src');
			$url        = parse_url($src);
			if (!$url) continue;

			$host       = $url['host'];
			if (array_key_exists($host, $transform_objects)) {
				$pattern = $transform_objects[$host][0];
				$replace = $transform_objects[$host][1];
				if (isset($transform_objects[$host][2]))
					$newhost = $transform_objects[$host][2];
				else
					$newhost = $host;
				if ($pattern[0] == '?') {
					$querykey = substr($pattern, 1);
					parse_str($url['query'], $query);
					if (array_key_exists($querykey, $query) &&
					    preg_match('/^[a-zA-Z0-9_]+$/', $query[$querykey])) {
						$iframesrc = 'https://' . $newhost .
							str_replace('$1',
								$query[$querykey],
								$replace
							);
					} else { // query string parameter not set
						continue;
					}
				} else { // not a query string, use path
					$iframesrc = 'https://' . $newhost .
						preg_replace($pattern,
							$replace,
							$url['path'], 
							-1, $rcount);
					if ($rcount < 1) continue;
				}
			} else { // host not in whitelist
				continue;
			}

			$tag_object = $entry->parentNode;
			$tag_parent = $tag_object->parentNode;
			$height     = intval($entry->getAttribute('height'));
			$width      = intval($entry->getAttribute('width'));
			// youtube defaults
			if ($height < 1) $height = 315;
			if ($width  < 1) $width = 560;
 
			$tag_iframe = $doc->createElement('iframe');
			$tag_iframe->setAttribute('allowfullscreen', '');
			$tag_iframe->setAttribute('width', $width);
			$tag_iframe->setAttribute('height', $height);
			$tag_iframe->setAttribute('frameborder', '0');
			$tag_iframe->setAttribute('src', $iframesrc);

			$tag_parent->replaceChild($tag_iframe, $tag_object);
		}

		if ($remove_unknown_iframes) {
			return array($doc, $allowed_elements, $disallowed_attributes);
		} else {
			return $doc;
		}
	}

}

