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
        <div class="card-header bg-{{ $log['level_class'] }} d-flex align-items-center" role="tab" id="heading{{ $key }}">
            <a href="#heading{{ $key }}" class="log-anchor text-white me-2 mr-2 border border-white rounded-circle text-center text-decoration-none flex-shrink-0" style="width: 30px; height: 30px; line-height: 28px;">
              <i class="la la-link"></i>
            </a>
            <a role="button" data-toggle="collapse" data-bs-toggle="collapse" data-parent="#accordion" href="#collapse{{ $key }}" aria-expanded="false" aria-controls="collapse{{ $key }}" class="text-white flex-grow-1 collapsed">
              <i class="la la-{{ $log['level_img'] }}"></i>
              <span>[{{ $log['date'] }}]</span>
              {{ Str::limit($log['text'], 150) }}
            </a>
        </div>
        <div id="collapse{{ $key }}" class="panel-collapse collapse p-3" role="tabpanel" aria-labelledby="heading{{ $key }}" data-parent="#accordion" data-bs-parent="#accordion">
          <div class="panel-body">
            <p>{{$log['text']}}</p>
            <pre class="p-0" ><code>{{ trim($log['stack']) }}</code></pre>
          </div>
        </div>
      </div>
    @empty
      <h3 class="text-center">No Logs to display.</h3>
    @endforelse
  </div>

@endsection

@section('after_scripts')
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
  <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
  <script>hljs.highlightAll();</script>
  <script>
    function openAccordionFromHash() {
        if (window.location.hash && window.location.hash.startsWith('#heading')) {
            const heading = document.querySelector(window.location.hash);
            if (heading) {
                const trigger = heading.querySelector('a[data-toggle="collapse"], a[data-bs-toggle="collapse"]');

                if (trigger) {
                    const targetId = trigger.getAttribute('href') || trigger.getAttribute('data-bs-target');
                    const collapseEl = document.querySelector(targetId);
                    const isExpanded = collapseEl && (collapseEl.classList.contains('show') || collapseEl.classList.contains('in'));

                    if (!isExpanded) {
                        trigger.click();
                        
                        try {
                            if (collapseEl && !collapseEl.classList.contains('show')) {
                                const bsCollapse = bootstrap.Collapse.getInstance(collapseEl) || new bootstrap.Collapse(collapseEl, { toggle: false });
                                bsCollapse.show();
                            }
                        } catch (e) {
                            //
                        }
                    }
                }

                setTimeout(() => {
                    const elementPosition = heading.getBoundingClientRect().top + window.scrollY;
                    const offsetPosition = elementPosition - 80;
    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });
                }, 250);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        openAccordionFromHash();

        window.addEventListener('hashchange', () => {
            openAccordionFromHash();
        });


        function showNotification(type, text) {
            new Noty({
                text: text,
                type: type
            }).show();
        }

        async function copyToClipboard(text) {
            try {
                if (navigator.clipboard) {
                    await navigator.clipboard.writeText(text);
                    showNotification('success', "{{ trans('backpack::logmanager.link_copied_to_clipboard') }}");
                } else {
                    throw new Error('Clipboard API unavailable');
                }
            } catch (err) {
                // Fallback
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showNotification('success', "{{ trans('backpack::logmanager.link_copied_to_clipboard') }}");
                } catch (fallbackErr) {
                    showNotification('error', "{{ trans('backpack::logmanager.link_copy_failed') }}");
                }
                document.body.removeChild(textArea);
            }
        }

        document.querySelectorAll('.log-anchor').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const url = window.location.origin + window.location.pathname + this.getAttribute('href');
                copyToClipboard(url);
            });
        });
    });
  </script>
@endsection
