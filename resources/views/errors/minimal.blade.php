@extends('errors.layout')

@section('title', $exception->getMessage() ?: 'Unexpected Application Error')
@section('code', method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 'ERROR')
@section('icon', 'bi bi-exclamation-octagon-fill')
@section('accent-color', '#dc2626')
@section('accent-bg', 'rgba(220, 38, 38, 0.12)')

@section('title', 'Unexpected Application Fault')

@section('message')
{{ $exception->getMessage() ?: 'An unexpected application fault has occurred within the VMRFDU Activity Portal. Please return to your executive dashboard or notify system administration if the issue persists.' }}
@endsection
