<?php


$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/env[/{name}]', function ($name = null) {
    $result = [
        "getenv()" => getenv(),
        '$_ENV' => $_ENV
    ];
    if ($name != null ) {
        $result["name"] = env($name);
    }
    return $result;
});


$router->get('/check/',
    ['middleware' => 'auth', 'uses'=>'PaymentController@shop_info']
);

$router->get('/payments/{id}',
    ['middleware' => 'auth', 'uses'=>'PaymentController@getPaymentInfo']
);

$router->post('/payments/',
    ['middleware' => 'auth', 'uses'=>'PaymentController@create_payment']
);

// на этот URL платёжная система отправляет пользователя после выполнения платежа
// в обычном режиме не предназначена для вызова в качестве API метода
$router->get('/payments/{id}/process',
    ['as' => 'process_payment', 'uses'=>'PaymentController@process_payment']
);

