<?php

declare(strict_types=1);

namespace Core\App;

use function count;
use function implode;
use function sprintf;

class Message
{
    public const ACCOUNT_UPDATED              = 'Your account was updated successfully.';
    public const ADMIN_CONFIRM_DELETION       = 'Please confirm the admin deletion.';
    public const ADMIN_CREATED                = 'Admin created successfully.';
    public const ADMIN_DELETED                = 'Admin deleted successfully';
    public const ADMIN_INACTIVE               = 'Admin account is inactive.';
    public const ADMIN_NOT_FOUND              = 'Admin not found.';
    public const ADMIN_UPDATED                = 'Admin updated successfully.';
    public const AN_ERROR_OCCURRED            = 'An error occurred, please try again later.';
    public const DUPLICATE_EMAIL              = 'An account with this email address already exists.';
    public const DUPLICATE_IDENTITY           = 'An account with this identity already exists.';
    public const ERROR_REPORT_OK              = 'Error report successfully saved.';
    public const ERROR_REPORT_NOT_ALLOWED     = 'You are not allowed to report errors.';
    public const ERROR_REPORT_NOT_ENABLED     = 'Remote error reporting is not enabled.';
    public const INVALID_CLIENT_ID            = 'Invalid client_id.';
    public const INVALID_CONFIG               = 'Invalid configuration value: "%s"';
    public const INVALID_CSRF                 = 'Invalid CSRF.';
    public const INVALID_CURRENT_PASSWORD     = 'Current password is incorrect.';
    public const INVALID_VALUE                = 'The value specified for "%s" is invalid.';
    public const MAIL_NOT_SENT_TO             = 'Could not send mail to "%s".';
    public const MAIL_SENT_RECOVER_IDENTITY   = 'If the provided email identifies an account in our system, '
    . 'you will receive an email with your account\'s identity.';
    public const MAIL_SENT_RESET_PASSWORD     = 'If the provided email identifies an account in our system, '
    . 'you will receive an email with further instructions on resetting your account\'s password.';
    public const MAIL_SENT_USER_ACTIVATION    = 'User activation mail has been successfully sent to "%s"';
    public const MISSING_CONFIG               = 'Missing configuration value: "%s".';
    public const NOT_ACCEPTABLE               = 'Not acceptable.';
    public const RESET_PASSWORD_EXPIRED       = 'Reset password hash is invalid (expired).';
    public const RESET_PASSWORD_NOT_FOUND     = 'Reset password request not found.';
    public const RESET_PASSWORD_OK            = 'Password successfully modified.';
    public const RESET_PASSWORD_USED          = 'Reset password hash is invalid (used).';
    public const RESET_PASSWORD_VALID         = 'Reset password hash is valid.';
    public const RESOURCE_ALREADY_REGISTERED  = 'Resource "%s" is already registered.';
    public const RESOURCE_NOT_ALLOWED         = 'You are not allowed to access this resource.';
    public const RESOURCE_NOT_FOUND           = '%s not found.';
    public const RESTRICTION_DEPRECATION      = 'Cannot use both "%s" and "%s" attributes on the same object.';
    public const RESTRICTION_IMAGE            = 'File must be an image> Accepted mim type(s): %s';
    public const RESTRICTION_ROLES            = 'At least one role is required.';
    public const ROLE_NOT_FOUND               = 'Role not found.';
    public const SERVICE_NOT_FOUND            = 'Service %s not found in the container.';
    public const SETTING_NOT_FOUND            = 'Setting "%s" not found.';
    public const TEMPLATE_NOT_FOUND           = 'Template "%s" not found.';
    public const UNSUPPORTED_MEDIA_TYPE       = 'Unsupported Media Type.';
    public const USER_ACTIVATED               = 'User account has been activated.';
    public const USER_ALREADY_ACTIVATED       = 'User account is already active.';
    public const USER_ALREADY_DEACTIVATED     = 'User account is already inactive.';
    public const USER_AVATAR_MISSING          = 'User avatar not found.';
    public const USER_AVATAR_UPDATED          = 'User avatar updated successfully.';
    public const USER_CONFIRM_DELETION        = 'Please confirm the user deletion.';
    public const USER_CREATED                 = 'User created successfully.';
    public const USER_DEACTIVATED             = 'User account has been deactivated.';
    public const USER_DELETED                 = 'User account deleted successfully.';
    public const USER_NOT_ACTIVATED           = 'User account must be activated first.';
    public const USER_NOT_FOUND               = 'User not found.';
    public const USER_UPDATED                 = 'User updated successfully.';
    public const VALIDATOR_INVALID_CHARACTERS = 'The value specified contains invalid characters.';
    public const VALIDATOR_INVALID_DATA       = 'The submitted request contains invalid data.';
    public const VALIDATOR_INVALID_EMAIL      = 'The value specified must be a valid email address.';
    public const VALIDATOR_LENGTH_MAX         = 'The value specified must have at most %d characters.';
    public const VALIDATOR_LENGTH_MIN         = 'The value specified must have at least %d characters.';
    public const VALIDATOR_LENGTH_MIN_MAX     = 'The value specified must have between %d and %d characters.';
    public const VALIDATOR_MISMATCH           = '"%s" and "%s" do not match.';
    public const VALIDATOR_REQUIRED_FIELD     = 'This field is required and cannot be empty.';
    public const VALIDATOR_REQUIRED_UPLOAD    = 'A file must be uploaded first.';

