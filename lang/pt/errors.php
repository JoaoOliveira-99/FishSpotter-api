<?php

/*
|--------------------------------------------------------------------------
| Mensagens de erro da API
|--------------------------------------------------------------------------
|
| A chave é o "code" devolvido no JSON. A app usa o "code" para decidir o
| que fazer; o texto serve apenas para mostrar ao utilizador e pode ser
| alterado à vontade sem partir a app.
|
*/

return [
    'validation_failed' => 'Os dados enviados são inválidos.',
    'unauthenticated' => 'É necessário iniciar sessão.',
    'invalid_token' => 'Sessão inválida ou expirada. Inicie sessão novamente.',
    'forbidden' => 'Não tem permissão para realizar esta ação.',
    'not_found' => 'O recurso pedido não existe.',
    'method_not_allowed' => 'Método não permitido.',
    'too_many_requests' => 'Demasiados pedidos. Tente novamente dentro de momentos.',
    'http_error' => 'Não foi possível processar o pedido.',
    'server_error' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
];
