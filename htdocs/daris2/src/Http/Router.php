<?php

declare(strict_types=1);

namespace Daris\Http;

use Closure;
use Daris\Exception\HttpException;
use Daris\Exception\NotFoundException;

/**
 * Routeur minimaliste : motifs avec paramètres {slug}, par méthode HTTP.
 */
final class Router
{
    /** @var list<array{method: string, pattern: string, regex: string, handler: Closure}> */
    private array $routes = [];

    public function get(string $pattern, Closure $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, Closure $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    /**
     * Trouve la route correspondant à la requête et exécute son gestionnaire.
     *
     * @throws NotFoundException si aucun motif ne correspond
     * @throws HttpException     (405) si le chemin existe pour une autre méthode
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method() === 'HEAD' ? 'GET' : $request->method();
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $request->path(), $matches) !== 1) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $method) {
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            return ($route['handler'])($request, ...array_values($params));
        }

        if ($pathMatched) {
            throw new HttpException(405, 'Méthode non autorisée.');
        }

        throw new NotFoundException();
    }

    private function add(string $method, string $pattern, Closure $handler): self
    {
        $parts = preg_split('/(\{[a-z]+\})/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$pattern];
        $regex = '';
        foreach ($parts as $part) {
            $regex .= preg_match('/^\{([a-z]+)\}$/', $part, $m) === 1
                ? '(?P<' . $m[1] . '>[A-Za-z0-9-]{1,80})'
                : preg_quote($part, '#');
        }

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => '#^' . $regex . '$#',
            'handler' => $handler,
        ];

        return $this;
    }
}