    /**
     * @return non-empty-string
     */
    public static function invalidConfig(string $config): string
    {
        return sprintf(self::INVALID_CONFIG, $config);
    }

    /**
     * @return non-empty-string
     */
    public static function invalidValue(string $value): string
    {
        return sprintf(self::INVALID_VALUE, $value);
    }

    /**
     * @return non-empty-string
     */
    public static function missingConfig(string $config): string
    {
        return sprintf(self::MISSING_CONFIG, $config);
    }

    /**
     * @return non-empty-string
     */
    public static function mailNotSentTo(string $email): string
    {
        return sprintf(self::MAIL_NOT_SENT_TO, $email);
    }

    /**
     * @return non-empty-string
     */
    public static function mailSentUserActivation(string $email): string
    {
        return sprintf(self::MAIL_SENT_USER_ACTIVATION, $email);
    }

    /**
     * @param string[] $types
     * @return non-empty-string
     */
    public static function notAcceptable(array $types = []): string
    {
        if (count($types) === 0) {
            return self::NOT_ACCEPTABLE;
        }

        return sprintf('%s Supported types: %s', self::NOT_ACCEPTABLE, implode(', ', $types));
    }

    /**
     * @return non-empty-string
     */
    public static function resourceAlreadyRegistered(string $resource): string
    {
        return sprintf(self::RESOURCE_ALREADY_REGISTERED, $resource);
    }

    /**
     * @return non-empty-string
     */
    public static function resourceNotFound(string $resource = 'Resource'): string
    {
        return sprintf(self::RESOURCE_NOT_FOUND, $resource);
    }

    /**
     * @return non-empty-string
     */
    public static function restrictionDeprecation(string $first, string $second): string
    {
        return sprintf(self::RESTRICTION_DEPRECATION, $first, $second);
    }

    /**
     * @param string[] $mimeTypes
     * @return non-empty-string
     */
    public static function restrictionImage(array $mimeTypes): string
    {
        return sprintf(self::RESTRICTION_IMAGE, implode(',', $mimeTypes));
    }

    /**
     * @return non-empty-string
     */
    public static function serviceNotFound(string $service): string
    {
        return sprintf(self::SERVICE_NOT_FOUND, $service);
    }

    /**
     * @return non-empty-string
     */
    public static function settingNotFound(string $identifier): string
    {
        return sprintf(self::SETTING_NOT_FOUND, $identifier);
    }

    /**
     * @return non-empty-string
     */
    public static function templateNotFound(string $template): string
    {
        return sprintf(self::TEMPLATE_NOT_FOUND, $template);
    }

    /**
     * @param string[] $types
     * @return non-empty-string
     */
    public static function unsupportedMediaType(array $types = []): string
    {
        if (count($types) === 0) {
            return self::UNSUPPORTED_MEDIA_TYPE;
        }

        return sprintf('%s Supported types: %s', self::UNSUPPORTED_MEDIA_TYPE, implode(', ', $types));
    }

    /**
     * @return non-empty-string
     */
    public static function validatorLengthMax(int $max): string
    {
        return sprintf(self::VALIDATOR_LENGTH_MAX, $max);
    }

    /**
     * @return non-empty-string
     */
    public static function validatorLengthMin(int $min): string
    {
        return sprintf(self::VALIDATOR_LENGTH_MIN, $min);
    }

    /**
     * @return non-empty-string
     */
    public static function validatorLengthMinMax(int $min, int $max): string
    {
        return sprintf(self::VALIDATOR_LENGTH_MIN_MAX, $min, $max);
    }

    /**
     * @return non-empty-string
     */
    public static function validatorMismatch(string $first, string $second): string
    {
        return sprintf(self::VALIDATOR_MISMATCH, $first, $second);
    }
}
