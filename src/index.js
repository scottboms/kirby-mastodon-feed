panel.plugin("scottboms/mastodon-feed", {
	components: {
		'k-mastodon-feed-view': {
			props: {
				status: String
			},
			// placeholder starter template view
			template: `
				<k-view>
					<k-header>Mastodon Feed</k-header>
					<k-text>{{ status }}</k-text>
				</k-view>
			`
		}
	},
	viewButtons: {
		mastodoncache: {
			template: `
				<k-button
					icon="mastodon"
					variant="filled"
					theme="purple-icon"
					size="sm"
					@click="clearCache"
				>Clear Cache
				</k-button>`,
			data() {
				return { loading: false };
			},
			methods: {
				async clearCache() {
					// console.log('log','Mastodon Cache button click registered');
					this.loading = true;
					try {
						const res = await this.$api.post('mastodon/clear-cache');
						if (res.status === 'ok') {
							this.$panel.notification.success({
							  message: "Mastodon cache cleared",
								icon: "check",
							  timeout: 5000
							});
						} else if (res.status === 'noop') {
							this.$panel.notification.info({
							  message: "No cache to clear",
								icon: "alert",
								theme: "notice",
							  timeout: 5000
							});
						}
					} catch (e) {
						this.$panel.notification.error({
						  message: e.message || "Request failed",
						  timeout: 5000
						});
					} finally {
						this.loading = false;
					}
				}
			},
		},
	},
});
