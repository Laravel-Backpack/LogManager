@extends(backpack_view('blank'))

@php
  $breadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    trans('backpack::logmanager.log_manager') => route('log.index'),
    trans('backpack::logmanager.preview') => false,
  ];
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-end" bp-section="page-header">
      <h1 bp-section="page-heading">
        {{ trans('backpack::logmanager.log_manager') }}
      </h1>
      <p class="ms-2 ml-2 mb-2" bp-section="page-subheading">
        {{ trans('backpack::logmanager.file_name') }}: <i>{{ $file_name }}</i>
      </p>
      <p class="ms-2 ml-2 mb-2" bp-section="page-subheading-back-button">
        <small><a href="{{ route('log.index') }}" class="hidden-print font-sm"><i class="la la-angle-double-left"></i> {{
            trans('backpack::logmanager.back_to_all_logs') }}</a></small>
      </p>
    </section>
@endsection

@section('content')
  <div id="accordion" role="tablist" aria-multiselectable="true">
    @forelse($logs as $key => $log)
      <div class="card mb-0 pb-0">
        <div class="card-header bg-{{ $log['level_class'] }}" role="tab" id="heading{{ $key }}">
            <a role="button" data-toggle="collapse" data-bs-toggle="collapse" data-parent="#accordion" href="#collapse{{ $key }}" aria-expanded="true" aria-controls="collapse{{ $key }}" class="text-white">
              <i class="la la-{{ $log['level_img'] }}"></i>
              <span>[{{ $log['date'] }}]</span>
              {{ Str::limit($log['text'], 150) }}
            </a>
        </div>
        <div id="collapse{{ $key }}" class="panel-collapse collapse p-3" role="tabpanel" aria-labelledby="heading{{ $key }}">
          <div class="panel-body">
            <p>{{$log['text']}}</p>
            <pre class="p-0" ><code class="php">{{ trim($log['stack']) }}</code></pre>
          </div>
        </div>
      </div>
    @empty
      <h3 class="text-center">No Logs to display.</h3>
    @endforelse
  </div>

@endsection

@section('after_scripts')
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/styles/default.min.css">
  <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/highlight.min.js"></script>
  <script>hljs.initHighlightingOnLoad();</script>
@endsection
