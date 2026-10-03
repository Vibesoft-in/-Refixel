<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\View;

abstract class Controller
{
    protected function render(string $template, array $data = [], ?string $layout = 'customer'): Response
    {
        return View::render($template, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    protected function validate(Request $request, array $rules): Validator
    {
        return Validator::make($request->all(), $rules);
    }

    protected function user(): ?array
    {
        return Auth::user();
    }

    protected function userId(): ?int
    {
        return Auth::id();
    }
}
