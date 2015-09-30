<script type="text/html" id="tmpl-audiotheme-gig-venue-details">
	<h5 class="venue-name">{{ data.name }}</h5>

	<# if ( ! data.isAddressEmpty() ) { #>
		<p class="venue-address">
			<# if ( data.address ) { #>
				{{ data.address }}<br>
			<# } #>
			{{ data.city }}, {{ data.state }} {{ data.postal_code }}, {{ data.country }}
		</p>
	<# } #>

	<# if ( data.phone ) { #>
		<p class="venue-phone">{{ data.phone }}</p>
	<# } #>

	<# if ( data.url ) { #>
		<p class="venue-url">{{ data.url }}</p>
	<# } #>
</script>
