<div class="row">
@lang('Welcome') {{ $user->name }}
</div>
<div class="row">
	<p> @lang('Your new password') </p>
	<password>{{ $password }}</password>
</div>