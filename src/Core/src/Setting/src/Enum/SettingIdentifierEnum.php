<?php

declare(strict_types=1);

namespace Core\Setting\Enum;

use function array_column;

enum SettingIdentifierEnum: string
{
    case IdentifierTableAdminListSelectedColumns       = 'table_admin_list_selected_columns';
    case IdentifierTableAdminListLoginsSelectedColumns = 'table_admin_list_logins_selected_columns';
    case IdentifierTableUserListSelectedColumns        = 'table_user_list_selected_columns';

    /**
     * @return non-empty-string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
