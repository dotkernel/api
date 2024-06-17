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

$doctrineKeywords = [
    'Id',
    'GeneratedValue',
    'CustomIdGenerator',
    'OneToOne',
    'OneToMany',
    'ManyToOne',
    'ManyToMany',
    'JoinColumn',
    'JoinColumns',
    'JoinTable',
    'Column',
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
                PHP_EOL . '#[ORM\Table(name: \'' . $matches[1] . '\')]' . PHP_EOL . 'class',
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
        preg_match_all('#/\*[\s\S]*?\*/#m', $after, $properties);
        foreach ($properties[0] as $property) {
            /**
             * Drop comments which do not contain at least one Doctrine keyword
             */
            preg_match(
                '/@ORM\\\(.*)/m',
                $property,
                $matches
            );
            if (empty($matches)) {
                continue;
            }

            $mappings = [];

            /**
             * Split Doctrine data into an array of Doctrine statements
             * [
             *      'ORM\ManyToMany(...)',
             *      'ORM\JoinTable(...)',
             * ]
             */
            $doctrineClassCalls = preg_split('/\s*\*\s*@ORM\\\/', $property);
            foreach ($doctrineClassCalls as $doctrineCallString) {
                $mapping = [
                    'type'       => '',
                    'attributes' => [],
                ];

                /**
                 * Drop comment lines which do not contain at least one Doctrine keyword
                 */
                preg_match(
                    '/(' . implode('|', $doctrineKeywords) . ')/m',
                    $doctrineCallString,
                    $matches
                );
                if (empty($matches)) {
                    continue;
                }

                $doctrineCallString = preg_replace('/\s*\n*\s*\*\//', ' ', $doctrineCallString);
                $doctrineCallString = preg_replace('/\s*\*\s*/', ' ', $doctrineCallString);
                $doctrineCallString = trim($doctrineCallString);

                $pattern = '/^(?P<className>' . implode('|', $doctrineKeywords) . ')\(\s*(?P<attributes>.*)\s*\)$/m';
                preg_match($pattern, $doctrineCallString, $doctrineCallArray);

                $mapping['type'] = $doctrineCallArray['className'];
                $classAttributes = $doctrineCallArray['attributes'];

                preg_match_all('/(\w+)=("[^"]*"|\d+|\w+|{[^}]*})/m', $classAttributes, $classAttributes);
                if (! isset($classAttributes[2])) {
                    continue;
                }
                foreach ($classAttributes[2] as $i => $classAttribute) {
                    $classAttribute = preg_replace('/\s+/', '', $classAttribute);

                    // cascade={"persist", "remove"}
                    if (
                        str_starts_with($classAttribute, '{"')
                        && str_ends_with($classAttribute, '"}')
                        && ! str_contains($classAttribute, ':')
                    ) {
                        preg_match_all('/"([^"]*)"/m', $classAttribute, $listValuesMatches);
                        if (count($listValuesMatches[1]) > 0) {
                            $mapping['attributes'][$classAttributes[1][$i]] = sprintf(
                                "['%s']",
                                implode("', '", $listValuesMatches[1])
                            );
                            continue;
                        }
                    }

                    preg_match(
                        '/(JoinColumn|JoinColumns|JoinTable)\(.*\)/m',
                        $classAttribute,
                        $doctrineKeywordMatches
                    );
                    if (! empty($doctrineKeywordMatches)) {
                        preg_match_all('/(\w+)=("[^"]+")/m', $classAttribute, $doctrineAttrMatches);
                        $attributes = [];
                        foreach ($doctrineAttrMatches[2] as $j => $doctrineAttrMatch) {
                            $attributes[$doctrineAttrMatches[1][$j]] = str_replace('"', "'", $doctrineAttrMatch);
                        }

                        $mapping['attributes'][$classAttributes[1][$i]] = [
                            'type'       => $doctrineKeywordMatches[1],
                            'attributes' => $attributes,
                        ];
                        continue;
                    }

                    // options={"unsigned":true,'default':0}
                    if (
                        str_starts_with($classAttribute, '{')
                        && str_ends_with($classAttribute, '}')
                        && str_contains($classAttribute, ':')
                    ) {
                        $classAttribute = str_replace("'", '"', $classAttribute);
                        preg_match_all('/("*\w+"*)*:("[^"]*"|\d+|\w+)/m', $classAttribute, $jsonValueMatches);
                        if (count($jsonValueMatches[2]) > 0) {
                            $attributes = [];
                            foreach ($jsonValueMatches[2] as $j => $jsonValueMatch) {
                                $key              = str_replace('"', "'", $jsonValueMatches[1][$j]);
                                $attributes[$key] = $jsonValueMatch;
                            }

                            $mapping['attributes'][$classAttributes[1][$i]] = $attributes;
                            continue;
                        }
                    }

                    $mapping['attributes'][$classAttributes[1][$i]] = str_replace('"', "'", $classAttribute);
                }
                $mappings[] = $mapping;
            }

            $replacements = [];
            foreach ($mappings as $mapping) {
                switch ($mapping['type']) {
                    case 'Id':
                        $replacements[] = sprintf('#[ORM\%s]', $mapping['type']);
                        break;
                    case 'GeneratedValue':
                        $keyValue = [];
                        foreach ($mapping['attributes'] as $key => $value) {
                            $keyValue[] = sprintf('%s: %s', $key, $value);
                        }
                        $replacements[] = sprintf('    #[ORM\%s(%s)]', $mapping['type'], implode(', ', $keyValue));
                        break;
                    case 'CustomIdGenerator':
                        $keyValue = [];
                        if (array_key_exists('class', $mapping['attributes'])) {
                            $keyValue[] = sprintf(
                                'class: \%s::class',
                                str_replace("'", '', (string) $mapping['attributes']['class'])
                            );
                        }
                        $replacements[] = sprintf('    #[ORM\%s(%s)]', $mapping['type'], implode(', ', $keyValue));
                        break;
                    case 'Column':
                        $keyValue = [];
                        foreach ($mapping['attributes'] as $key => $value) {
                            if (is_array($value)) {
                                $keyValue2 = [];
                                foreach ($value as $key2 => $value2) {
                                    $keyValue2[] = sprintf('%s => %s', $key2, $value2);
                                }
                                $keyValue[] = sprintf('%s: [%s]', $key, implode(', ', $keyValue2));
                            } else {
                                $keyValue[] = sprintf('%s: %s', $key, $value);
                            }
                        }
                        if (in_array('#[ORM\Id]', $replacements)) {
                            $replacements[] = sprintf('    #[ORM\%s(%s)]', $mapping['type'], implode(', ', $keyValue));
                        } else {
                            $replacements[] = sprintf('#[ORM\%s(%s)]', $mapping['type'], implode(', ', $keyValue));
                        }
                        break;
                    case 'OneToOne':
                    case 'OneToMany':
                    case 'ManyToOne':
                    case 'ManyToMany':
                        $keyValue = [];
                        if (isset($mapping['attributes']['mappedBy'])) {
                            $keyValue[] = sprintf('mappedBy: %s', trim($mapping['attributes']['mappedBy']));
                        }
                        if (isset($mapping['attributes']['inversedBy'])) {
                            $keyValue[] = sprintf('inversedBy: %s', trim($mapping['attributes']['inversedBy']));
                        }
                        if (isset($mapping['attributes']['targetEntity'])) {
                            $pattern = 'targetEntity: %s::class';
                            if (str_contains($mapping['attributes']['targetEntity'], '\\')) {
                                $pattern = 'targetEntity: \%s::class';
                            }
                            $keyValue[] = sprintf(
                                $pattern,
                                str_replace("'", '', trim($mapping['attributes']['targetEntity']))
                            );
                        }
                        if (isset($mapping['attributes']['cascade'])) {
                            $keyValue[] = sprintf('cascade: %s', trim($mapping['attributes']['cascade']));
                        }
                        if (isset($mapping['attributes']['fetch'])) {
                            $keyValue[] = sprintf(
                                'fetch: %s',
                                str_replace('"', '', trim($mapping['attributes']['fetch']))
                            );
                        }
                        if (isset($mapping['attributes']['orphanRemoval'])) {
                            $keyValue[] = sprintf('orphanRemoval: %s', trim($mapping['attributes']['orphanRemoval']));
                        }
                        if (isset($mapping['attributes']['indexBy'])) {
                            $keyValue[] = sprintf('indexBy: %s', trim($mapping['attributes']['indexBy']));
                        }
                        $replacements[] = sprintf('#[ORM\%s(%s)]', $mapping['type'], implode(', ', $keyValue));
                        break;
                    case 'JoinColumn':
                        $keyValue = [];
                        if (isset($mapping['attributes']['name'])) {
                            $keyValue[] = sprintf('name: %s', trim($mapping['attributes']['name']));
                        }
                        if (isset($mapping['attributes']['referencedColumnName'])) {
                            $keyValue[] = sprintf(
                                'referencedColumnName: %s',
                                trim($mapping['attributes']['referencedColumnName'])
                            );
                        }
                        $replacements[] = sprintf('    #[ORM\%s(%s)]', $mapping['type'], implode(', ', $keyValue));
                        break;
                    case 'JoinTable':
                        $keyValue = [];
                        if (isset($mapping['attributes']['name'])) {
                            $keyValue[] = sprintf('name: %s', trim($mapping['attributes']['name']));
                        }
                        $replacements[] = sprintf('    #[ORM\%s(%s)]', $mapping['type'], implode(', ', $keyValue));

                        if (isset($mapping['attributes']['joinColumns'])) {
                            $keyValue = [];
                            foreach ($mapping['attributes']['joinColumns']['attributes'] as $key => $value) {
                                $keyValue[] = sprintf('%s: %s', $key, str_replace('"', "'", $value));
                            }
                            $replacements[] = sprintf(
                                '    #[ORM\%s(%s)]',
                                $mapping['attributes']['joinColumns']['type'],
                                implode(', ', $keyValue)
                            );
                        }

                        if (isset($mapping['attributes']['inverseJoinColumns'])) {
                            $keyValue = [];
                            foreach ($mapping['attributes']['inverseJoinColumns']['attributes'] as $key => $value) {
                                $keyValue[] = sprintf('%s: %s', $key, str_replace('"', "'", $value));
                            }
                            $replacements[] = sprintf(
                                '    #[ORM\InverseJoinColumn(%s)]',
                                implode(', ', $keyValue)
                            );
                        }
                        break;
                }
            }

            $after = str_replace($property, implode(PHP_EOL, $replacements), $after);
        }
    }

    /**
     * Replace @Inject(dependencies) with #[Inject(dependencies)] in all classes
     */
    if (str_contains($after, '@Inject({')) {
        preg_match('#/\**\n*\s*\**\s*@Inject[\s\S\n]*?\*/#', $after, $injectTag);

        if (isset($injectTag[0])) {
            $injectTag = $injectTag[0];

            preg_match_all('/[^,\s{]+::class|".+?"/m', $injectTag, $oldDependencies);

            $newDependencies = [];
            if (count($oldDependencies[0]) > 0) {
                $newDependencies[] = '#[Inject(';
                foreach ($oldDependencies[0] as $dependency) {
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
