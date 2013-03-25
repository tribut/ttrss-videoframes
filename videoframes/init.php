<?php
class VideoFrames extends Plugin {

	function about() {
		return array(0.1,
			"Enable the playback of embedded videos from well-known sites",
			"dxbi",
			false);
	}

	function init($host) {
		$host->add_hook($host::HOOK_SANITIZE, $this);
	}

	function hook_sanitize($doc, $site_url) {
		$allowed_iframes = array(
			'www.youtube.com' => '/embed/',
			'player.vimeo.com' => '/video/',
			'www.myvideo.de' => '/embed/',
			'www.youtube-nocookie.com' => '/embed/',
			'www.dailymotion.com', => '/embed/video/'
		);

		$xpath = new DOMXPath($doc);
		$entries = $xpath->query('//iframe');
		foreach ($entries as $entry) {
			$src = $entry->getAttribute('src');
			$url = parse_url($src);

			if ($url &&
			    array_key_exists($url['host'], $allowed_iframes) &&
			    strpos($url['path'], $allowed_iframes[$url['host']]) === 0
			) {
				// remove sandbox attribute
				while($entry->hasAttribute('sandbox')) {
					$entry->removeAttribute('sandbox');
				}
				// force https
				// http_build_url would be the nice solution,
				// but that's apparently not available everywhere
				$entry->setAttribute('src',
					preg_replace(
						'#^[a-z]+://#i',
						'https://',
						$src
					)
				);
			}
		}

		return $doc;
	}

}

