<?php

declare(strict_types=1);

namespace Api\App;

class Message
{
    public const ACCEPT_NOT_ACCEPTABLE            = 'Not Acceptable';
    public const ACCEPT_NOT_RESOLVABLE            = 'Unable to resolve Accept header to a representation';
    public const ADMIN_CREATED                    = 'Admin account has been created.';
    public const ADMIN_INACTIVE                   = 'Admin account must be activated first.';
    public const ADMIN_NOT_FOUND                  = 'Admin account was not found.';
    public const ADMIN_ROLE_NOT_FOUND             = 'Admin role not found.';
    public const BAD_REQUEST                      = 'The submitted data contains invalid values.';
    public const CONFLICT                         = 'The submitted request conflicts with the current state of an'
    . ' existing resource.';
    public const DUPLICATE_EMAIL                  = 'An account with this email address already exists.';
    public const DUPLICATE_IDENTITY               = 'An account with this identity already exists.';
    public const ERROR_REPORT_OK                  = 'Error report successfully saved.';
    public const ERROR_REPORT_NOT_ALLOWED         = 'The client is not allowed to report errors.';
    public const ERROR_REPORT_NOT_CONFIGURED      = 'Error report feature is not configured correctly.';
    public const ERROR_REPORT_NOT_ENABLED         = 'Error report feature is not enabled.';
    public const ERROR_REPORT_UNAUTHORIZED        = 'The client must provide a valid error reporting token via the %s'
    . ' header.';
    public const EXPIRED                          = 'The requested resource has expired.';
    public const FORBIDDEN                        = 'The client is not allowed to access this resource.';
    public const INVALID_CLIENT_ID                = 'The submitted client_id is invalid.';
    public const INVALID_CONFIG                   = 'Invalid configuration value for: \'%s\'';
    public const INVALID_VALUE                    = 'The value specified for \'%s\' is invalid.';
    public const INVALID_VALUE_USE_ONE_OF         = 'The value specified for \'%s\' is invalid. The client should use'
    . ' one of the predefined values.';
    public const INTERNAL_SERVER_ERROR            = 'An unexpected error occurred while processing the client request.';
    public const MAIL_NOT_SENT_TO                 = 'Unable to send mail to \'%s\'.';
    public const MAIL_SENT_RECOVER_IDENTITY       = 'If the provided email identifies an account in our system, '
    . 'the account owner will receive an email with the account identity.';
    public const MAIL_SENT_RESET_PASSWORD         = 'If the provided email identifies an account in our system, '
    . 'the account owner will receive an email with further instructions on resetting the account password.';
    public const MAIL_SENT_USER_ACTIVATION        = 'User activation mail has been successfully sent to \'%s\'';
    public const METHOD_NOT_ALLOWED               = 'The request method is not supported for the requested resource.';
    public const MISSING_CONFIG                   = 'Missing configuration value: \'%s\'.';
    public const NOT_ENOUGH_PERMISSIONS           = 'To access this resource, the client needs to have the right'
    . ' permissions.';
    public const OAUTH_MISSING_CONFIG             = 'Unable to convert to JWT without config.';
    public const OAUTH_MISSING_PRIVATE_KEY        = 'Unable to init JWT without private key';
    public const RESET_PASSWORD_EXPIRED           = 'Password reset request is invalid (expired).';
    public const RESET_PASSWORD_NOT_FOUND         = 'Password reset request not found.';
    public const RESET_PASSWORD_OK                = 'Password successfully modified.';
    public const RESET_PASSWORD_USED              = 'Password reset request is invalid (used).';
    public const RESET_PASSWORD_VALID             = 'Password reset request is valid.';
    public const NOT_FOUND                        = 'The requested resource could not be found.';
    public const RESTRICTION_DEPRECATION          = 'Cannot use both `%s` and `%s` attributes on the same object.';
    public const RESTRICTION_IMAGE                = 'Input file must have one of the following formats: jpg or png.';
    public const RESTRICTION_ROLES                = 'Accounts must have at least one role.';
    public const SERVICE_NOT_FOUND                = 'Service %s not found in container.';
    public const UNAUTHORIZED                     = 'The client must be authorized before accessing this resource.';
    public const UNSUPPORTED_MEDIA_TYPE           = 'Unsupported Media Type';
    public const USER_ACTIVATED                   = 'User account has been activated.';
    public const USER_ALREADY_ACTIVE              = 'User account is already active.';
    public const USER_AVATAR_NOT_FOUND            = 'User avatar was not found.';
    public const USER_INACTIVE                    = 'User account must be activated first.';
    public const USER_NOT_FOUND                   = 'User account was not found.';
    public const USER_ROLE_NOT_FOUND              = 'User role not found.';
    public const VALIDATOR_FIX_ERRORS             = 'Fix the errors and try again.';
    public const VALIDATOR_MIN_LENGTH             = '%s must be at least %d characters long.';
    public const VALIDATOR_PASSWORD_MISMATCH      = 'Password confirmation does not match the provided password.';
    public const VALIDATOR_REQUIRED_FIELD_BY_NAME = '%s is required and cannot be empty.';
    public const VALIDATOR_REQUIRED_UPLOAD        = 'A file must be uploaded first.';
}
