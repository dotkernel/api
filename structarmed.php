<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layerPattern('CoreApp', '/^Core\\\\App\\\\.*$/', '/^Core\\\\App\\\\Fixture\\\\.*$/')
    ->layer('CoreFixture', 'src/Core/src/App/src/Fixture')
    ->layer('CoreSetting', 'src/Core/src/Setting/src')
    ->layer('CoreAdmin', 'src/Core/src/Admin/src')
    ->layer('CoreSecurity', 'src/Core/src/Security/src')
    ->layer('CoreUser', 'src/Core/src/User/src')
    ->layer('App', 'src/App/src')
    ->layer('Security', 'src/Security/src')
    ->layer('Admin', 'src/Admin/src')
    ->layer('User', 'src/User/src')
    ->ruleset([
        'CoreApp'      => ['CoreUser'],
        'CoreSetting'  => ['+CoreApp', 'CoreAdmin'],
        'CoreAdmin'    => ['+CoreSetting'],
        'CoreSecurity' => ['+CoreAdmin', 'CoreUser'],
        'CoreUser'     => ['+CoreSecurity'],
        'CoreFixture'  => ['+CoreUser'],
        'App'          => ['+CoreUser'],
        'Security'     => ['+App'],
        'Admin'        => ['+App'],
        'User'         => ['+App'],
    ]);
