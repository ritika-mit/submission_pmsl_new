<x-mail::message>
{{-- Greeting --}}
Dear ***{{ $greeting ?? 'Author' }}***,

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
$color = match ($level) {
'success', 'error' => $level,
default => 'primary',
};
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (!empty($salutation))
{{ $salutation }}
@else
<address>
@lang('Regards'),<br>
@lang('Thank you for your cooperation and with regards.')<br>
@lang('Editor')<br>
{{ config('app.full_name') }}<br>
<a href="https://journals.ramartipublishers.com/PMSL/" target="_blank">https://journals.ramartipublishers.com/PMSL/</a>
</address>
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang("If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n" . 'into your web browser:', compact('actionText')) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>