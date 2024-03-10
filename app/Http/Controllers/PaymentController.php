<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Request;
use App\Services\YooClient;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function callUserInfoAPI($action, $params) {
        $userInfoAPI_URL = env('USERINFO_API_URL') . $action;
        $userInfoAPI_Token = env('USERINFO_API_TOKEN');
//        dd($userInfoAPI_URL, $params);
        $response = Http::withHeader('API-Token', $userInfoAPI_Token)
            ->post($userInfoAPI_URL, $params);
        return $response->body();
    }

    public function shop_info()
    {
        $client = new YooClient();
        try {
            $response = $client->me();
        } catch (\Exception $e) {
            $response = response($e->getMessage(), 501);
        }
        return $response;
    }

    public function getPaymentInfo($id)
    {
        $client = new YooClient();
        try {
            $response = $client->getPaymentInfo($id);
        } catch (\Exception $e) {
            $response = response($e->getMessage(), 501);
        }
        return $response;
    }

    public function create_payment(Request $request)
    {
        try {
            $param_names = ['payment_amount', 'user_id', 'user_mail', 'redirect_url'];
            if (!$request->filled($param_names)) {
                return (response('Не задан один из параметров payment_amount, user_id, user_mail, redirect_url', 501));
            }
            $params = $request->only($param_names);
            $user_mail = $request->input('user_mail');
            $t = new Transaction($params);
            $t->description = 'Пополнение баланса пользователя ' . $t->user_id;
            $t->save();

            $client = new YooClient();
            $builder = \YooKassa\Request\Payments\CreatePaymentRequest::builder();
            $builder->setAmount($t->payment_amount)
                ->setCurrency(\YooKassa\Model\CurrencyCode::RUB)
                ->setCapture(true)
                ->setDescription($t->description)
                ->setMetadata([
                    'user_id' => $t->user_id,
                    'transaction_id' => $t->id
                ]);
            $builder->setConfirmation([
                'type' => \YooKassa\Model\Payment\ConfirmationType::REDIRECT,
                'returnUrl' => route('process_payment', ['id' => $t->id]),
            ]);

            // Указываем данные для чекв
            $builder->setReceiptEmail($user_mail);
            //$builder->setReceiptPhone('71111111111');

            // Создаем объект запроса
            $request = $builder->build();

            $idempotenceKey = uniqid('', true);
            $payment = $client->createPayment($request, $idempotenceKey);
            $t->payment_id = $payment->getId();
            $t->save();

            $response = [
                "payment_id" => $payment->getId(),
                "user_id" => $t->user_id,
                "payment_amount" => $t->payment_amount,
                "description" => $t->description,
                "income_amount" => $payment->getAmount()->value,
                "status" => $payment->getStatus(),
                "confirmation_url" => $payment->getConfirmation()->getConfirmationUrl()
            ];
        } catch (\Exception $e) {
            $response = response($e, 501);
        }
        return $response;
    }

    // http://pgups-payment.test/payments/9b8663ee-7782-4528-8ada-7fe73bbae88e/process
    public function process_payment(Request $request, string $id)
    {
        $t = Transaction::find($id);
        if ($t == null) {
            return (response('Не найдена информация о транзакции ' . $id, 404));
        }
        $payment = $this->getPaymentInfo($t->payment_id);
        if ($payment['status'] == 'succeeded' and $t->pushed2PaperCut == null) {
            $response = $this->callUserInfoAPI('changebalance', [
                'userName' => $t->user_id,
                'amount' => $t->payment_amount,
                'comment' => "Пополнение платежа через YooKassa, ID платежа {$payment->getId()}"
            ]);
            if ($response==1) {
                $t->pushed2PaperCut = Carbon::now();
                $t->save();
            }
        }
        return redirect($t->redirect_url);
    }
}
