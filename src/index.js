panel.plugin("scottboms/mastodon-feed", {
	components: {
		'k-mastodon-feed-view': {
			props: {
				status: String,
				items: Array,
				error: String
			},
			// placeholder starter template view
			template: `
				<k-view>
					<k-header>Mastodon Feed</k-header>
					<k-text theme="info">{{ status }}</k-text>
					<k-text v-if="error" theme="negative">Error: {{ error }}</k-text>

					<k-box v-if="items.length" theme="info" style="margin-top: 1.5rem;">
<ul>
              <li v-for="(item, index) in items" :key="index" style="margin-bottom: 1rem;">
								<strong>{{ formatDate(item.created_at) }}</strong><br>
								<span v-html="item.content"></span>
							</li>
						</ul>
					</k-box>

					<k-text v-else>No items to show.</k-text>
				</k-view>
			`,
			methods: {
				formatDate(dateString) {
					const date = new Date(dateString);
					return date.toLocaleDateString(undefined, {
						year: 'numeric',
						month: 'short',
						day: 'numeric'
					});
				}
			}
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
