<?php

use Scottboms\Mastodon\Feed;

return [
	[
		'pattern' => 'mastodon-feed/clear-cache',
		'method'  => 'POST',
		'auth'    => true, // require a panel-authenticated user
		'action'  => function () {
			// restrict to admins
			$user = kirby()->user();
			if (!$user || !$user->isAdmin()) {
				throw new PermissionException('Not allowed.');
			}

			try {
				$ok = Feed::clearCache();
				return [
					'status'  => $ok ? 'ok' : 'noop',
					'message' => $ok ? 'Mastodon cache cleared.' : 'Nothing to clear.'
				];
			} catch (Throwable $e) {
				// return a clean error to the panel
				return [
					'status'  => 'error',
					'message' => 'Cache clear failed: ' . $e->getMessage()
				];
			}
		}
	]
];
