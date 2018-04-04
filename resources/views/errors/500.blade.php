@extends('errors::layout')

@section('title', 'Error')

@section('message', 'Whoops, looks like something went wrong.'.(isset($incidentCode) ? ' Please contact support with incident code: '.$incidentCode : ''))
