<template>
	<div>
  	<k-panel-inside>
			<k-header class="k-site-view-header">Mastodon Feed
				<k-button-group slot="buttons">
					<k-button
						icon="open"
						variant="filled"
						size="sm"
						title="Open Profile"
						@click="goToProfile"
					/>

					<k-button
						icon="refresh"
						variant="filled"
						theme="blue-icon"
						size="sm"
						title="Refresh Feed"
						text="Refresh Feed"
						@click="refreshFeedCache"
					/>

					<k-button
						icon="mastodon"
						variant="filled"
						theme="purple-icon"
						size="sm"
						title="Clear Cache"
						text="Clear Cache"
						@click="clearCache"
					/>
				</k-button-group>
			</k-header>

			<k-section>
				<k-box theme="white" class="k-mastodon-profile">
					<k-image :src="account.avatar_static" alt="Avatar" class="k-mastodon-avatar" />
					<k-html-field-preview :value="account.note" class="k-mastodon-note" />
				</k-box>
			</k-section>

			<k-section class="k-mastodon-stats">
				<k-stats :reports="[{ label: account.username, value: account.display_name, link: account.uri, icon: 'account', info: account.server }, { label: 'Followers', value: account.followers_count, icon: 'followers' }, { label: 'Following', value: account.following_count, icon: 'following' }, { label: 'Toots', value: account.statuses_count, icon: 'megaphone', info: account.last_status_at }]" size="huge" class="k-mastodon-info-reports" />
			</k-section>

			<k-section label="Latest Toots">
				<k-text v-if="error" theme="negative" icon="alert">{{ error }}</k-text>

				<div v-if="items.length" class="k-collection k-mastodon-toots">
					<div class="k-items k-cards k-cards-items" data-layout="cards" data-size="huge">
						<div v-for="(item, index) in items" :key="index" class="k-item k-cards-item k-mastodon-toot">

							<template v-if="firstMedia(originalStatus(item))">
								<k-image-frame
									:src="firstMedia(originalStatus(item)).src"
									:alt="firstMedia(originalStatus(item)).alt"
									class="k-mastodon-media"
									back="pattern"
									ratio="4/3"
									cover
									style="width: 100%" />
							</template>

							<div class="k-mastodon-boosted">
								<k-box v-if="isBoost(item)" class="k-mastodon-boost-header" data-theme="blue" icon="boost">
									<a href="{{ item.reblogAuthor.url }}">@{{ item.reblogAuthor.name }}</a>
								</k-box>

								<div v-if="isBoost(item)" class="k-item-content k-mastodon-boost-content">
									<k-box class="k-item-content" data-theme="gray">
										<div v-html="item.rebloggedContent" />
									</k-box>
								</div>
							</div>

							<div v-if="originalStatus(item)" class="k-item-content">
								<div class="k-item-title" v-html="originalStatus(item).content" />
								<div class="k-mastodon-meta k-item-info">
									{{ formatDate(originalStatus(item).created_at || item.date) }}
									<span v-if="item.applicationName">• {{ item.applicationName }}</span>
								</div>
							</div>

							<k-bar class="k-mastodon-item-details">
								<k-box icon="replies" style="--columns: 2; gap: 0.25rem; justify-content: center" :text="item.repliesCount" />
								<k-box icon="star" style="--columns: 2; gap: 0.25rem; justify-content: center" :text="item.favouritesCount" />
								<k-box icon="boost" style="--columns: 2; gap: 0.25rem; justify-content: center" :text="item.reblogsCount" />
							</k-bar>

						</div>
					</div>
				</div>
			</k-section>
  	</k-panel-inside>
	</div>
</template>

