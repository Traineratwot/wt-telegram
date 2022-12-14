<nav class="navbar navbar-expand-lg navbar-light bg-light">
	<div class="container-fluid">
		<a class="navbar-brand" href="/">Menu</a>
		<button
				class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
				aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"
		>
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {if $isAuthenticated}
					<li class="nav-item">
						<a class="nav-link active" aria-current="page" href="/user/profile">{t}profile{/t}</a>
					</li>
                {else}
					<li class="nav-item">
						<a class="nav-link active" aria-current="page" href="/user/login">{t}login{/t}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" aria-current="page" href="/user/register">{t}register{/t}</a>
					</li>
                {/if}
			</ul>
            {if $isAuthenticated}
				<form class="d-flex" action="/user/logout" id="Logout">
					<button class="btn btn-outline-info" type="submit">{t}logout{/t}</button>
				</form>
            {/if}
		</div>
		<script>
			$('#Logout').on('success', function() {
				document.location.href = '/'
			})
		</script>
	</div>
</nav>
