<?php

declare(strict_types=1);

namespace Api\App\Common;

/**
 * Class Message
 * @package Api\App\Common
 */
class Message
{
    const DUPLICATE_EMAIL = 'An account with this email address already exists.';
    const INVALID_ACTIVATION_CODE = 'Invalid activation code.';
    const INVALID_VALUE = 'The value specified for %s is invalid.';
    const MISSING_PARAMETER = 'Missing parameter: %s';
    const NOT_FOUND_BY_UUID = 'Unable to find %s identified by uuid: %s';
    const RESOURCE_NOT_ALLOWED = 'You are not allowed to access this resource.';
    const RESTRICTION_IMAGE = 'File must be an image (jpg, png).';
    const RESTRICTION_ROLES = 'User accounts must have at least one role.';
    const USER_ALREADY_ACTIVATED = 'This account is already active.';
    const USER_NOT_ACTIVATED = 'User account must be activated first.';
    const USER_NOT_FOUND_BY_EMAIL = 'Could not find account identified by email %s';
    const VALIDATOR_REQUIRED_FIELD = 'This field is required and cannot be empty.';
    const VALIDATOR_REQUIRED_UPLOAD = 'A file must be uploaded first.';
}
