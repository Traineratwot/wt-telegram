<head>
	<meta charset="utf-8">
	<title>Telegram: Contact @{$BOT_NAME}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<meta property="og:title" content="{$BOT_NAME}">
	<meta property="og:image" content="https://telegram.org/img/t_logo.png">
	<meta property="og:site_name" content="Telegram">
	<meta property="og:description" content="You can contact @{$BOT_NAME} right away.">

	<meta property="twitter:title" content="{$BOT_NAME}">
	<meta property="twitter:image" content="https://telegram.org/img/t_logo.png">
	<meta property="twitter:site" content="@Telegram">

	<meta property="al:ios:app_store_id" content="686449807">
	<meta property="al:ios:app_name" content="Telegram Messenger">
	<meta property="al:ios:url" content="tg://resolve?domain={$BOT_NAME}">

	<meta property="al:android:url" content="tg://resolve?domain={$BOT_NAME}">
	<meta property="al:android:app_name" content="Telegram">
	<meta property="al:android:package" content="org.telegram.messenger">

	<meta name="twitter:card" content="summary">
	<meta name="twitter:site" content="@Telegram">
	<meta name="twitter:description" content="You can contact @{$BOT_NAME} right away.
">
	<meta name="twitter:app:name:iphone" content="Telegram Messenger">
	<meta name="twitter:app:id:iphone" content="686449807">
	<meta name="twitter:app:url:iphone" content="tg://resolve?domain={$BOT_NAME}">
	<meta name="twitter:app:name:ipad" content="Telegram Messenger">
	<meta name="twitter:app:id:ipad" content="686449807">
	<meta name="twitter:app:url:ipad" content="tg://resolve?domain={$BOT_NAME}">
	<meta name="twitter:app:name:googleplay" content="Telegram">
	<meta name="twitter:app:id:googleplay" content="org.telegram.messenger">
	<meta name="twitter:app:url:googleplay" content="https://t.me/{$BOT_NAME}">

	<meta name="apple-itunes-app" content="app-id=686449807, app-argument: tg://resolve?domain={$BOT_NAME}">
	<link rel="shortcut icon" href="//telegram.org/favicon.ico?3" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet" type="text/css">
	<!--link href="/css/myriad.css" rel="stylesheet"-->
	<link href="//telegram.org/css/bootstrap.min.css?3" rel="stylesheet">
	<link href="//telegram.org/css/telegram.css?212" rel="stylesheet" media="screen">
	<script>(function() {
			let isEmbed = true
			let playback
			let quality = 'highres'
			onYouTubePlayerReady = function(e) {
				document.addEventListener('updateQuality', (e => { quality = e.quality, updQuality()})), playback = !1, globalPlayer = e, globalPlayer.addEventListener('onStateChange', (e => { 3 !=
																																																 e || playback ? -1 == e ? playback = !1 : 5 == e && (isEmbed = !0) : updQuality()})), globalPlayer.addEventListener('onPlaybackQualityChange', (e => {
					if(isEmbed) {
						null === globalPlayer.getAvailableQualityLevels && (document.removeEventListener('updateQuality', (e => { quality = e.quality, updQuality()})), globalPlayer = document
							.getElementById('movie_player'))
						const e = globalPlayer.getAvailableQualityLevels(), t = undefined, a = undefined;
						(-1 == e.indexOf(quality) ? e[0] : quality) === globalPlayer.getPlaybackQuality() || updQuality(), isEmbed = !1
					}
				})), updQuality()
			}

			function updQuality() {
				const e = globalPlayer.getAvailableQualityLevels(),
					  t = e.length && -1 == e.indexOf(quality) ? e[0] : quality, a = globalPlayer.getPlayerState()
				playback = !0
				const l = (new Date).getTime(), i = { creation: l, data: t, expiration: l + 2592e6}
				localStorage['yt-player-quality'] = JSON.stringify(i), globalPlayer.setPlaybackQuality(t)
			}
		})()</script>
	<link rel="prefetch">
</head>
<body>

<div class="tgme_page_wrap">
	<div class="tgme_head_wrap">
		<div class="tgme_head">
			<a href="//telegram.org/" class="tgme_head_brand">
				<i class="tgme_logo"></i>
			</a>
		</div>
	</div>
	<a class="tgme_head_dl_button" href="//telegram.org/dl?tme=e3140493dabfa81f85_15926313986922829064">
		Don't have <strong>Telegram</strong> yet? Try it now!<i class="tgme_icon_arrow"></i>
	</a>
	<div class="tgme_page">

		<div class="tgme_page_title"><span dir="auto">{$BOT_NAME}</span></div>
		<div class="tgme_page_extra">
			@{$BOT_NAME}
		</div>

		<div class="tgme_page_action">
			<a class="tgme_action_button_new" href="tg://resolve?domain={$BOT_NAME}">Send Message</a>
		</div>
        {*		<div class="tgme_page_action">*}
        {*			<a class="tgme_action_button_new" href="https://t.me/+Rm02pglMzkw5ZDUy">Join Channel</a>*}
        {*		</div>*}

		<div class="tgme_page_additional">
			If you have <strong>Telegram</strong>, you can contact <br><strong>{$BOT_NAME}</strong> right away.
		</div>
	</div>
</div>

<div id="tgme_frame_cont"></div>

<script type="text/javascript">
	const protoUrl = "tg:\/\/resolve?domain={$BOT_NAME}"
	if(protoUrl) {
		setTimeout(function() {
			window.location = protoUrl
		}, 100)
	}
</script>
<script></script>


<!-- page generated in 11.17ms -->
</body>