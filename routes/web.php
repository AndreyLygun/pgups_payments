<?php
use OpenApi\Annotations as OA;

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

 *      @OA\Response(response=200, description="OK"),
 *      @OA\Response(response=400, description="Bad Request")
 * )
 * @var \Laravel\Lumen\Routing\Router $router
 *
 */
$router->get('/check/', 'PaymentController@shop_info');

/**
 * @OA\Post(
 *      path="/payment/",
 *      operationId="createPayment",
 *      summary="Создать платёж",

 *      @OA\Response(response=200, description="OK"),
 *      @OA\Response(response=400, description="Bad Request")
 * )
 * @var \Laravel\Lumen\Routing\Router $router
 *
 */
$router->post('/payment/', 'PaymentController@create_payment');
