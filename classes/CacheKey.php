<?php

namespace Scottboms\Mastodon;

use Exception;

class CacheKey
{
	public static function forFeed(string $server, string $userId): string
	{
		if (empty($server) || empty($userId)) {
			throw new Exception('Cannot resolve feed cache key: missing server or userId.');
		}

		return "mastodon-feed-{$server}-{$userId}";
	}

	public static function forUserId(string $username, string $server): string
	{
		if (empty($username) || empty($server)) {
			throw new Exception('Cannot resolve userid cache key: missing username or server.');
		}

		return "userid-{$username}@{$server}";
	}

	public static function forAccountInfo(string $username, string $server): string
	{
		if (empty($username) || empty($server)) {
			throw new Exception('Cannot resolve account info cache key: missing username or server.');
		}

		return "account-info-{$username}@{$server}";
	}
}
