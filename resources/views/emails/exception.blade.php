@component('mail::message')
    Nastala exception {{ $exceptionMessage }}

    stackTrace:
    {{ $exceptionStackTrace }}
@endcomponent