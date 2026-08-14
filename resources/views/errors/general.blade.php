@extends('errors.layout')

@section('title', 'Application Error')
@section('code', 'ERR')
@section('icon', 'bi bi-exclamation-octagon-fill')
@section('accent-color', '#dc2626')
@section('accent-bg', 'rgba(220, 38, 38, 0.12)')

@section('title', 'Unexpected Application Error')

@section('message')
An unexpected application error has occurred within the VMRFDU Activity Portal. Please try your operation again or navigate back to the dashboard.
@endsection
