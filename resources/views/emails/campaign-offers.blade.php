@extends('emails.layout')

@section('content')
    @if($campaign->preview_text)
        <p class="preview-text">{{ $campaign->preview_text }}</p>
    @endif
    
    <div style="margin-bottom: 30px;">
        {!! $campaign->content_html !!}
    </div>
    
    @if($campaign->offers->isNotEmpty())
        <div style="margin-top: 40px;">
            <h2 style="color: #667eea; margin-bottom: 20px; text-align: center;">
                {{ __('Special Offers Just For You') }}
            </h2>
            
            <div style="background: #f9f9f9; padding: 30px; border-radius: 8px;">
                @foreach($campaign->offers as $offer)
                    <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 6px; border-left: 4px solid #667eea;">
                        <h3 style="color: #333; margin-bottom: 10px;">{{ $offer->code }}</h3>
                        
                        @if($offer->description)
                            <p style="color: #666; margin-bottom: 15px;">{{ $offer->description }}</p>
                        @endif
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <strong style="font-size: 24px; color: #667eea;">
                                    @if($offer->discount_type === 'percentage')
                                        {{ $offer->discount_value }}%
                                    @else
                                        {{ number_format($offer->discount_value, 0) }} {{ __('EGP') }}
                                    @endif
                                </strong>
                                <span style="color: #999; margin-left: 10px;">{{ __('Discount') }}</span>
                            </div>
                            
                            @if($offer->valid_until)
                                <div style="color: #999; font-size: 14px;">
                                    {{ __('Valid until') }}: {{ $offer->valid_until->format('d M Y') }}
                                </div>
                            @endif
                        </div>
                        
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                            <a href="{{ route('home') }}?coupon={{ $offer->code }}" 
                               style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                                {{ __('Shop Now') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 2px solid #e0e0e0;">
        <a href="{{ route('home') }}" 
           style="display: inline-block; background: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
            {{ __('Visit Our Store') }}
        </a>
    </div>
@endsection
