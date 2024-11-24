
<div class="d-flex gap-2 gap-sm-3 align-items-center max-w-600 mx-auto">
    <div class="price-box d-flex flex-column rounded-3 border flex-grow-1">
        <div class="price-box__top px-2 py-4 text-center mb-3">
            <h5>{{ $packageSubscriber?->package_name }}</h5>
        </div>

        <div class="text-center min-h-62 d-flex flex-column justify-content-center pb-3">
            <strong class="h3">{{with_currency_symbol($packageSubscriber->package_price - $packageSubscriber->vat_amount)}}</strong>
            <div>{{ $packageSubscriber?->package->duration }}  {{translate('Days')}}</div>
        </div>
    </div>

    <div class="flex-shrink-0">
        <img width="40" src="{{asset('public/assets/admin-module/img/icons/shift.png')}}" alt="">
    </div>

    <div class="price-box d-flex flex-column active rounded-3 border flex-grow-1">
        <div class="price-box__top px-2 py-4 text-center mb-3">
            <h5>{{ $subscriptionPackage->name }}</h5>
            <input type="hidden" name="package_id" value="{{ $subscriptionPackage->id }}">
            <input type="hidden" name="provider_id" value="{{ $providerId }}">
        </div>

        <div class="text-center min-h-62 d-flex flex-column justify-content-center pb-3">
            <strong class="h3">{{with_currency_symbol($subscriptionPackage->price)}}</strong>
            <div>{{ $subscriptionPackage?->duration }} {{translate('Days')}}</div>
        </div>
    </div>
</div>

<div class="my-4">
    <div class="p-3 bg-primary-light flex-wrap border d-flex gap-3 justify-content-around rounded">
        <div class="d-flex gap-2 flex-column align-items-center">
            <div>{{translate('Validity')}}</div>
            <h5 class="h5 fw-bold">{{ $subscriptionPackage?->duration }} {{translate('Days')}}</h5>
        </div>
        <div class="d-flex gap-2 flex-column align-items-center">
            <div>{{translate('Price')}}</div>
            <h5 class="h5 fw-bold">{{with_currency_symbol($subscriptionPackage?->price)}}</h5>
        </div>
        <div class="d-flex gap-2 flex-column align-items-center">
            <div>{{translate('Bill Status')}}</div>
            <h5 class="h5 fw-bold">{{translate('Shift')}}</h5>
        </div>
    </div>
</div>

<h5 class="mb-3">{{ translate('Select Payment Method') }}</h5>
<div class="row g-3">
    <div class="col-sm-6">
        <label class="payment-method-option border active rounded p-3 d-flex justify-content-between cursor-pointer">
            <div class="d-flex gap-2 align-items-center">
                <div>{{ translate('Received Money Manually') }}</div>
            </div>

            <input value="received_manually" type="radio" name="payment_method" class="position-static" checked>
        </label>
    </div>
    @php($freeTrialStatus = (int)((business_config('free_trial_period', 'subscription_Setting'))->is_active ?? 0))
    @if($freeTrialStatus)
        <div class="col-sm-6">
            <label class="payment-method-option border active rounded p-3 d-flex justify-content-between cursor-pointer">
                <div class="d-flex gap-2 align-items-center">
                    <div>{{ translate('Continue With Free Trial') }}</div>
                </div>

                <input value="free_trial" type="radio" name="payment_method" class="position-static">
            </label>
        </div>
    @endif
    <div class="col-12">
        <div class="d-flex flex-wrap gap-3 justify-content-end">
            <button type="button" class="btn btn--secondary text-capitalize" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
            <button type="submit" class="btn btn--primary text-capitalize">
                {{translate('Shift Plan')}}</button>
        </div>
    </div>
</div>