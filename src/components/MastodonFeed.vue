<template>
	<div>
  	<k-panel-inside>
  		<k-view class="k-mastodon-feed-view">
  			<k-header>Mastodon Feed</k-header>
				<k-section label="Latest Mastodon Feed Items">
  				<k-text theme="info">{{ status }}</k-text>
  				<k-text v-if="error" theme="negative">Error: {{ error }}</k-text>
  				<k-text v-else>No items to show.</k-text>
				</k-section>
  		</k-view>
  	</k-panel-inside>
	</div>
</template>

<script>
export default {
  
	methods: {
    name: 'MastodonFeed',
		props: {
			status: String,
			items: Array,
			error: String
		},
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