(function(){"use strict";function n(t,e,o,m,l,u,f,h){var a=typeof t=="function"?t.options:t;return e&&(a.render=e,a.staticRenderFns=o,a._compiled=!0),{exports:t,options:a}}const s={methods:{name:"MastodonFeed",props:{status:String,items:Array,error:String},formatDate(t){return new Date(t).toLocaleDateString(void 0,{year:"numeric",month:"short",day:"numeric"})}}};var i=function(){var e=this,o=e._self._c;return o("div",[o("k-panel-inside",[o("k-view",{staticClass:"k-mastodon-feed-view"},[o("k-header",[e._v("Mastodon Feed")]),o("k-section",{attrs:{label:"Latest Mastodon Feed Items"}},[o("k-text",{attrs:{theme:"info"}},[e._v(e._s(e.status))]),e.error?o("k-text",{attrs:{theme:"negative"}},[e._v("Error: "+e._s(e.error))]):o("k-text",[e._v("No items to show.")])],1)],1)],1)],1)},r=[],c=n(s,i,r);const d=c.exports;panel.plugin("scottboms/mastodon-feed",{components:{"k-mastodon-feed-view":d},viewButtons:{mastodoncache:{template:`
				<k-button
					icon="mastodon"
					variant="filled"
					theme="purple-icon"
					size="sm"
					@click="clearCache"
				>Clear Cache
				</k-button>`,data(){return{loading:!1}},methods:{async clearCache(){this.loading=!0;try{const t=await this.$api.post("mastodon/clear-cache");t.status==="ok"?this.$panel.notification.success({message:"Mastodon cache cleared",icon:"check",timeout:5e3}):t.status==="noop"&&this.$panel.notification.info({message:"No cache to clear",icon:"alert",theme:"notice",timeout:5e3})}catch(t){this.$panel.notification.error({message:t.message||"Request failed",timeout:5e3})}finally{this.loading=!1}}}}}})})();
