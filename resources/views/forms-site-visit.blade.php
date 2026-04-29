@extends('layouts.dashboard')
@section('title', 'Site Visit Form')
@section('content')
<style>
  .sv-iframe-wrap { padding: 0; margin: 0; height: calc(100vh - 64px); }
  .sv-iframe-wrap iframe { width: 100%; height: 100%; border: none; display: block; }
</style>

<div class="sv-iframe-wrap">
  <iframe src="{{ route('tripping') }}" title="Site Visit Form" allowfullscreen></iframe>
</div>
@endsection
