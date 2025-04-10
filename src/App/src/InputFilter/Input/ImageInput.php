<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\FileInput;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\NotEmpty;

class ImageInput extends FileInput
{
    public static array $mimeTypes = ['image/jpeg', 'image/png'];

    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => Message::VALIDATOR_REQUIRED_FIELD,
            ], true)
            ->attachByName(UploadFile::class, [
                'message' => Message::VALIDATOR_REQUIRED_UPLOAD,
            ], true)
            ->attachByName(IsImage::class, [
                'message' => Message::restrictionImage(self::$mimeTypes),
            ], true)
            ->attachByName(MimeType::class, [
                'mimeType' => self::$mimeTypes,
                'message'  => Message::restrictionImage(self::$mimeTypes),
            ], true);
    }
}
