@extends('errors.layout')

@section('title', '404 - Page Not Found')
@section('code', '404')
@section('icon', 'bi bi-compass-fill')
@section('accent-color', '#3b82f6')
@section('accent-bg', 'rgba(59, 130, 246, 0.12)')

@section('title', 'Requested Page Not Found')

@section('message')
The page, institutional activity record, or resource you are searching for does not exist, has been moved, or is no longer accessible within the active VMRFDU directory. Please verify the URL or return to your dashboard to navigate to valid records.
@endsection
