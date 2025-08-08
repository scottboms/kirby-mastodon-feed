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
	'Scottboms\Mastodon\Feed' => __DIR__ . '/classes/Feed.php',
	'Scottboms\Mastodon\CacheKey' => __DIR__ . '/classes/CacheKey.php',
]);

use Scottboms\Mastodon\Feed;
use Kirby\Cms\App;

// shamelessly borrowed from distantnative/retour-for-kirby
if (
	version_compare(App::version() ?? '0.0.0', '4.0.1', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '6.0.0', '>=') === true
) {
	throw new Exception('Mastodon Feed requires Kirby v4 or v5');
}

Kirby::plugin(
	name: 'scottboms/mastodon-feed',
	info: [
		'homepage' => 'https://github.com/scottboms/kirby-mastodon-feed',
		'license' => 'MIT'
	],
	version: '1.1.2',
	extends: [
		'options' => [
			'username'			 => null,
			'server'				 => null,
			'cache'					 => true,
			'cachettl'       => 900,
			'limit'					 => 20,
			'dateformat'     => 'M d, Y',
			'excludereplies' => true,
			'onlymedia' 		 => false,
			'panel' => [
				'limit' => 12
			],
		],
		'api' => [
			'routes' => require __DIR__ . '/lib/routes.php'
		],
		'autoload' => [
			'psr-4' => [
				'Scottboms\\Mastodon\\' => __DIR__ . '/classes',
			]
		],
		'areas' => [
			'mastodon-feed' => function ($kirby) {
				return [
					'label' => 'Mastodon Feed',
					'icon'  => 'mastodon',
					'breadcrumbLabel' => function() {
						return 'Mastodon Feed';
					},
					'menu'  => true,
					'link'  => 'mastodon-feed',
					'views' => [
						[
							'pattern' => 'mastodon-feed',
							'action'  => function () {
								$user = kirby()->user();

								try {
									$items = Feed::formattedFeed();
									$feed = Feed::build();
									$account = $feed->getAccountInfo();

								  if (!$items instanceof Kirby\Toolkit\Collection) {
								    throw new Exception('Feed did not return a valid collection.');
								  }

									$panelLimit = (int) option('scottboms.mastodon-feed.panel.limit', 12);
									$panelLimit = max(1, $panelLimit);

								  return [
								    'component' => 'k-mastodon-feed-view',
								    'props' => [
								      'status' => 'Mastodon feed loaded',
											'account' => $account,
								      'items'  => $items->limit($panelLimit)->values(),
								    ]
								  ];

								} catch (Exception $e) {
									return [
										'component' => 'k-mastodon-feed-view',
										'props' => [
											'status' => 'Failed to load Mastodon feed',
											'error' => $e->getMessage(),
											'items' => []
										]
									];
								}

								// return data from feed class
								return [
									'component' => 'k-mastodon-feed-view',
									'title' => 'Mastodon Feed',
									'props' => [
										'status' => 'Panel area loaded',
									]
								];
							}
						]
					]
				];
			}
		],

		'snippets' => [
			'mastodon_feed' => __DIR__ . '/snippets/mastodon_feed.php'
		]
	]
);
