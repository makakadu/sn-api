<?php
declare(strict_types=1);
namespace App\Infrastructure;

class MyRequestPathRule {
    /**
     * Stores all the options passed to the rule
     */
    private array $options = [
        "path" => ["/"],
        "ignore" => []
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request): bool
    {
        $uri = "/" . $request->getUri()->getPath();
        $uri = preg_replace("#/+#", "/", $uri);

        /* If request path is matches ignore should not authenticate. */
        
        // Логика аналогична той, которая в классе RequestPathRule, но с одним отличием, есть переменная $method. Теперь путь будет игнорироваться, если ещё и метод совпадает
        foreach ((array)$this->options["ignore"] as $ignore) {
            print_r($ignore);
            $method = $ignore['method'] ?? null;
            
            $ignore = rtrim($ignore['uri'], "/");
//            echo $ignore;exit();
            
            $uriMatched = !!preg_match("@^{$ignore}(/.*)?$@", (string) $uri);
            
            if(!$method && $uriMatched) {                                                   // Если метод не указан и путь, который мы хочем игнорировать, совпадает с текущим uri - возвращаем false
                return false;
            } else if($method && $uriMatched && $method === $request->getMethod()) {        // Если метод указан и совпадает с методом запроса, а также если путь, который мы хочем игнорировать, совпадает с текущим uri - возвращаем false
                return false;
            }                                                                               // Если возвращается false, то валидацию токена проводить НЕ нужно
            
        }

        /* Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["path"] as $path) {
            $path = rtrim($path, "/");
            if (!!preg_match("@^{$path}(/.*)?$@", (string) $uri)) {
                return true;
            }
        }
        return false;
    }
}