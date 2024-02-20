<?php
namespace App\Http\Controllers;

use YooKassa\Client as YooClient;


/**
 * @OA\Info(
 *     title="Платёжное API для коллективной системы печати",
 *     version="0.1"
 * )
 */

class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *      path="/shop_info",
     *      operationId="deleteNotificationById",
     *      summary="Delete notification by ID",
     *      @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=400, description="Bad Request")
     * )
     */

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

    public function create_payment() {
        try {
            $payment_amount = request('payment_amount');
            $user_id = request('user_id');
            $redirect_url = request('redirect_url');

            $client = new \YooKassa\Client();
            $client->setAuth(env('PAYMENTSYSTEM_CLIENT_ID'), env('PAYMENTSYSTEM_CLIENT_SECRET'));

            $builder = \YooKassa\Request\Payments\CreatePaymentRequest::builder();
            $builder->setAmount($payment_amount)
                    ->setCurrency(\YooKassa\Model\CurrencyCode::RUB)
                    ->setCapture(true)
                    ->setDescription('Пополнение баланса пользователя ' . $user_id)
                    ->setMetadata([
                        'cms_name'       => 'yoo_api_test',
                        'order_id'       => '112233',
                        'language'       => 'ru',
                        'transaction_id' => '123-456-789',
                    ]);
            // Устанавливаем страницу для редиректа после оплаты
            $builder->setConfirmation([
                'type'      => \YooKassa\Model\Payment\ConfirmationType::REDIRECT,
                'returnUrl' => $redirect_url,
            ]);

            // Можем установить конкретный способ оплаты
            $builder->setPaymentMethodData(\YooKassa\Model\Payment\PaymentMethodType::BANK_CARD);

            // Составляем чек
            $builder->setReceiptEmail('john.doe@merchant.com');
            $builder->setReceiptPhone('71111111111');
            // Добавим товар
            $builder->addReceiptItem(
                'Платок Gucci',
                3000,
                1.0,
                2,
                'full_payment',
                'commodity'
            );
            // Добавим доставку
            $builder->addReceiptShipping(
                'Delivery/Shipping/Доставка',
                100,
                1,
                \YooKassa\Model\Receipt\PaymentMode::FULL_PAYMENT,
                \YooKassa\Model\Receipt\PaymentSubject::SERVICE
            );

            // Создаем объект запроса
            $request = $builder->build();

            // Можно изменить данные, если нужно
            $request->setDescription($request->getDescription() . ' - merchant comment');

            $idempotenceKey = uniqid('', true);
            $response = $client->createPayment($request, $idempotenceKey);

            //получаем confirmationUrl для дальнейшего редиректа
            $confirmationUrl = $response->getConfirmation()->getConfirmationUrl();
        } catch (\Exception $e) {
            $response = $e;
        }
        return $response;
    }

    //
}
