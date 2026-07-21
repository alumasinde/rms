<?php

declare(strict_types=1);

namespace App\Core\Response;

final class Response
{
    private int $status = 200;
    private array $headers = [];
    private string $content = '';

    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function html(string $content): self
    {
        $this->content = $content;
        $this->header('Content-Type', 'text/html; charset=UTF-8');
        return $this;
    }

    public function json(array $data): self
    {
        $this->content = json_encode($data, JSON_THROW_ON_ERROR);
        $this->header('Content-Type', 'application/json');
        return $this;
    }

    public function redirect(string $url, int $status = 302): self
    {
        $this->status = $status;
        $this->header('Location', $url);
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }
}
