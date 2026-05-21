<x-mail::message>
  # You've been invited!

  You have been invited to join the trip **{{ $invitation->trip->name }}** as a {{ ucfirst($invitation->role) }}.

  <x-mail::button :url="route('invitations.accept', $invitation->token)">
    Accept Invitation
  </x-mail::button>

  Thanks,<br>
  {{ config('app.name') }}
</x-mail::message>