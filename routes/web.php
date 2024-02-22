<?php
use OpenApi\Annotations as OA;

// php artisan swagger-lume:generate

/**
* @var \Laravel\Lumen\Routing\Router $router
*/

/**
 * @OA\Info(
 *     title="Платёжное API для коллективной системы печати",
 *     version="0.1"
 * )
 */

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/**
 * @OA\Get(
 *      path="/check",
 *      operationId="checkConnection",
 *      summary="Проверить подключение к YooKassa",
 *      description="Можно использовать для проверки подключения к YooKassa. Не требует параметров, возвращает (среди прочего) ID магазина и возможные способы платежа",*
 *      tags={"Платежи"},
 *      @OA\Response(response=200, description="OK"),
 *      @OA\Response(response=400, description="Bad Request")
 * )
 * @var \Laravel\Lumen\Routing\Router $router
 *
 */

$router->get('/env[/{name}]', function ($name = null) {
    if ($name == null) {
        return "<pre>" . print_r($_ENV, true);
    }
    return [
        "getenv($name)" => getenv($name),
        "env($name)" => env($name),
        '$_ENV' => $_ENV
    ];
});


$router->get('/check/', 'PaymentController@shop_info');

/**
 * @OA\Get(
 *      path="//payments/{id}",
 *      operationId="Get Payment Info",
 *      summary="Получить информацию о платеже",
 *      description="Получает информацию о платеже по Id платежа",
 *      tags={"Платежи"},
 *      @OA\Response(response=200, description="OK"),
 *      @OA\Response(response=400, description="Bad Request")
 * )
 * @var \Laravel\Lumen\Routing\Router $router
 *
 */
$router->get('/payments/{id}', 'PaymentController@getPaymentInfo');

/**
 * @OA\Post(
 *     path="/payments/",
 *     tags={"Платежи"},
 *     description="С этого метода начинается процесс платежа. Метод создаёт платёж и возвращает информацию о нём. <br>Входные параметры: <ul><li>payment_amount - сумма платежа в рублях</li><li> user_id - ID пользователя в виде ivan.ivanov, redirect_url - адрес страницы, на который будет переадресован пользователь после платежа",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *            mediaType="application/json",
 *            @OA\Schema(
 *               type="object",
 *               @OA\Property(property="payment_amount", type="string"),
 *               @OA\Property(property="user_id", type="string"),
 *               @OA\Property(property="redirect_url", type="string"),
 *            )
 *        )
 *    ),
 *   @OA\Response(response=201,description="Successful created"),
 *   @OA\Response(response=422, description="Error: Unprocessable Entity")
 * )
 */
$router->post('/payments/', 'PaymentController@create_payment');

