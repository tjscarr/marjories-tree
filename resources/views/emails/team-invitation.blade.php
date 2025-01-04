@component('mail::message')
{{ __('team.been_invited', ['team' => $invitation->team->name]) }}

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
{{ __('team.no_account') }}

@component('mail::button', ['url' => route('register')])
{{ __('team.create_account') }}
@endcomponent


@component('mail::button', ['url' => $acceptUrl])
{{ __('team.accept_invitation') }}
@endcomponent

{{ __('team.discard') }}
@endcomponent