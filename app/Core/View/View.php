<?php

declare(strict_types=1);

namespace App\Core\View;

use App\Core\Exceptions\ViewException;

/**
 * Renders a view file (HTML only, no business logic) and wraps it
 * in a layout via output buffering. Views receive data as extracted
 * variables; they must not touch the database or contain PHP logic
 * beyond simple loops/conditionals for display.
 */
final class View
{
    public function __construct(
        private string $viewsPath,
        private array $shared = []
    ) {
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function render(string $view, array $data = [], ?string $layout = 'layouts.app'): string
    {
        $content = $this->renderView($view, $data);

        if ($layout === null) {
            return $content;
        }

        return $this->renderView($layout, array_merge($data, ['content' => $content]));
    }

    public function renderPartial(string $view, array $data = []): string
    {
        return $this->renderView($view, $data);
    }

    private function renderView(string $view, array $data): string
    {
        $path = $this->resolvePath($view);

        if (!is_file($path)) {
            throw new ViewException("View [{$view}] not found at {$path}");
        }

        extract(array_merge($this->shared, $data), EXTR_SKIP);

        ob_start();
        require $path;
        return ob_get_clean();
    }

    private function resolvePath(string $view): string
    {
        $relative = str_replace('.', '/', $view) . '.php';
        return rtrim($this->viewsPath, '/') . '/' . $relative;
    }
}
