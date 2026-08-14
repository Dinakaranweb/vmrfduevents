@extends('errors.layout')

@section('title', '403 - Forbidden Access')
@section('code', '403')
@section('icon', 'bi bi-shield-slash-fill')
@section('accent-color', '#ef4444')
@section('accent-bg', 'rgba(239, 68, 68, 0.12)')

@section('title', 'Access Forbidden by Authority')

@section('message')
Your assigned institutional role does not have the required authorization permissions to view or perform actions on this resource. Under VMRFDU governance policy, access is strictly limited based on role and assigned college hierarchy. If the Super Admin grants the required permission, this resource will become accessible based on your assigned role and constituent college access.
@endsection
