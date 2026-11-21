<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function validate(array $rules, ?string $backTo = null): array
    {
        $v = new Validator($_POST, $rules);
        if (!$v->passes()) {
            Session::withInput($_POST, $v->errors());
            flash('error', 'Please check the highlighted fields.');
            $backTo ? redirect($backTo) : back();
        }
        return $v->data();
    }
}
