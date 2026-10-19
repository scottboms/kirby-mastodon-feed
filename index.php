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
use Kirby\Cms\App as Kirby;
use Kirby\Toolkit\Collection;

$version = Kirby::version() ?? '0.0.0';
if (
  version_compare($version, '6.0.0-beta.1', '<') ||
  version_compare($version, '7.0.0', '>=')
) {
  throw new Exception('Mastodon Feed requires Kirby 6');
}

Kirby::plugin('scottboms/mastodon-feed',
	info: [
		'homepage' => 'https://github.com/scottboms/kirby-mastodon-feed',
		'license'  => 'MIT',
		'version'  => '6.0.0',
		'authors'  => [[ 'name' => 'Scott Boms' ]]
	],

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

									// error_log('[Mastodon] Collection count: ' . $items->count());
									// error_log('[Mastodon] First item: ' . print_r($items->first(), true));

									$feed = Feed::build();
									$account = $feed->getAccountInfo();

									$panelLimit = max(
										1,
										(int) option('scottboms.mastodon-feed.panel.limit', 12)
									);

									// Convert the collection to a plain array of associative arrays
									$panelItems = [];

									foreach ($items->limit($panelLimit) as $item) {
										$panelItems[] = $item->toArray();
									}

									//error_log('[Mastodon] Panel item count: ' . count($panelItems));

									return [
										'component' => 'k-mastodon-feed-view',
										'title' => 'Mastodon Feed',
										'props' => [
											'status'  => 'Mastodon feed loaded',
											'account' => $account,
											'items'   => $panelItems,
										]
									];

								} catch (Exception $e) {
									return [
										'component' => 'k-mastodon-feed-view',
										'title' => 'Mastodon Feed',
										'props' => [
											'status'  => 'Mastodon feed loaded',
											'account' => $account,
											'items'   => $panelItems,
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
