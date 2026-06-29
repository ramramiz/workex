@extends('errors.layout')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Access Denied. You do not have permission to view this page.'))
