<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Throwable;

class MoyassarPaymentController extends Controller
{
    use Processor;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('moyassar', 'payment_config');
        $moyassar = false;
        if (!is_null($config) && $config->mode == 'live') {
            $moyassar = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $moyassar = json_decode($config->test_values);
        }

        if ($moyassar) {
            $config = array(
                'api_key' => $moyassar->api_key,
                'published_key' => $moyassar->published_key
            );
            Config::set('moyassar_config', $config);
        }

        $this->payment = $payment;
        $this->user = $user;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0, 'payment_method' => 'moyassar'])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payer = json_decode($data['payer_information']);

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
            $business_logo = $business->business_logo ?? url('/');
        } else {
            $business_name = "my_business";
            $business_logo = url('/');
        }

        return view('payment-views.moyassar', compact('data', 'payer', 'business_logo', 'business_name'));
    }


    public function callback(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        try {
            $input = $request->all();
            if (
                count($input)
                && !empty($input['id'])
                && isset($input['status'])
                && $this->confirmMoyasserPayment(moyassar_payment_id: $input['id'], payment_id: $input['payment_id'])
            ) {
                $data = $this->payment::where(['id' => $request['payment_id']])->first();
                if (isset($data) && function_exists($data->success_hook)) {
                    call_user_func($data->success_hook, $data);
                }
                return $this->payment_response($data, 'success');
            }
            $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
            if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
            return $this->payment_response($payment_data, 'fail');

        } catch (Throwable $e) {
            dd($e->getMessage());
            Log::error('Error In: ' . __METHOD__ . ' ERROR:' . $e->getMessage());
            return redirect()->route('payment-fail');
        }
    }


    private function confirmMoyasserPayment($moyassar_payment_id, $payment_id): bool
    {
        try {
            $response = Http::withBasicAuth(Config::get('moyassar_config.api_key'), '')
                ->get("https://api.moyasar.com/v1/payments/{$moyassar_payment_id}");
            dd($response->json());
            if ($response->successful()) {
                $data = $response->json();
                $is_paid = 0;
                $transaction_id = $data['id'];
                if ($response['status'] == 'paid') {
                    $is_paid = 1;
                    $transaction_id = $data['id'];
                }
                $this->payment::where(['id' => $payment_id])->update([
                    'payment_method' => 'moyassar',
                    'is_paid' => $is_paid,
                    'transaction_id' => $transaction_id,
                ]);
                return $is_paid;
            }
            return false;
        } catch (Throwable $e) {
            dd($e->getMessage());
            Log::error('Error In: ' . __METHOD__ . ' ERROR:' . $e->getMessage());
            return false;
        }
    }
}
