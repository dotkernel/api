<?php

declare(strict_types=1);

namespace Api\User\InputFilter\Input;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\FileInput;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\NotEmpty;

use function sprintf;

class AvatarInput extends FileInput
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => sprintf(Message::VALIDATOR_REQUIRED_FIELD_BY_NAME, 'Avatar'),
            ], true)
            ->attachByName(UploadFile::class, [
                'message' => Message::VALIDATOR_REQUIRED_UPLOAD,
            ], true)->attachByName(IsImage::class, [
                'message' => Message::RESTRICTION_IMAGE,
            ], true);
    }
}
