@extends('layouts.layout')

@section('title')
    {{__('Template Assets')}}
@endsection

@section('sidebar')
    @include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_processes')])
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumbs', ['routes' => [
        __('Designer') => route('processes.index'),
        __('Processes') => route('processes.index')
    ]])
@endsection

@section('content')
<div id="template-asset-manager">
    <router-view></router-view>
</div>
@endsection

@section('js')
    <script src="{{mix('js/templates/assets.js')}}"></script>
    {{-- <script>
      test = new Vue({
        el: '#templateAssets',
        data() {
          return {
          }
        },
        methods: {
          onClose() {
            window.location.href = '/processes';
          },
        }
      });
    </script> --}}
@endsection

{{-- @section('css')
    <style>

    </style>
@endsection --}}
