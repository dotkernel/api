<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\NotEmpty;

/**
 * UploadFile must stay first: FileInput only skips auto-prepending its own UploadFile validator when one is
 * already at the top of the chain.
 *
 * @phpstan-import-type InputSpecification from InputFilterInterface
 */
final class ImageInput
{
    /** @var non-empty-string[] */
    public static array $mimeTypes = ['image/jpeg', 'image/png'];

    private function __construct()
    {
    }

    /**
     * @param non-empty-string $name
     * @return InputSpecification
     */
    public static function spec(string $name, bool $isRequired = true): array
    {
        return [
            'type'       => FileInput::class,
            'name'       => $name,
            'required'   => $isRequired,
            'validators' => [
                [
                    'name'                   => UploadFile::class,
                    'options'                => [
                        'messages' => [
                            UploadFile::INI_SIZE   => Message::VALIDATOR_REQUIRED_UPLOAD,
                            UploadFile::FORM_SIZE  => Message::VALIDATOR_REQUIRED_UPLOAD,
                            UploadFile::PARTIAL    => Message::VALIDATOR_REQUIRED_UPLOAD,
                            UploadFile::NO_FILE    => Message::VALIDATOR_REQUIRED_UPLOAD,
                            UploadFile::NO_TMP_DIR => Message::VALIDATOR_REQUIRED_UPLOAD,
                            UploadFile::CANT_WRITE => Message::VALIDATOR_REQUIRED_UPLOAD,
                            UploadFile::EXTENSION  => Message::VALIDATOR_REQUIRED_UPLOAD,
                            UploadFile::UNKNOWN    => Message::VALIDATOR_REQUIRED_UPLOAD,
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name'                   => NotEmpty::class,
                    'options'                => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => Message::VALIDATOR_REQUIRED_FIELD,
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name'                   => IsImage::class,
                    'options'                => [
                        'messages' => [
                            IsImage::FALSE_TYPE   => Message::restrictionImage(self::$mimeTypes),
                            IsImage::NOT_READABLE => Message::restrictionImage(self::$mimeTypes),
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name'                   => MimeType::class,
                    'options'                => [
                        'mimeType' => self::$mimeTypes,
                        'messages' => [
                            MimeType::FALSE_TYPE => Message::restrictionImage(self::$mimeTypes),
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
        ];
    }
}
