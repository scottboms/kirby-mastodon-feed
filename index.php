<?php

//namespace scottboms\mastodon-feed;

/**
 * Mastodon Feed plugin for Kirby
 *
 * @author Scott Boms <plugins@scottboms.com>
 * @copyright Scott Boms <plugins@scottboms.com>
 * @link https://github.com/scottboms/kirby-mastodon-feed
 * @license MIT
**/

load([
	'Scottboms\Mastodon\Feed' => __DIR__ . '/classes/Feed.php'
]);

use Scottboms\Mastodon\Feed;
use Kirby\Cms\App;

// shamelessly borrowed from distantnative/retour-for-kirby
if (
	version_compare(App::version() ?? '0.0.0', '4.0.1', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '6.0.0', '>=') === true
) {
	throw new Exception('ISBN Field requires Kirby v4 or v5');
}

Kirby::plugin(
	name: 'scottboms/mastodon-feed',
	info: [
		'homepage' => 'https://github.com/scottboms/kirby-mastodon-feed',
		'license' => 'MIT'
	],
	version: '1.0.2',
	extends: [
		'options' => [
			'username'			 => null,
			'server'				 => null,
			'cache'					 => true,
			'cachettl'       => 900,
			'limit'					 => 20,
			'dateformat'     => 'M d, Y',
			'excludereplies' => true,
			'onlymedia' 		 => false
		],
		'api' => [
			'routes' => require __DIR__ . '/lib/routes.php'
		],
		'areas' => [
			'mastodon-feed' => function ($kirby) {
				return [
					'label' => 'Mastodon Feed',
					'icon'  => 'rss',
					'menu'  => true,
					'link'  => 'mastodon-feed',
					'views' => [
						'pattern' => 'mastodon-feed',
						'action'  => function () {
							return [
								'component' => 'k-mastodon-feed-view',
								'props' => [
									// return data from the Feed class here
									'status' => 'Panel area loaded',
								]
							];
						}
					]
				];
			}
		],
		'snippets' => [
			'mastodon_feed' => __DIR__ . '/snippets/mastodon_feed.php'
		]
	]
);
