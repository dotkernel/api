<?php

declare(strict_types=1);

namespace App\User\Form;

use Zend\Filter\File\RenameUpload;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\IsImage;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\UploadFile;

use function getcwd;

/**
 * Class UserAvatarInputFilter
 * @package App\User\Form
 */
class UserAvatarInputFilter extends InputFilter
{
    /**
     * UserAvatarInputFilter constructor.
     */
    public function __construct()
    {
        $this->add([
            'name' => 'avatar',
            'type' => FileInput::class,
            'required' => true,
            'validators' => [
                [
                    'name' => UploadFile::class
                ],
                [
                    'name' => IsImage::class
                ],
            ]
        ]);
    }
}
