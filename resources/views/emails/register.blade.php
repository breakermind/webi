<div class="row">
@lang('Welcome') {{ $user->name }}
</div>
<div class="row">
	<p> @lang('Confirm your email address') </p>
	<a href="{{ request()->getSchemeAndHttpHost() }}/activate/{{ $user->id }}/{{ $user->code }}" target="_blank"> @lang('Confirm email') </a>
</div>