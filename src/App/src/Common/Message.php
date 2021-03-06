<?php

declare(strict_types=1);

namespace Api\App\Common;

/**
 * Class Message
 * @package Api\App\Common
 */
class Message
{
    public const ADMIN_NOT_ACTIVATED = 'This account is deactivated.';
    public const DUPLICATE_EMAIL = 'An account with this email address already exists.';
    public const DUPLICATE_IDENTITY = 'An account with this identity already exists.';
    public const INVALID_ACTIVATION_CODE = 'Invalid activation code.';
    public const INVALID_CLIENT_ID = 'Invalid client_id.';
    public const INVALID_VALUE = 'The value specified for \'%s\' is invalid.';
    public const INVALID_EMAIL = 'Invalid email';
    public const INVALID_IDENTIFIER = 'User cannot be found by supplied identifier';
    public const MAIL_SENT_RESET_PASSWORD = 'If the provided email identifies an account in our system, ' .
        'you will receive an email with further instructions on resetting your account\'s password.';
    public const MAIL_SENT_RECOVER_IDENTITY = 'If the provided email identifies an account in our system, ' .
    'you will receive an email with your account\'s identity.';
    public const MAIL_SENT_USER_ACTIVATION = 'User activation mail has been successfully sent to \'%s\'';
    public const MISSING_PARAMETER = 'Missing parameter: \'%s\'';
    public const NOT_FOUND_BY_UUID = 'Unable to find %s identified by uuid: %s';
    public const RESET_PASSWORD_EXPIRED = 'Password reset request for hash: \'%s\' is invalid (expired).';
    public const RESET_PASSWORD_NOT_FOUND = 'Could not find password reset request for hash: \'%s\'';
    public const RESET_PASSWORD_USED = 'Password reset request for hash: \'%s\' is invalid (completed).';
    public const RESET_PASSWORD_VALID = 'Password reset request for hash: \'%s\' exists and is valid.';
    public const RESOURCE_NOT_ALLOWED = 'You are not allowed to access this resource.';
    public const RESTRICTION_IMAGE = 'File must be an image (jpg, png).';
    public const RESTRICTION_ROLES = 'User accounts must have at least one role.';
    public const USER_ALREADY_ACTIVATED = 'This account is already active.';
    public const USER_NOT_ACTIVATED = 'User account must be activated first.';
    public const USER_NOT_FOUND_BY_EMAIL = 'Could not find account identified by email \'%s\'';
    public const VALIDATOR_REQUIRED_FIELD = 'This field is required and cannot be empty.';
    public const VALIDATOR_REQUIRED_UPLOAD = 'A file must be uploaded first.';
}
