<?php

declare(strict_types=1);

$dotAnnotatedServices          = 'dot-annotated-services';
$dotAnnotatedServicesPackage   = 'dotkernel/dot-annotated-services';
$dotDependencyInjection        = 'dot-dependency-injection';
$dotDependencyInjectionPackage = 'dotkernel/dot-dependency-injection';

/**
 * Check composer.json
 */
$path = 'composer.json';
if (! is_readable($path)) {
    exit(sprintf('❌ %s: file not found/readable' . PHP_EOL, $path));
}
echo sprintf('✅ %s: found at %s' . PHP_EOL, $path, realpath($path));

$contents = file_get_contents($path);

if (str_contains($contents, $dotAnnotatedServicesPackage)) {
    exit(sprintf(
        '❌ remove %s using: composer remove %s' . PHP_EOL,
        $dotAnnotatedServices,
        $dotAnnotatedServicesPackage
    ));
}
echo sprintf('✅ %s: %s is not required' . PHP_EOL, $path, $dotAnnotatedServices);

if (! str_contains($contents, $dotDependencyInjectionPackage)) {
    exit(sprintf(
        '❌ require %s using: composer require %s' . PHP_EOL,
        $dotDependencyInjection,
        $dotDependencyInjectionPackage
    ));
}
echo sprintf('✅ %s: %s is required' . PHP_EOL, $path, $dotDependencyInjection);

/**
 * Check config/config.php
 */
$path = 'config/config.php';
if (! is_readable($path)) {
    exit(sprintf('❌ %s: file not found/readable' . PHP_EOL, $path));
}
echo sprintf('✅ %s: found at %s' . PHP_EOL, $path, realpath($path));

$contents = file_get_contents($path);

if (str_contains($contents, 'Dot\AnnotatedServices\ConfigProvider::class')) {
    exit(sprintf('❌ %s: remove Dot\AnnotatedServices\ConfigProvider::class' . PHP_EOL, $path));
}
echo sprintf('✅ %s: Dot\AnnotatedServices\ConfigProvider::class was not found' . PHP_EOL, $path);

if (! str_contains($contents, 'Dot\DependencyInjection\ConfigProvider::class')) {
    exit(sprintf('❌ %s: add Dot\DependencyInjection\ConfigProvider::class' . PHP_EOL, $path));
}
echo sprintf('✅ %s: Dot\DependencyInjection\ConfigProvider::class was found' . PHP_EOL, $path, $dotAnnotatedServices);

$definitionReplacements = [
    'old' => ['=', ':',     '{', '}', ':', '= ', ' :', '"'],
    'new' => ['= ', ' => ', '[', ']', '=', ': ', ':',  "'"],
];

$diReplacements = [
    $dotAnnotatedServices   => [
        'use Dot\AnnotatedServices\Factory\AnnotatedRepositoryFactory;',
        'use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;',
        'use Dot\AnnotatedServices\Annotation\Entity;',
        'use Dot\AnnotatedServices\Annotation\Inject;',
        PHP_EOL . 'use Dot\AnnotatedServices\Annotation\Service;',
        'AnnotatedServiceFactory::class',
        'AnnotatedRepositoryFactory::class',
        PHP_EOL . <<<SRV
/**
 * @Service
 */
SRV,
        PHP_EOL . <<<SRV
/**
 * @Service()
 */
SRV,
    ],
    $dotDependencyInjection => [
        'use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;',
        'use Dot\DependencyInjection\Factory\AttributedServiceFactory;',
        'use Dot\DependencyInjection\Attribute\Entity;',
        'use Dot\DependencyInjection\Attribute\Inject;',
        '',
        'AttributedServiceFactory::class',
        'AttributedRepositoryFactory::class',
        '',
        '',
    ],
];

$iterator = new RegexIterator(
    new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src')),
    '/^.+\.php$/',
    RegexIterator::GET_MATCH
);

echo sprintf(
    PHP_EOL . '✅ All checks passed. Starting %s -> %s conversion of %d files...' . PHP_EOL . PHP_EOL,
    $dotAnnotatedServices,
    $dotDependencyInjection,
    iterator_count($iterator)
);

