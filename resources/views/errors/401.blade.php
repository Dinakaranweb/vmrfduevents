@extends('errors.layout')

@section('title', '401 - Unauthorized Access')
@section('code', '401')
@section('icon', 'bi bi-shield-lock-fill')
@section('accent-color', '#f59e0b')
@section('accent-bg', 'rgba(245, 158, 11, 0.12)')

@section('title', 'Authentication Required')

@section('message')
You must be authenticated with valid VMRFDU institutional credentials to access this protected area. Your session may have expired or you have not yet signed in. Please return to the login portal and authenticate to continue.
@endsection
