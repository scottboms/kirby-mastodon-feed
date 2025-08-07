<template>
	<div>
  	<k-panel-inside>
			<k-header class="k-site-view-header">Mastodon Feed
				<k-button-group slot="buttons">
					<k-button
						icon="mastodon"
						variant="filled"
						theme="purple-icon"
						size="sm"
						text="Clear Cache"
						:link="mastodon/clear-cache"
					/>
				</k-button-group>
			</k-header>

			<k-grid class="k-mastodon-info-container" variant="columns" style="">
				<k-box class="k-mastodon-profile" theme="white" style="--width: 1/4">
					<k-image :src="account.avatar_static" alt="Avatar" style="width: 70px; height: 70px;" />
					<div class="k-mastodon-profile">
						<a href="{{ account.uri }}">
							<h1>{{ account.display_name }}</h1>
							<dl>
								<dt>{{ account.username }}</dt>
								<dd>{{ account.id }}</dd>
							</dl>
						</a>
					</div>
				</k-box>

				<k-box theme="white" class="k-mastodon-note" style="--width: 1/2">
					<k-html-field-preview :value="account.note" />
				</k-box>

				<k-box theme="white" class="k-mastodon-counts" style="--width: 1/4">
					<k-stat label="Followers" :value="account.followers_count" icon="followers" theme="info" size="huge" class="k-mastodon-stat" />
					<k-stat label="Following" :value="account.following_count" icon="following" theme="info" class="k-mastodon-stat" />
				</k-box>
			</k-grid>


			<k-section>
				<k-stats :reports="[{ label: account.username, value: account.display_name, link: account.uri, icon: 'account' }, { label: 'Followers', value: account.followers_count, icon: 'followers' }, { label: 'Following', value: account.following_count, icon: 'following' }]" size="huge" class="k-mastodon-info-reports" />
			</k-section>

			<k-section label="Latest Items from Profile">
				<k-text v-if="error" theme="negative">Error: {{ error }}</k-text>

				<k-grid v-if="items.length" variant="fields">
					<k-box v-for="(item, index) in items" :key="index" theme="white" style="--width: 1/4">
						<div v-html="item.content" />
						<div>
							<strong>{{ item.date }}</strong>
						</div>
					</k-box>
				</k-grid>

			</k-section>

			<k-section><pre>{{ items }}</pre></k-section>


  	</k-panel-inside>
	</div>
</template>

<script>
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
		}
	}
}
</script>

<style>
.k-mastodon-info-container {
	display: flex;
	flex-flow: columns wrap;
	justify-content: flex-start;
	align-items: flex-start;
	align-content: flex-start;
	flex-grow: 1;
	gap: .1rem;
}

.k-mastodon-avatar {

}

.k-mastodon-note {
	max-width: 50%;
}

.k-mastodon-stat {

}

</style>