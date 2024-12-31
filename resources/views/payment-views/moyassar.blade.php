@extends('payment-views.layouts.master')

@push('script')
    <link rel="stylesheet" href="https://cdn.moyasar.com/mpf/1.14.0/moyasar.css" />
    <!-- Moyasar Scripts -->
    <script src="https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?version=4.8.0&features=fetch"></script>
    <script src="https://cdn.moyasar.com/mpf/1.14.0/moyasar.js"></script>
@endpush

@section('content')
    <div class="text-center">
        <h1>Please do not refresh this page...</h1>
    </div>
    <div class="mysr-form"></div>
    <script>
        Moyasar.init({
            element: '.mysr-form',
            amount: "{{ round($data->payment_amount, 2) * 100 }}",
            currency: "{{ $data->currency_code }}",
            description: "{{ $data->payment_amount }}",
            publishable_api_key: "{{ config()->get('moyassar_config.published_key') }}",
            callback_url: "{{ route('moyassar.callback', ['payment_id' => $data->id]) }}",
            methods: ['creditcard']
        })
    </script>
@endsection
