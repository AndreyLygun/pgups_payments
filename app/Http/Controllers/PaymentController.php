<?php
namespace App\Http\Controllers;

use http\Client\Response;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Request;
use YooKassa\Client as YooClient;

class PaymentController extends Controller
{
    public function __construct()
    {
        //
    }


    public function shop_info() {
        $client = new YooClient();
        $client->setAuth(env('PAYMENTSYSTEM_CLIENT_ID'), env('PAYMENTSYSTEM_CLIENT_SECRET'));
        try {
            $response = $client->me();
        } catch (\Exception $e) {
            $response = $e;
        }
        return $response;
    }

    public function getPaymentInfo($id) {
        $client = new YooClient();
        $client->setAuth(env('PAYMENTSYSTEM_CLIENT_ID'), env('PAYMENTSYSTEM_CLIENT_SECRET'));
        try {
            $response = $client->getPaymentInfo($id);
        } catch (\Exception $e) {
            $response = $e;
        }
        return $response;
    }

    public function create_payment(Request $request) {
        function get_param($name, $request) {
            $param = $request->input($name)??$request->post($name)??'';
            if ($param=='') {
                throw new HttpClientException("Value $name must be set in JSON object");
            }
            return $param;
        }
        try {
            $payment_amount = get_param('payment_amount', $request);
            $user_id = get_param('user_id', $request);
            $redirect_url = get_param('redirect_url', $request);
            $user_mail = get_param('user_mail', $request);
            $description = 'Пополнение баланса пользователя ' . $user_id;

            $client = new \YooKassa\Client();
            $client->setAuth(env('PAYMENTSYSTEM_CLIENT_ID'), env('PAYMENTSYSTEM_CLIENT_SECRET'));

            $builder = \YooKassa\Request\Payments\CreatePaymentRequest::builder();
            $builder->setAmount($payment_amount)
                    ->setCurrency(\YooKassa\Model\CurrencyCode::RUB)
                    ->setCapture(true)
                    ->setDescription($description)
                    ->setMetadata([
                        'user_id' => $user_id,
                    ]);
            // Устанавливаем страницу для редиректа после оплаты

            $builder->setConfirmation([
                'type'      => \YooKassa\Model\Payment\ConfirmationType::REDIRECT,
                'returnUrl' => $redirect_url,
            ]);

            // Можем установить конкретный способ оплаты
            // $builder->setPaymentMethodData(\YooKassa\Model\Payment\PaymentMethodType::BANK_CARD);

            // Составляем чек
            $builder->setReceiptEmail($user_mail);
            //$builder->setReceiptPhone('71111111111');

            // Создаем объект запроса
            $request = $builder->build();

            // Можно изменить данные, если нужно
            $request->setDescription($request->getDescription() . ' - merchant comment');

            $idempotenceKey = uniqid('', true);
            $payment = $client->createPayment($request, $idempotenceKey);

            //получаем confirmationUrl для дальнейшего редиректа
            $response = [];
            $response["payment_id"] = $payment->getId();
            $response["user_id"] = $user_id;
            $response["payment_amount"] = $payment_amount;
            $response["description"] = $description;
            $response["income_amount"] = $payment->getAmount()->value;
            $response["status"] = $payment->getStatus();
            $response["confirmation_url"] = $payment->getConfirmation()->getConfirmationUrl();
        } catch (\Exception $e) {
            $response = $e;
        }
        return $response;
    }

    //
}
