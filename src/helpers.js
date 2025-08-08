export async function clearMastodonCache($api, $panel) {
	try {
		const res = await $api.post('mastodon-feed/clear-cache');
		if (res.status === 'ok') {
			$panel.notification.success({
				message: "Mastodon cache cleared",
				icon: "check",
				timeout: 5000
			});
		} else if (res.status === 'noop') {
			$panel.notification.info({
				message: "No cache to clear",
				icon: "alert",
				theme: "notice",
				timeout: 5000
			});
		}
	} catch (e) {
		$panel.notification.error({
			message: e.message || "Request failed",
			timeout: 5000
		});
	}
}

export async function refreshFeed($api, $panel) {
	try {
		const res = await $api.post('mastodon-feed/refresh');
		if (res.status === 'ok') {
			$panel.notification.success({
				message: "Mastodon feed refreshed",
				icon: "check",
				timeout: 5000
			});
		} else {
			$panel.notification.info({
				message: res.message || "Nothing to update",
				icon: "refresh",
				timeout: 5000
			});
		}
	} catch (e) {
		$panel.notification.error({
			message: e.message || "Feed refresh failed",
			timeout: 5000
		});
	}
}