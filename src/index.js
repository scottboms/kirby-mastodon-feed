import MastodonFeed from "./components/MastodonFeed.vue";

import { icons } from "./icons.js";
import { clearMastodonCache } from "./helpers.js";

panel.plugin("scottboms/mastodon-feed", {
	icons,
	components: {
		'k-mastodon-feed-view': MastodonFeed
	},
	viewButtons: {
		mastodoncache: {
			template: `
				<k-button
					icon="mastodon"
					variant="filled"
					theme="purple-icon"
					size="sm"
					title="Clear Cache"
					text="Clear Cache"
					@click="clearCache"
				/>`,
			data() {
				return { loading: false };
			},
			methods: {
				async clearCache() {
					this.loading = true;
					try {
						await clearMastodonCache(this.$api, this.$panel);
					} finally {
						this.loading = false;
					}
				}
			},
		},
	},
});
