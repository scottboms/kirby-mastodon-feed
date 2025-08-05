<?php

namespace Scottboms\Mastodon;

use Exception;
use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\A;

use function option;

class Feed {
	/*
	 * @var Array
	 */
	private $options;

	private function __construct(array $options = []) {
		$defaults = [
			'username'				=> option('scottboms.mastodon.username'),
			'server'					=> option('scottboms.mastodon.server'),
			'cache'						=> option('scottboms.mastodon.cache'),
			'cachettl'        => option('scottboms.mastodon.cachettl', 900), // 15 minutes (60 * 15)
			'wrapper'					=> option('scottboms.mastodon.wrapper', 'div'),
			'class'						=> option('scottboms.mastodon.class', 'mastodon-feed'),
			'limit'						=> option('scottboms.mastodon.limit', 20),
			'dateformat'			=> option('scottboms.mastodon.dateformat', 'M d, Y'),
			'excludereplies'	=> option('scottboms.mastodon.excludereplies', true),
			'onlymedia'				=> option('scottboms.mastodon.onlymedia', false),
		];

		$this->options = array_merge($defaults, $options);

		// validation: fail early if key values are missing
		if (empty($this->options['username']) || empty($this->options['server'])) {
		  throw new Exception('Mastodon Feed: Required options `username` and `server` must be set.');
		}

		// automatically look up userid if not set
		if (empty($this->options['userid'])) {
		  $this->options['userid'] = $this->getUserId();

			if (empty($this->options['userid'])) {
				throw new Exception('Could not resolve userid');
			}
		}

  }

	/*
	 * @var String
	 */
	protected function getUserId(): ?string
	{
		$cache = kirby()->cache('scottboms.mastodon');
		$cacheKey = 'userid-' . $this->options['username'] . '@' . $this->options['server'];
		
		// return cached id if available
		if ($userId = $cache->get($cacheKey)) {
			return $userId;
		}

		// build lookup url
		$acct = $this->options['username'] . '@' . $this->options['server'];
		$url = 'https://' . $this->options['server'] . '/api/v1/accounts/lookup?acct=' . urlencode($acct);

		// make the request
		$response = Remote::get($url);

		if ($response->code() !== 200) {
			return null;
		}

		$json = $response->json();

		if (!is_array($json) || empty($json['id'])) {
			return null;
		}

		// cache it (for e.g. 24 hours = 86400 seconds)
		$cache->set($cacheKey, $json['id'], 86400);
		return $json['id'];
	}

	/*
	 * @var String
	 */
	protected static function resolveCacheKey(): string
	{
		$instance = new static();
		$userId = $instance->getUserId();
		$server = $instance->options['server'];

		return "mastodon-feed-{$server}-{$userId}";
	}

	/*
	 * @var String
	 */
  public function buildFeedUrl(): string 
	{
		$feedUrl = 'https://' . $this->options['server'] 
			. '/api/v1/accounts/' . $this->options['userid'] 
			. '/statuses?exclude_replies=' . ($this->options['excludereplies'] ? 'true' : 'false')
			. '&only_media=' . ($this->options['onlymedia'] ? 'true' : 'false')
			. '&limit=' . $this->options['limit'];
    
    return $feedUrl;
  }

  public static function getFeed($feedUrl): array|string 
	{
		// fetch and decode the json data
		// see: https://getkirby.com/docs/reference/objects/http/remote/get
		// and https://getkirby.com/docs/reference/objects/http/remote/request
		$cacheKey = static::resolveCacheKey();
		$cache = kirby()->cache('scottboms.mastodon');

		// try to get cached feed
		if ($cached = $cache->get($cacheKey)) {
			return $cached;
		}

		// otherwise, fetch from remote
		$request = Remote::get($feedUrl);

		if ($request->code() === 200) {
			$json_data = $request->json();

			// store in cache, e.g., for 15 minutes
			$ttl = option('scottboms.mastodon.cachettl', 900); // 15 min default
			$cache->set($cacheKey, $json_data, $ttl);

			return $json_data;
		} else {
			return "Feed error returned. Please wait and try again later.";
		}

    return $json_data;
  }

	/*
	 * @var Bool
	 */
	public static function clearFeedCache(): bool 
	{
		$cacheKey = static::resolveCacheKey();
		$cache = kirby()->cache('scottboms.mastodon');

		return $cache->remove($cacheKey);
	}

	/*
	 * @var Array
	 */
	public function formatFeed(array $feed): array 
	{
		$feed = array_slice($feed, 0, $this->options['limit']);

		return array_map(function ($item) {
			$isBoost = empty($item['content']) && isset($item['reblog']);
			$source = $isBoost ? $item['reblog'] : $item;

			return [
				'id' => $item['id'] ?? null, // use original ID, not reblogged one
				'url' => $item['url'] ?? null,
				'originalContent' => $item['content'] ?? '',
				'rebloggedContent' => $item['reblog']['content'] ?? '',
				'content' => $item['content'] ?? ($item['reblog']['content'] ?? ''),
				'author' => $item['account']['display_name'] ?? $item['account']['username'] ?? '',
				'reblogAuthor' => $isBoost
				  ? [
						'name' => $item['reblog']['account']['display_name'] ?? $item['reblog']['account']['username'] ?? '',
						'url' => $item['reblog']['account']['url'] ?? null,
						]
				  : null,
				'avatar' => $item['account']['avatar_static'] ?? null,
				'date' => isset($item['created_at'])
				  ? date($this->options['dateformat'] ?? 'Y-m-d', strtotime($item['created_at']))
				  : '',
				'isBoost' => $isBoost,
				'attribution' => $isBoost
					? 'Boosted from @' . ($source['account']['acct'] ?? 'unknown')
					: '',
				'media' => array_map(function ($attachment) {
					return [
						'url'   => $attachment['url'] ?? null,
						'previewUrl' => $attachment['preview_url'] ?? null,
						'type'  => $attachment['type'] ?? null,
						'description' => $attachment['description'] ?? '',
					];
				}, $source['media_attachments'] ?? []),
				'applicationName' => $item['application']['name'] ?? null,
				'applicationWebsite' => $item['application']['website'] ?? null,
			];
		}, $feed);
	}

	/*
	 * @var Array
	 */
	public static function formattedFeed(): array 
	{
		$instance = new static();
		$url = $instance->buildFeedUrl();
		$raw = static::getFeed($url);

		if (!is_array($raw)) {
			return [];
		}

		$formatted = $instance->formatFeed($raw);

		// detect empty feed with only_media filter and output message
		if ($instance->options['onlymedia'] && empty($formatted)) {
			return [[
				'content' => 'No media posts found.',
				'author'  => '',
				'date'    => '',
				'url'     => '',
				'isBoost' => false,
				'avatar'  => null,
				'attribution' => '',
				'reblogAuthor' => null,
				'media' => [],
				'originalContent' => '',
				'rebloggedContent' => '',
				'isNotice' => true // used to output message in snippet
			]];
		}

		return $formatted;
	}

}