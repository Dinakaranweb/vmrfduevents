@extends('errors.layout')

@section('title', '500 - Internal Server Error')
@section('code', '500')
@section('icon', 'bi bi-hdd-network-fill')
@section('accent-color', '#dc2626')
@section('accent-bg', 'rgba(220, 38, 38, 0.12)')

@section('title', 'System Execution Fault')

@section('message')
An unexpected internal server error occurred while processing your request. Our technical administration desk has logged the diagnostic trace. If the problem persists when submitting large files, verify your connection and try again, or contact portal administration.
@endsection
