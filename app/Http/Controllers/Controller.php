<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;

/**
 * Base Controller class using Illuminate HTTP components
 */
abstract class Controller
{
    protected ValidationFactory $validator;
    
    public function __construct(ValidationFactory $validator)
    {
        $this->validator = $validator;
    }
    /**
     * Render a view with data
     */
    protected function view(string $view, array $data = []): Response
    {
        $viewPath = $this->resolveViewPath($view);
        
        if (!file_exists($viewPath)) {
            return new Response("View not found: {$view}", 404);
        }
        
        // Extract data for use in view
        extract($data);
        
        // Capture view output
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        return new Response($content);
    }
    
    /**
     * Return a JSON response
     */
    protected function json(array $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }
    
    /**
     * Return a plain response
     */
    protected function response(string $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
    
    /**
     * Validate request data using Illuminate validation
     */
    protected function validate(Request $request, array $rules, array $messages = [], array $attributes = []): array
    {
        $validator = $this->validator->make($request->all(), $rules, $messages, $attributes);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }

    
    /**
     * Resolve view file path
     */
    private function resolveViewPath(string $view): string
    {
        $view = str_replace('.', '/', $view);
        return resource_path('views/' . $view . '.php');
    }
}