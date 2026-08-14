@extends('errors.layout')

@section('title', '419 - Session Expired')
@section('code', '419')
@section('icon', 'bi bi-clock-history')
@section('accent-color', '#8b5cf6')
@section('accent-bg', 'rgba(139, 92, 246, 0.12)')

@section('title', 'Security Session Expired')

@section('message')
Your institutional security session has expired due to inactivity or a CSRF security token timeout. Please return to the previous page, refresh your browser, or sign back into your dashboard to continue securely.
@endsection
