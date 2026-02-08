<?php

namespace Application;

class Page
{
    public function list(array $items): void
    {
        http_response_code(200);
        echo json_encode($items);
    }

    public function item(array|int|false $item = false): void
    {
        if ($item === false) {
            $this->notFound();
            return;
        }

        http_response_code(200);
        echo json_encode($item);
    }

    public function created(array|int $item): void
    {
        http_response_code(201);
        echo json_encode($item);
    }

    public function badRequest(): void
    {
        http_response_code(400);
        echo json_encode(["error" => "Bad request"]);
    }

    public function notFound(): void
    {
        http_response_code(404);
        echo json_encode(["error" => "Not found"]);
    }
}
