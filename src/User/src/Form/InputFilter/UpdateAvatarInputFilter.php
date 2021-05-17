<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Api\App\Message;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\NotEmpty;

/**
 * Class UpdateAvatarInputFilter
 * @package Api\User\Form\InputFilter
 */
class UpdateAvatarInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter(): InputFilterInterface
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = new InputFilter();
            $this->inputFilter->add([
                'name' => 'avatar',
                'type' => FileInput::class,
                'required' => true,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_REQUIRED_FIELD
                        ]
                    ], [
                        'name' => UploadFile::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_REQUIRED_UPLOAD
                        ]
                    ], [
                        'name' => IsImage::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::RESTRICTION_IMAGE
                        ]
                    ],
                ]
            ]);
        }

        return $this->inputFilter;
    }
}
