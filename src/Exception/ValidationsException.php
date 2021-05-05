<?php declare(strict_types=1);

namespace App\Exception;

class ValidationsException extends AppException
{
    private array $exceptions;

    /**
     * @param array<int, AppException> $exceptions
     */
    public function __construct(string $message, array $exceptions)
    {
        parent::__construct($message);
        $this->exceptions = $exceptions;
    }

    /**
     * @return array<int, AppException>
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
