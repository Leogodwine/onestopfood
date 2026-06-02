@extends('layout')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h2 fw-bold mb-1"><i class="bi bi-book text-success"></i> User Manual</h1>
                    <p class="text-muted mb-0">One Stop Food — Food Order &amp; Delivery System</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('docs.guidelines') }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-journal-text"></i> System Guidelines
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> {{ __('auth.sign_in') }}
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5 doc-content">
                    @include('docs.partials.user-manual-content')
                </div>
            </div>

            <p class="text-center text-muted small mt-4 mb-0">
                <a href="{{ route('home') }}"><i class="bi bi-house"></i> Back to home</a>
                &nbsp;·&nbsp;
                Need help? <a href="mailto:support@onestopfood.co.tz">support@onestopfood.co.tz</a>
            </p>
        </div>
    </div>
</div>

<style>
.doc-content h2 { font-size: 1.35rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; color: #1a5632; border-bottom: 2px solid #e8f5e9; padding-bottom: .5rem; }
.doc-content h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: .75rem; }
.doc-content p, .doc-content li { line-height: 1.7; }
.doc-content table { width: 100%; margin: 1rem 0; font-size: .95rem; }
.doc-content table th, .doc-content table td { padding: .6rem .75rem; border: 1px solid #dee2e6; vertical-align: top; }
.doc-content table th { background: #f8f9fa; font-weight: 600; }
.doc-content blockquote { border-left: 4px solid #28a745; padding: .75rem 1rem; background: #f0f7f4; margin: 1rem 0; border-radius: 0 8px 8px 0; }
.doc-content ul { padding-left: 1.25rem; }
</style>
@endsection
