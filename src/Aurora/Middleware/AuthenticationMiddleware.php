<?php

namespace AuroraLumina\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // Verificar se o usuário está autenticado
        if (!$this->isUserAuthenticated($request)) {
            // Se não estiver autenticado, redirecione para a página de login
            return $this->redirectToLogin();
        }

        // Se estiver autenticado, permita que a solicitação prossiga
        return $handler->handle($request);
    }

    protected function isUserAuthenticated(Request $request): bool
    {
        // Implemente a lógica para verificar se o usuário está autenticado
        // Por exemplo, você pode verificar se existe uma sessão de usuário válida
        // ou se o token de autenticação está presente nos cabeçalhos da solicitação
        return true;
    }

    protected function redirectToLogin(): Response
    {
        return new \Laminas\Diactoros\Response\RedirectResponse('/login');
    }
}
