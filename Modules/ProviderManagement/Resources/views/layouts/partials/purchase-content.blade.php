<div class="price-box d-flex flex-column active rounded-3 border flex-grow-1 max-w220 m-auto">
    <div class="price-box__top px-2 py-4 text-center mb-3">
        <input type="hidden" name="package_id" value="{{ $subscriptionPackage->id }}">
        <input type="hidden" name="callback" value="{{route('provider.subscription-package.details')}}">
        <h5>{{ $subscriptionPackage->name }}</h5>
    </div>

    <div class="text-center min-h-62 d-flex flex-column justify-content-center pb-3">
        <strong class="h3">{{with_currency_symbol($subscriptionPackage->price)}}</strong>
        <div>{{ $subscriptionPackage->duration }}  {{translate('Days')}}</div>
    </div>
</div>

<div class="my-4">
    <div class="p-3 bg-primary-light flex-wrap border d-flex gap-3 justify-content-around rounded">
        <div class="d-flex gap-2 flex-column align-items-center">
            <div>{{translate('Validity')}}</div>
            <h5 class="h5 fw-bold">{{ $subscriptionPackage->duration }}  {{translate('Days')}}</h5>
        </div>
        <div class="d-flex gap-2 flex-column align-items-center">
            <div>{{translate('Price')}}</div>
            <h5 class="h5 fw-bold">{{with_currency_symbol($subscriptionPackage->price)}}</h5>
        </div>
        <div class="d-flex gap-2 flex-column align-items-center">
            <div>{{translate('Bill Status')}}</div>
            <h5 class="h5 fw-bold">{{translate('Purchase')}}</h5>
        </div>
    </div>
</div>

<h5 class="mb-3">Select Payment Method</h5>
<div class="row g-3">
    @foreach($paymentGateways ?? [] as $gateway)
        <div class="col-sm-6">
            <label class="payment-method-option border active rounded p-3 d-flex justify-content-between">
                <div class="d-flex gap-2 align-items-center max-height-45px">
                    <img width="70" src="{{ $gateway['gateway_image'] }}"
                         alt="{{translate('gateway image')}}">
                    <div>{{ $gateway['label'] }}</div>
                </div>

                <input value="{{ $gateway['gateway'] }}" type="radio" name="payment_method" class="position-static" {{ $loop->first ? 'checked' : '' }}>
            </label>
        </div>
    @endforeach
</div>


<div class="col-12 mt-3">
    <div class="d-flex flex-wrap gap-3 justify-content-end">
        <button type="button" class="btn btn--secondary text-capitalize" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn--primary text-capitalize">Purchase Subscription Plan</button>
    </div>
</div>
