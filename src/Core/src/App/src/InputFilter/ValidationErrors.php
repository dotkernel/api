<?php

declare(strict_types=1);

namespace Core\App\InputFilter;

use Laminas\InputFilter\ErrorMessages;
use Laminas\InputFilter\ValidationResultInterface;

final class ValidationErrors
{
    private function __construct()
    {
    }

    /**
     * Error messages of the failed inputs only, as a nested array.
     *
     * A validation result also holds an (empty) entry for every input that passed, which is dropped here.
     *
     * @param ValidationResultInterface<mixed> $result
     * @return array<array-key, mixed>
     */
    public static function fromResult(ValidationResultInterface $result): array
    {
        return self::filter($result->getMessages());
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function filter(ErrorMessages $messages): array
    {
        $errors = [];
        foreach ($messages as $key => $message) {
            if ($message instanceof ErrorMessages) {
                $message = self::filter($message);
            }

            if ($message === []) {
                continue;
            }

            $errors[$key] = $message;
        }

        return $errors;
    }
}
