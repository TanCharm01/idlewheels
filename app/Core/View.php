<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $content = self::capture($view, $data);
        if ($layout === null) {
            echo $content;
            return;
        }
        echo self::capture($layout, array_merge($data, ['content' => $content]));
    }

    public static function capture(string $view, array $data = []): string
    {
        $file = BASE_PATH . '/app/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $view");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    public static function partial(string $view, array $data = []): string
    {
        return self::capture($view, $data);
    }
}
