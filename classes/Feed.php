<?php

namespace Scottboms\Mastodon;

use Exception;
use Kirby\Data\Json;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;
use Scottboms\Mastodon\CacheKey;

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
		$cacheKey = CacheKey::forUserId($this->options['username'], $this->options['server']);

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
	 * factory constructor
	 * @var Array
	 */
	public static function build(array $options = []): self
	{
		return new self($options);
	}

	/*
	 * @var Array
	 */
	public function getAccountInfo(): array
	{
		$cache = kirby()->cache('scottboms.mastodon');
		$cacheKey = CacheKey::forAccountInfo($this->options['username'], $this->options['server']);

		// try to return cached account info
		if ($cached = $cache->get($cacheKey)) {
			return $cached;
		}

		// build the lookup url
		$acct = $this->options['username'] . '@' . $this->options['server'];
		$url = 'https://' . $this->options['server'] . '/api/v1/accounts/lookup?acct=' . urlencode($acct);

		$response = Remote::get($url);
		if ($response->code() !== 200) {
			throw new Exception('Failed to fetch account info');
		}

		$json = $response->json();

		if (!is_array($json) || empty($json['id'])) {
			throw new Exception('Invalid account data received');
		}

		// info to return from the api
		$account = [
			'id'              => $json['id'] ?? null,
			'username'        => $json['username'] ?? null,
			'display_name'    => $json['display_name'] ?? null,
			'server'          => $this->options['server'] ?? null,
			'avatar_static'   => $json['avatar_static'] ?? null,
			'url'             => $json['url'] ?? null,
			'note'            => $json['note'] ?? null,
			'followers_count' => $json['followers_count'] ?? null,
			'following_count' => $json['following_count'] ?? null,
			'statuses_count'  => $json['statuses_count'] ?? null,
			'last_status_at'  => $json['last_status_at'] ?? null,
		];

		// cache the result
		$ttl = $this->options['cachettl'] ?? 900;
		$cache->set($cacheKey, $account, $ttl);

		return $account;
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

  public static function getFeed($feedUrl, array $options = []): array|string
	{
		// fetch and decode the json data
		// see: https://getkirby.com/docs/reference/objects/http/remote/get
		// and https://getkirby.com/docs/reference/objects/http/remote/request

    $instance = static::build($options);
    $cache = kirby()->cache('scottboms.mastodon');

    $server = $instance->options['server'];
    $userId = $instance->options['userid'];

		$cacheKey = CacheKey::forFeed($server, $userId);

		// try to get cached feed
		if ($cached = $cache->get($cacheKey)) {
			return $cached;
		}

		// otherwise, fetch from remote
		$request = Remote::get($feedUrl);

		if ($request->code() === 200) {
			$json_data = $request->json();

			// store in cache, e.g., for 15 minutes
			$ttl = $instance->options['cachettl'] ?? 900;
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
	public static function clearCache(array $options = []): bool
	{
		$cache = kirby()->cache('scottboms.mastodon');
		// flush the entire cache
		return (bool) $cache->flush();
	}

	/*
	 * @var Object
	 */
	public function formatFeed(array $feed): array
	{
		$feed = array_slice($feed, 0, $this->options['limit']);

		return array_map(function ($item) {
			$isBoost = empty($item['content']) && isset($item['reblog']);
			$source = $isBoost ? $item['reblog'] : $item;

			// build media as obj[]
			$media = array_map(function ($attachment) {
				return new Obj([
					'url'         => $attachment['url'] ?? null,
					'previewUrl'  => $attachment['preview_url'] ?? null,
					'type'        => $attachment['type'] ?? null,
					'description' => $attachment['description'] ?? '',
				]);
			}, $source['media_attachments'] ?? []);

			// return an obj for the item
			return new Obj([
				'id'                 => $item['id'] ?? null,
				'url'                => $item['url'] ?? null,
				'author'             => $item['account']['display_name'] ?? $item['account']['username'] ?? '',
				'username'           => $item['account']['username'] ?? '',
				'avatar'             => $item['account']['avatar_static'] ?? null,
				'originalContent'    => $item['content'] ?? '',
				'rebloggedContent'   => $item['reblog']['content'] ?? '',
				'content'            => $item['content'] ?? ($item['reblog']['content'] ?? ''),
				'isBoost'            => $isBoost,
				'reblogAuthor'       => $isBoost ? new Obj([
					'name' => $item['reblog']['account']['display_name'] ?? $item['reblog']['account']['username'] ?? '',
					'url'  => $item['reblog']['account']['url'] ?? null,
				]) : null,
				'attribution'        => $isBoost ? 'Boosted from @' . ($source['account']['acct'] ?? 'unknown') : '',
				'date'               => isset($item['created_at'])
					? date($this->options['dateformat'] ?? 'Y-m-d', strtotime($item['created_at']))
					: '',
				'media'              => $media, // array of obj
				'applicationName'    => $item['application']['name'] ?? null,
				'applicationWebsite' => $item['application']['website'] ?? null,
			]);
		}, $feed);
	}

	/*
	 * @var Object
	 */
	public static function formattedFeed(): array|Collection
	{
		$instance = static::build();
		$url = $instance->buildFeedUrl();
		$raw = static::getFeed($url);

		if (!is_array($raw)) {
			return new Collection([]);
		}

		$items = $instance->formatFeed($raw);

		if ($instance->options['onlymedia'] && empty($items)) {
		$items = [/* obj as above */];
		}

		return new Collection($items);
	}

}