$modified = $unmodified = 0;
foreach ($iterator as $file) {
    $path = $file[0];
    if (! is_readable($path)) {
        exit(sprintf('File %s is not readable.' . PHP_EOL, $path));
    }

    $before = file_get_contents($path);
    $after  = str_replace($diReplacements[$dotAnnotatedServices], $diReplacements[$dotDependencyInjection], $before);

    /**
     * Convert @ORM\* to #[ORM\*] in repositories
     */
    if (str_contains($path, '/Repository/')) {
        preg_match('/Entity\(name="(.*)"\)/', $after, $matches);
        if (isset($matches[1])) {
            $after = str_replace(
                PHP_EOL . 'class',
                PHP_EOL . '#[Entity(name: \\' . $matches[1] . '::class)]' . PHP_EOL . 'class',
                $after
            );
            $after = str_replace(
                PHP_EOL . ' * @Entity(name="' . $matches[1] . '")',
                '',
                $after
            );
        }
    }

    /**
     * Convert @ORM\* to #[ORM\*] in entities
     */
    if (str_contains($path, '/Entity/')) {
        /**
         * Convert ORM\Entity
         */
        preg_match('/^.*Entity\(repositoryClass="(.*)"\)\s*$/m', $after, $matches);
        if (isset($matches[1])) {
            $after = str_replace(
                PHP_EOL . 'class',
                PHP_EOL . '#[ORM\Entity(repositoryClass: \\' . $matches[1] . '::class)]' . PHP_EOL . 'class',
                $after
            );
            $after = str_replace(PHP_EOL . $matches[0], '', $after);
        }

        /**
         * Convert ORM\Table
         */
        preg_match('/^.*Table\(name="(.*)"\)\s*$/m', $after, $matches);
        if (isset($matches[1])) {
            $after = str_replace(
                PHP_EOL . 'class',
                PHP_EOL . '#[ORM\Table(name: "' . $matches[1] . '")]' . PHP_EOL . 'class',
                $after
            );
            $after = str_replace(PHP_EOL . $matches[0], '', $after);
        }

        /**
         * Convert ORM\EntityListeners
         */
        preg_match('/^.*EntityListeners\(\{(.*)}\)\s*$/m', $after, $matches);
        if (isset($matches[1])) {
            $after = str_replace(
                PHP_EOL . 'class',
                PHP_EOL . '#[ORM\EntityListeners([' . $matches[1] . '])]' . PHP_EOL . 'class',
                $after
            );
            $after = str_replace(PHP_EOL . $matches[0], '', $after);
        }

        /**
         * Convert ORM\HasLifecycleCallbacks
         */
        if (str_contains($after, '@ORM\HasLifecycleCallbacks')) {
            $after = str_replace(
                PHP_EOL . 'class',
                PHP_EOL . '#[ORM\HasLifecycleCallbacks]' . PHP_EOL . 'class',
                $after
            );
            $after = str_replace(
                PHP_EOL . ' * @ORM\HasLifecycleCallbacks',
                '',
                $after
            );
            $after = str_replace(
                PHP_EOL . ' * @ORM\HasLifecycleCallbacks()',
                '',
                $after
            );
        }

        /**
         * Convert entity properties
         */
        preg_match_all(
            '#/\**\n*.*(OneToOne|OneToMany|ManyToOne|ManyToMany|Column)\((.*)\)\n*\s*\*/#m',
            $after,
            $properties
        );
        foreach ($properties[2] ?? [] as $index => $property) {
            $definition = str_replace($definitionReplacements['old'], $definitionReplacements['new'], $property);
            $definition = preg_replace('/\s+/', ' ', $definition);

            $after = str_replace(
                $properties[0][$index],
                sprintf('#[ORM\%s(%s)]', $properties[1][$index], $definition),
                $after
            );

            preg_match('/.*targetEntity:\s\'(\b\w+\b).*/', $definition, $targetEntity);
            if (isset($targetEntity[1])) {
                $after = str_replace("'" . $targetEntity[1] . "'", $targetEntity[1] . '::class', $after);
            }
        }
    }

    /**
     * Replace @Inject(dependencies) with #[Inject(dependencies)] in all classes
     */
    if (str_contains($after, '@Inject({')) {
        preg_match('#/\*\*\n\s*\*\s*@Inject[\s\S\n]*?\*/#', $after, $injectTag);

        if (isset($injectTag[0])) {
            $injectTag = $injectTag[0];

            preg_match_all('/^\s*\*\s*(\b\w*\b::class,*|"[a-zA-Z0-9-._]*",*)$/m', $injectTag, $oldDependencies);

            $newDependencies = [];
            if (isset($oldDependencies[1])) {
                $newDependencies[] = '#[Inject(';
                foreach ($oldDependencies[1] as $dependency) {
                    if (! str_ends_with($dependency, ',')) {
                        $dependency .= ',';
                    }
                    $newDependencies[] = sprintf('        %s', $dependency);
                }
                $newDependencies[] = '    )]';
            }

            $after = str_replace($injectTag, implode(PHP_EOL, $newDependencies), $after);
        }
    }

    /**
     * Remove class/interface/package annotations
     * Remove empty comment block resulted from class/package annotations removing
     * Remove misc typos found in older repos
     */
    $after = preg_replace('/\n\s*\*\s*Class\s*\b\w*\b\s*\(*\s*\)*\s*$/mi', '', $after);
    $after = preg_replace('/\n\s*\*\s*Interface\s*\b\w*\b\s*\(*\s*\)*\s*$/mi', '', $after);
    $after = preg_replace('/\n\s*\*\s*@package\s*.*$/mi', '', $after);
    $after = str_replace(PHP_EOL . ' * @ORM\Entity()()', '', $after);
    $after = preg_replace('/\n\/\**\n\s*\**\/$/m', '', $after);

    if ($before !== $after) {
        file_put_contents($path, $after);

        echo '✅ ' . $path . PHP_EOL;
        $modified++;
    } else {
        echo '🟰 ' . $path . PHP_EOL;
        $unmodified++;
    }
}

echo sprintf(
    PHP_EOL . '🏁 %s -> %s conversion has finished.' . PHP_EOL,
    $dotAnnotatedServices,
    $dotDependencyInjection
);
echo sprintf(' - ✅ %d files were updated' . PHP_EOL, $modified);
echo sprintf(' - 🟰 %d files remained unmodified' . PHP_EOL, $unmodified);
