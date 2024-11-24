@extends('providermanagement::layouts.master')

@section('title',translate('service_details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/provider-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/provider-module')}}/plugins/dataTables/select.dataTables.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/provider-module')}}/plugins/wysiwyg-editor/froala_editor.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('service_details')}}</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="row mb-4 g-4">
                        <div class="col-lg-4 col-sm-12">
                            <div class="statistics-card statistics-card__total-orders">
                                <h2>{{$service->bookings_count}}</h2>
                                <h3>{{translate('total_bookings')}}</h3>
                                <img src="{{asset('public/assets/provider-module/img/icons/total-orders.png')}}"
                                     class="absolute-img" alt="{{ translate('total-orders') }}">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="statistics-card statistics-card__ongoing">
                                <h2>{{$service['ongoing_count']??0}}</h2>
                                <h3>{{translate('ongoing')}}</h3>
                                <img src="{{asset('public/assets/provider-module/img/icons/ongoing.png')}}"
                                     class="absolute-img" alt="{{ translate('ongoing') }}">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="statistics-card statistics-card__canceled">
                                <h2>{{$service['canceled_count']??0}}</h2>
                                <h3>{{translate('canceled')}}</h3>
                                <img src="{{asset('public/assets/provider-module/img/icons/canceled.png')}}"
                                     class="absolute-img" alt="{{ translate('canceled') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <button class="nav-link {{!isset($webPage) || $webPage=='general'?'active':''}}"
                                data-bs-toggle="tab"
                                data-bs-target="#general-tab-pane">{{translate('general_info')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{isset($webPage) && $webPage=='faq'?'active':''}}" data-bs-toggle="tab"
                                data-bs-target="#faq-tab-pane">{{translate('faq')}}</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{isset($webPage) && $webPage=='review'?'active':''}}"
                                data-bs-toggle="tab"
                                data-bs-target="#review-tab-pane">{{translate('reviews')}}
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade {{!isset($webPage) || $webPage=='general'?'show active':''}}"
                     id="general-tab-pane">
                    <div class="card">
                        <div class="card-body p-30">
                            <div class="media flex-column flex-md-row gap-3 mb-3">
                                <div class="">
                                    <img width="300"
                                         src="{{$service->cover_image_full_path}}"
                                         class="img-dropshadow" alt="{{ translate('cover-image') }}">
                                </div>
                                <div class="media-body ">
                                    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
                                        <h2 class="c1">{{$service->name}}</h2>
                                    </div>
                                    <p class="text-secondary">{{translate('category')}}: @if($service?->category){{$service?->category->name ?? translate('Unavailable')}}@endif  | @if($service?->subCategory){{translate('sub-category')}}: {{$service?->subCategory->name ?? translate('Unavailable')}}@endif</p>
                                    <p>{{$service->short_description}}</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <ul class="nav nav--tabs">
                                    <li class="nav-item">
                                        <button class="nav-link active" data-bs-toggle="tab"
                                                data-bs-target="#long-description-tab-pane">{{translate('details')}}
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab"
                                                data-bs-target="#price-table-tab-pane">{{translate('price_table')}}
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="long-description-tab-pane">
                                    {!! $service->description !!}
                                </div>
                                <div class="tab-pane fade" id="price-table-tab-pane">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-10">
                                            <div class="mt-3 mb-4">
                                                <ul class="nav nav--tabs nav--tabs__style3">
                                                    @php($count=0)
                                                    @foreach($service->variations->unique('zone_id')->all() as $index=>$zone)
                                                        <li class="nav-item">
                                                            <button class="nav-link {{$count==0?'active':''}}"
                                                                    data-bs-toggle="tab"
                                                                    data-bs-target="#tab-{{$zone->zone_id}}">{{$service->variations->where('zone_id',$zone->zone_id)->first()->zone->name??""}}
                                                            </button>
                                                        </li>
                                                        @php($count++)
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <div class="tab-content">
                                                @php($count=0)
                                                @foreach($service->variations->unique('zone_id')->all() as $index=>$zone)
                                                    <div class="tab-pane fade show {{$count==0?'active':''}}"
                                                         id="tab-{{$zone->zone_id}}">
                                                        <p class="text-center"><strong
                                                                class="c1 me-1">{{$service->variations->where('zone_id',$zone->zone_id)->count()}}</strong>
                                                            {{translate('available_variants')}}
                                                        </p>
                                                        <div class="service-price-list">
                                                            @foreach($service->variations->where('zone_id',$zone->zone_id)->all() as $variant)
                                                                <div class="service-price-list-item">
                                                                    <p>{{translate($variant->variant)}} </p>
                                                                    <h3 class="c1">{{with_currency_symbol($variant->price)}}</h3>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @php($count++)
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{isset($webPage) && $webPage=='faq'?'show active':''}}" id="faq-tab-pane">
                    <div class="card mb-30">
                        <div class="card-body p-30" id="faq-list">
                            @include('servicemanagement::provider.partials._faq-list',['faqs'=>$faqs])
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{isset($webPage) && $webPage=='review'?'show active':''}}"
                     id="review-tab-pane">
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <div class="row">
                                <div class="col-lg-5 mb-30 mb-lg-0 d-flex justify-content-center">
                                    <div class="rating-review">
                                        <h2 class="rating-review__title">
                                            <span class="rating-review__out-of">{{$service->avg_rating}}</span>/5
                                        </h2>
                                        <div class="rating">
                                            <span
                                                class="{{$service->avg_rating>=1?'material-icons':'material-symbols-outlined'}}">{{$service->avg_rating>=1?'star':'grade'}}</span>
                                            <span
                                                class="{{$service->avg_rating>=2?'material-icons':'material-symbols-outlined'}}">{{$service->avg_rating>=2?'star':'grade'}}</span>
                                            <span
                                                class="{{$service->avg_rating>=3?'material-icons':'material-symbols-outlined'}}">{{$service->avg_rating>=3?'star':'grade'}}</span>
                                            <span
                                                class="{{$service->avg_rating>=4?'material-icons':'material-symbols-outlined'}}">{{$service->avg_rating>=4?'star':'grade'}}</span>
                                            <span
                                                class="{{$service->avg_rating>=5?'material-icons':'material-symbols-outlined'}}">{{$service->avg_rating>=5?'star':'grade'}}</span>
                                        </div>
                                        <div class="rating-review__info d-flex flex-wrap gap-3">
                                            @php($total_review_count = $service->reviews->where('is_active', 1)->whereNotNull('review_rating')->whereNotNull('review_comment')->count())
                                            @php($totalReviews = $service->reviews->where('is_active', 1)->whereNotNull('review_rating')->count())
                                            <span>{{ $totalReviews }} {{ translate('ratings') }}</span>
                                            <span>{{$total_review_count}} {{translate('reviews')}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <ul class="common-list common-list__style2 after-none gap-10">
                                        <li>
                                            <span class="review-name">{{translate('excellent')}}</span>
                                            @php($excellent_count=$service->reviews->where('is_active', 1)->where('review_rating',5)->count())
                                            @php($excellent=(divnum($excellent_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$excellent}}%"
                                                     aria-valuenow="{{$excellent}}" aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$excellent_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('good')}}</span>
                                            @php($good_count=$service->reviews->where('is_active', 1)->where('review_rating',4)->count())
                                            @php($good=(divnum($good_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: {{$good}}%"
                                                     aria-valuenow="{{$good}}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$good_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('avarage')}}</span>
                                            @php($average_count=$service->reviews->where('is_active', 1)->where('review_rating',3)->count())
                                            @php($average=(divnum($average_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$average}}%"
                                                     aria-valuenow="{{$average}}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$average_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('below_avarage')}}</span>
                                            @php($below_average_count=$service->reviews->where('is_active', 1)->where('review_rating',2)->count())
                                            @php($below_average=(divnum($below_average_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$below_average}}%"
                                                     aria-valuenow="{{$below_average}}" aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$below_average_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('poor')}}</span>
                                            @php($poor_count=$service->reviews->where('is_active', 1)->where('review_rating',1)->count())
                                            @php($poor=(divnum($poor_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: {{$poor}}%"
                                                     aria-valuenow="{{$poor}}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$poor_count}}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center">
                        <h4 class="page-title">{{translate('My Reviews')}}</h4>
                        <div>
                            <div class="d-flex justify-content-end border-bottom pb-2 mb-10">
                                <div class="d-flex gap-2 fw-medium">
                                    <span class="opacity-75">{{translate('total_reviews')}}:</span>
                                    <span class="title-color">{{$reviews->total()}}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                                <span class="search-form__icon">
                                                    <span class="material-icons">search</span>
                                                </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="review_search"
                                               placeholder="{{translate('search_review_id')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">
                                        {{translate('search')}}
                                    </button>
                                </form>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="dropdown">
                                        <button type="button"
                                                class="btn btn--secondary text-capitalize dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span> {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li><a class="dropdown-item"
                                                   href="{{route('provider.service.reviews.download',['review_search'=>$search, 'service_id' => request()->id])}}">{{translate('excel')}}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Review ID')}}</th>
                                        <th>{{translate('reviewer')}}</th>
                                        <th>{{translate('date')}}</th>
                                        <th>{{translate('ratings')}}</th>
                                        <th>{{translate('reviews')}}</th>
                                        <th>{{translate('reply')}}</th>
                                        <th>{{translate('action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @forelse($reviews as $key => $review)
                                        <tr>
                                            <td>{{$key+$reviews?->firstItem()}}</td>
                                            <td>{{ $review->readable_id == 0 ? 'N/A' : $review->readable_id }}</td>
                                            <td>
                                                @if(isset($review->customer))
                                                    <span>{{$review->customer->first_name . ' ' .$review->customer->last_name}}</span><br>
                                                    <span>{{ translate('Booking ID #') . $review?->booking?->readable_id }}</span>
                                                @else
                                                    <span class="opacity-50">{{translate('Customer_not_available')}}</span>
                                                @endif
                                            </td>
                                            <td>{{$review->created_at}}</td>
                                            <td>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                                    <path d="M7 1.81445L8.854 5.76398L13 6.4012L10 9.47376L10.708 13.8145L7 11.764L3.292 13.8145L4 9.47376L1 6.4012L5.146 5.76398L7 1.81445Z" fill="#FFB900" stroke="#FFB900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                {{$review->review_rating}}
                                            </td>
                                            <td  data-bs-custom-class="review-tooltip" data-bs-toggle="tooltip" data-bs-placement="top"
                                                 title="{{$review->review_comment}}">{{ Str::limit($review->review_comment, 100) ?? translate('No review yet') }}</td>
                                            <td  data-bs-custom-class="review-tooltip" data-bs-toggle="tooltip" data-bs-placement="top"
                                                 title="{{$review->reviewReply?->reply}}">{{ Str::limit($review->reviewReply?->reply, 100) ?? translate('No reply yet') }}</td>
                                            <td>
                                                @if(!empty($review->review_comment))
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button class="action-btn btn--light-primary fw-medium text-capitalize fz-14" data-bs-toggle="modal" id="replyModalBtn"
                                                        data-bs-target="#replyModal"
                                                        data-booking_id ="{{$review?->booking?->readable_id}}"
                                                        data-readable_id ="{{$review->readable_id}}"
                                                        data-review_id ="{{$review->id}}"
                                                        data-service_name="{{$review->service->name}}"
                                                        data-service_img="{{$service->cover_image_full_path}}"
                                                        data-review="{{$review->review_comment ?? translate('No review yet')}}"
                                                        data-review_reply="{{$review->reviewReply?->reply ?? translate('No reply yet')}}"
                                                        data-variant_key="{{ $review->booking?->detail[0]?->variant_key }}"
                                                        data-action="{{ route('provider.service.review.reply') }}"
                                                    >
                                                        <span class="material-symbols-outlined">visibility</span>
                                                    </button>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" colspan="8">
                                                {{ translate('You don’t have any reviews yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $reviews->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $reviewPermission = business_config('provider_can_reply_review', 'provider_config')->live_values;
    ?>
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="p-3 pt-0">
                        <div class="d-flex gap-3">
                            <img src="" class="rounded aspect-square object-fit-cover" width="80" alt="Service Image">
                            <div class="w-0 flex-grow-1">
                                <div class="mb-2">
                                    <span>{{translate('Booking ID #')}}</span> <label class="booking_id"></label>
                                </div>
                                <h5 class="service_name"></h5>
                                <div class="mt-2">
                                    <span class="variant_key"></span>
                                </div>
                            </div>
                        </div>
                        <div class="review_section mb-3 mt-3">
                            <h4 class="mb-2">{{translate('Review')}}</h4>
                            <div class="p-3 rounded bg--secondary">
                                <p class="review_content"></p>
                            </div>
                        </div>
                        <form action="" method="post">
                            @csrf
                            <div class="reply_section">
                                <div>
                                    <h4 class="mb-3">{{translate('Reply')}}</h4>
                                    <div class="form-group">
                                        <textarea id="reply_content" class="form-control" name="reply_content" rows="4" {{$reviewPermission ? '' : 'readonly disabled'}}></textarea>
                                        <input type="hidden" class="form-control" name="review_id" value="">
                                    </div>
                                </div>
                            </div>
                            @if($reviewPermission)
                                <div class="text-end mt-3">
                                    <button class="btn btn--primary" type="submit">{{ translate('submit') }}</button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        "use strict"

        document.addEventListener('DOMContentLoaded', function () {
            var clickableRows = document.querySelectorAll('.clickable-row');
            clickableRows.forEach(function (row) {
                row.addEventListener('click', function () {
                    var target = row.getAttribute('data-target');
                    var collapseElement = document.querySelector(target);
                    collapseElement.classList.toggle('show');
                });
            });
        });

        $('#replyModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const modal = $(this);
            const serviceImg = button.data('service_img');
            const serviceName = button.data('service_name');
            const bookingID = button.data('booking_id');
            const readableID = button.data('readable_id');
            const reviewID = button.data('review_id');
            const review = button.data('review');
            const variantKey = button.data('variant_key');
            const reviewReply = button.data('review_reply');
            const action = button.data('action');

            modal.find('.service_name').text(serviceName);
            modal.find('.booking_id').text(bookingID);
            modal.find('.variant_key').text(variantKey);
            modal.find('.review_content').text(review);
            modal.find('img').attr('src', serviceImg);

            modal.find('textarea[name=reply_content]').val(reviewReply);
            modal.find('input[name=review_id]').val(reviewID);
            modal.find('form').attr('action',action);
        });
    </script>
@endpush
