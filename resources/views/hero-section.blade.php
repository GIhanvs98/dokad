<!-- resources/views/hero-section.blade.php -->
@php
    $sectionOptions = $getSearchFormOp ?? [];
    $sectionData ??= [];
    
    // Get Search Form Options
    $enableFormAreaCustomization = data_get($sectionOptions, 'enable_extended_form_area') ?? '0';
    $hideTitles = data_get($sectionOptions, 'hide_titles') ?? '0';
    
    $headerTitle = data_get($sectionOptions, 'title_' . config('app.locale'));
    $headerTitle = (!empty($headerTitle)) ? replaceGlobalPatterns($headerTitle) : null;
    
    $headerSubTitle = data_get($sectionOptions, 'sub_title_' . config('app.locale'));
    $headerSubTitle = (!empty($headerSubTitle)) ? replaceGlobalPatterns($headerSubTitle) : null;
    
    $parallax = data_get($sectionOptions, 'parallax') ?? '0';
    $hideForm = data_get($sectionOptions, 'hide_form') ?? '0';
    $displayStatesSearchTip = config('settings.list.display_states_search_tip');
    
    $hideOnMobile = (data_get($sectionOptions, 'hide_on_mobile') == '1') ? ' hidden-sm' : '';
@endphp
@if (isset($enableFormAreaCustomization) && $enableFormAreaCustomization == '1')
    
    @if (isset($firstSection) && !$firstSection)
        <div class="p-0 mt-lg-4 mt-md-3 mt-3"></div>
    @endif
    
    @php
        $parallax = ($parallax == '1') ? ' parallax' : '';
    @endphp
        
    <!-- start hero-section -->
    <div class="hero-area">
        
        <div class="main-content1">
            <div id="owl-csel1" class="owl-carousel owl-theme">
                <div class="slide-item">
                    <a href="#">
                        <img src="{{ asset('images/slider-2.jpg') }}" alt="">
                    </a>
                </div>
                <div class="slide-item">
                    <a href="#">
                        <img src="{{ asset('images/slider-1.jpg') }}" alt="">
                    </a>
                </div>
                <div class="slide-item">
                    <a href="#">
                        <img src="{{ asset('images/slider-2.jpg') }}" alt="">
                    </a>
                </div>
                <div class="slide-item">
                    <a href="#">
                        <img src="{{ asset('images/slider-1.jpg') }}" alt="">
                    </a>
                </div>
            </div>
            <div class="owl-theme">
                <div class="owl-controls">
                    <div class="custom-nav owl-nav"></div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .owl-dots {
            display: none !important;
        }
        .nav-btn {
            cursor: pointer;
        }
        .prev-slide {
            float: left;
        }
        .next-slide {
            float: right;
        }
    </style>
    <!-- end hero-section -->
@endif

@push('scripts')
<script>
    $(document).ready(function(){
        $("#owl-csel1").owlCarousel({
            items: 1,
            loop: true,
            nav: true,
           
            navText: ["<div class='nav-btn prev-slide'></div>", "<div class='nav-btn next-slide'></div>"],
            dots: false
        });
    });
</script>
@endpush