<script>
import { clearMastodonCache, refreshFeed } from "../helpers.js";
export default {
	name: 'MastodonFeed',
	props: {
		status: String,
		account: {
			type: Object,
			default: () => ({})
	  },
		items: {
			type: Array,
			default: () => []
	  },
		error: String
	},
	methods: {
		formatDate(dateString) {
			const date = new Date(dateString);
			return date.toLocaleDateString(undefined, {
				year: 'numeric',
				month: 'short',
				day: 'numeric'
			});
		},

		// is this a boosted post?
	  isBoost(item) {
			return !!item?.reblog || item?.isBoost === true;
		},

		// original status for content/media
		originalStatus(item) {
			return item?.reblog ?? item;
		},

		firstMedia(item) {
			const candidates =
				item?.media ||
				item?.media_attachments ||
				item?.attachments ||
				[];

			if (!Array.isArray(candidates) || candidates.length === 0) return null;

		  // prefer images, otherwise fall back to the first attachment
			const pick =
				candidates.find(a => a?.type === 'image') ??
				candidates[0];

			if (!pick) return null;

			// mastodon usually includes a preview_url for images; fall back to url
			const src = pick.previewUrl || pick.url;
			if (!src) return null;

			return {
				src,
				alt: pick.description || pick.alt || 'Mastodon media'
			};
		},

		async goToProfile() {
			if (!this.account.url) {
				this.$panel.notification.error('No Mastodon profile URL found');
				return;
			}

			const url = this.account.url.startsWith('http')
				? this.account.url
				: `https://${this.account.url}`;
			window.open(url, '_blank');
		},

		async clearCache() {
			this.loading = true;
			try {
				await clearMastodonCache(this.$api, this.$panel);
				// refresh the view
				if (this.$reload) {
					await this.$reload(); // panel helper
				} else if (this.$view?.reload) {
					await this.$view.reload(); // older/newer variant
				} else {
					location.reload(); // brute force fallback
				}
			} finally {
				this.loading = false;
			}
		},

		async refreshFeedCache() {
			console.log('clicked refresh feed cache button');
			this.loading = true;
			try {
				await refreshFeed(this.$api, this.$panel);
				// refresh the view
				if (this.$reload) {
					await this.$reload(); // panel helper
				} else if (this.$view?.reload) {
					await this.$view.reload(); // older/newer variant
				} else {
					location.reload(); // brute force fallback
				}
			} finally {
				this.loading = false;
			}
		},

	}
}
</script>

<style>
.k-mastodon-profile {
	align-items: start;
}

.k-mastodon-avatar {
	border-radius: var(--rounded);
	height: auto;
	margin: 2px 0;
	width: 6rem;
}

.k-mastodon-note {
	width: calc(100% - 7rem);
}

@media only screen and (min-width: 36em) {
	.k-mastodon-note {
		width: 60%;
	}
}

.k-section.k-mastodon-stats {
	margin-top: .2rem !important;
}

.k-mastodon-media {
  display: block;
  margin-bottom: .5rem;
  border-radius: var(--rounded);
}

.k-mastodon-toot {
	position: relative;
}

.k-mastodon-boost-header {
	border-radius: var(--rounded);
	display: flex;
	gap: .5rem;
	align-items: center;
	font-size: var(--text-xs);
	padding: var(--spacing-3) var(--spacing-2);
}

.k-mastodon-boost-content {
	font-size: var(--text-xs);
}

.k-mastodon-boost-content .k-item-content div {
	hyphens: manual;
	inline-size: 100%;
	overflow-wrap: break-word;
}

.k-mastodon-toot .k-item-content p {
	margin-bottom: var(--spacing-3);
}

.k-mastodon-item-details {
	border-top: 1px solid light-dark(var(--color-gray-250), var(--color-gray-800));
	gap: 0.1rem;
	margin-top: var(--spacing-8);
	padding: var(--spacing-6) var(--spacing-2);
	position: absolute;
	bottom: 0;
	width: 100%;
}

.k-mastodon-meta {
	padding-top: var(--spacing-1);
	padding-bottom: 4rem;
}
</style>
