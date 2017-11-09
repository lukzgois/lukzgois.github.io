---
extends: _layouts.post
title: Quer testar interações com APIs? Guzzle e PHPUnit ao resgate
author: Lucas Padilha Gois
date: 2016-07-18
section: content
short: API's são coisas lindas de Deus, mas talvez sejam um pouco chatas quando precisamos testar as interações em nossas aplicações. Felizmente podemos usar Guzzle para isso.
---

Salve galera, como estão?
Essa semana tive que desenvolver algumas funcionalidades do sistema do trabalho que irão interagir com outras API's. Tudo tranquilo até aí, porém logo me veio uma dúvida na cabeça: como testar essa interação de uma maneira prática? Bem, foi muito mais fácil do que eu pensava.

### Introdução
O sistema que estou trabalhando é desenvolvido com Laravel 5.1, então já tenho uma belíssima API de testes para me auxiliar, o que já facilita metade do trabalho. Escolhi utilizar a biblioteca <a href="https://github.com/guzzle/guzzle" target="_blank">Guzzle</a> para interagir com as API's. É uma ótima biblioteca e ainda deixa o código muito mais legível e bonito, muito melhor do que utilizar diretamente com as funções de cURL do PHP.

### Mãos na massa
Vamos então imaginar que temos um método em nosso *controller* que interage com a <a href="https://developer.github.com/v3/" target="_blank">API do Github</a>: 

```php
use GuzzleHttp\Client;

public function user($user, Client $guzzle)
{
    $response = $guzzle->get("https://api.github.com/users/$user");

    return $response->getBody();
}
```

Essa é uma função bem simples, apenas para demonstração. Ela mostrará um json com as informações do usuário na tela. Chegamos ao ponto que queremos testar nossa função. Bom, usando a API de testes do laravel podemos escrever um teste simples:

```php
public function it_gets_the_user_info()
{
    $this->visit('/user/lukzgois')
        ->seeJson([
            "login" => "lukzgois"
        ]);
}
```

Pronto, estamos com nosso teste funcionando certo? Ele está funcionando, sim, mas não é a melhor maneira de testar. O motivo é simples: cada vez que nosso teste for executado, uma chamada HTTP real será feita para a API do github. Com isso temos algumas desvantagens:

- Se nossa API tiver um limite de utilizações, estaremos consumindo esse limite sem motivo.
- O teste irá esperar a resposta da API, o que irá impactar na performance de toda nossa suíte de testes.

Precisamos resolver esses problemas, mas como? A solução é mais simples do que podíamos imaginar. Nossa querida biblioteca Guzzle já possui uma maneira de realizarmos o *mock* das nossa respostas, como pode ser visto <a href="http://docs.guzzlephp.org/en/latest/testing.html" target="_blank">na documentação</a>.

Vamos alterar nosso teste para fazer uso dessa funcionalidade:

```php
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

public function it_gets_the_user_info()
{
    $mock = new MockHandler([
        new Response(200, [], '{"login": "lukzgois"}')
    ]);
    $handler = HandlerStack::create($mock);

    $this->visit('/user/lukzgois')
        ->seeJson([
            "login" => "lukzgois"
        ]);
}
```

Criamos nosso mock, mas agora temos um novo problema: como fazer o laravel usar a nossa versão da biblioteca Guzzle? Aqui temos nosso trabalho facilitado pela mágica do laravel. Vocês devem ter notado que estamos usando o <a href="https://laravel.com/docs/5.1/container" target="_blank">service container</a> do laravel para resolvermos a dependência da Guzzle em nosso controller:

```php
public function userInfo($user, Client $guzzle)
```

Beleza, mas e aí? O que tem a ver estarmos utilizando o service container do laravel para isso? Simplesmente tudo! Se estamos deixando que o laravel resolva essa dependência para nós, podemos dizer ao laravel exatamente como queremos que ele resolva essa dependência, ou seja, podemos utilizar nossa versão com o *mock*. Nosso teste ficará assim:

```php
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

public function it_gets_the_user_info()
{
    $mock = new MockHandler([
        new Response(200, [], '{"login": "lukzgois"}')
    ]);
    $handler = HandlerStack::create($mock);

    app()->bind(Client::class, function ($app) use ($handler) {
        return new Client(['handler' => $handler]);
    });

    $this->visit('/user/lukzgois')
    ->seeJson([
        "login" => "lukzgois"
    ]);
}
```

*Voilà*. Agora vemos a mágica acontecendo. O que fizemos agora é dizer ao laravel que quando nossa aplicação pedir uma instância da classe `GuzzleHttp\Client`, ele deve fornecer a versão com nosso `$handler` personalizado. Então, quando nosso teste for executado, ao invés de fazer uma chamada HTTP real como antes, a biblioteca Guzzle irá fornecer a `Response` que criamos no início do teste. Com isso podemos simular até mesmo outras respostas diferentes de `200 OK`, como por exemplo, forçar uma resposta de erro 500 para verificarmos se nossa aplicação está realizando o tratamento correto.

Estamos quase com a solução final. Nosso único problema aqui é que, se tivermos mais testes que necessitem de uma versão com o *mock* precisaremos repetir o *bind*, o que não ficará algo muito legível ou simples de se entender. Então, para fecharmos esse artigo, iremos fazer uma pequena refatoração para deixamos nosso teste mais legível e reutilizável:

```php
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

public function it_gets_the_user_info()
{
    $this->mockResponse([new Response(200, [], '{"login": "lukzgois"}')]);

    $this->visit('/user/lukzgois')
    ->seeJson([
        "login" => "lukzgois"
    ]);
}

private function mockResponse($responses = [])
{
    $mock = new MockHandler($responses);
    $handler = HandlerStack::create($mock);

    app()->bind(Client::class, function ($app) use ($handler) {
        return new Client(['handler' => $handler]);
    });
}
```

Prontinho, agora se precisarmos fazer o *mock* de mais respostas em outras funções, basta utilizarmos a função `mockResponse`. Se quisermos podemos extrair essa função para um *helper*, mas para o nosso exemplo já está OK.


### Finalizando
Conseguimos escrever um teste para nossa aplicação que irá interagir com a API do github e utilizar o *mock* que a biblioteca [Guzzle] nos oferece. Com isso já podemos testar como nossa aplicação responde a diferentes tipos de resposta e status HTTP.

