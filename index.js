(function(){"use strict";panel.plugin("scottboms/mastodon-feed",{components:{"k-mastodon-feed-view":{props:{status:String},template:`
				<k-view>
					<k-header>Mastodon Feed</k-header>
					<k-text>{{ status }}</k-text>
				</k-view>
			`}},viewButtons:{mastodoncache:{template:`
				<k-button
					icon="mastodon"
					variant="filled"
					theme="purple-icon"
					size="sm"
					@click="clearCache"
				>Clear Cache
				</k-button>`,data(){return{loading:!1}},methods:{async clearCache(){this.loading=!0;try{const e=await this.$api.post("mastodon/clear-cache");e.status==="ok"?this.$panel.notification.success({message:"Mastodon cache cleared",icon:"check",timeout:5e3}):e.status==="noop"&&this.$panel.notification.info({message:"No cache to clear",icon:"alert",theme:"notice",timeout:5e3})}catch(e){this.$panel.notification.error({message:e.message||"Request failed",timeout:5e3})}finally{this.loading=!1}}}}}})})();
