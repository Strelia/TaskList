<?php

namespace App\Helper;


use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{

    protected $errors;

    /**
     * ApiException constructor.
     * @param int $statusCode
     * @param string|null $message
     * @param array $errors
     * @param \Throwable|null $previous
     * @param array $headers
     * @param int|null $code
     */
    public function __construct(int $statusCode, string $message = null, $errors = [], \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}