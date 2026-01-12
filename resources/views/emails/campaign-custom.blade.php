@extends('emails.layout')

@section('content')
    @if($campaign->preview_text)
        <p class="preview-text">{{ $campaign->preview_text }}</p>
    @endif
    
    <div style="margin-bottom: 30px;">
        {!! $campaign->content_html !!}
    </div>
    
    <div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 2px solid #e0e0e0;">
        <a href="{{ route('home') }}" 
           style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
            {{ __('Visit Our Store') }}
        </a>
    </div>
@endsection
