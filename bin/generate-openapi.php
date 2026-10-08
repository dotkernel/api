<?php

/**
 * Generates the OpenAPI document from the OA attributes under src/, to the path named by
 * `openapi.output_file`.
 *
 * The document root — info, servers, external docs, security schemes — is not declared with
 * attributes. PHP attribute arguments must be constant expressions, so they cannot read the API
 * base URL out of the local autoload config; the root is assembled here from the `openapi` and
 * `application` config keys instead and injected after the scan.
 *
 * `openapi.exclude_tags` keeps a module's endpoints out of the published document entirely.
 *
 * `openapi.servers` lists servers published after the project's own URL; see Api\App\Helper\OpenApiServers.
 */

declare(strict_types=1);

use Api\App\Helper\OpenApiServers;
use OpenApi\Attributes as OA;
use OpenApi\Builder;
use OpenApi\Generator;
use OpenApi\Undefined;
use OpenApi\Utils\SourceFinder;
use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

const SOURCE_PATHS = ['src'];

/** @var ContainerInterface $container */
$container = require 'config/container.php';

/** @var array<string, mixed> $config */
$config = $container->get('config');

$baseUrl = $config['application']['url'] ?? null;
if (! is_string($baseUrl) || $baseUrl === '') {
    fwrite(STDERR, 'Missing the `application.url` config key; cannot set the OpenAPI server URL.' . PHP_EOL);
    exit(1);
}

/** @var array<string, mixed> $documentConfig */
$documentConfig = $config['openapi'] ?? [];

$outputFile = $documentConfig['output_file'] ?? null;
if (! is_string($outputFile) || $outputFile === '') {
    fwrite(STDERR, 'Missing the `openapi.output_file` config key; nowhere to write the document.' . PHP_EOL);
    exit(1);
}

// Relative paths are resolved against the project root, which this script has already chdir'd to.
$outputDirectory = dirname($outputFile);
if (! is_dir($outputDirectory)) {
    fwrite(STDERR, sprintf('The output directory "%s" does not exist.', $outputDirectory) . PHP_EOL);
    exit(1);
}

/**
 * Tags whose operations are kept out of the published document — see `openapi.exclude_tags`.
 *
 * @var list<non-empty-string> $excludedTags
 */
$excludedTags = array_values($documentConfig['exclude_tags'] ?? []);

$builder = (new Builder())->addSource(new SourceFinder(SOURCE_PATHS));

if (isset($documentConfig['openapi_version'])) {
    $builder->setVersion($documentConfig['openapi_version']);
}

if ($excludedTags !== []) {
    // swagger-php's PathFilter is an allowlist of tag patterns, so an exclusion is expressed as a
    // negative lookahead over the names to drop. Removing those operations orphans the schemas only
    // they referenced, which is what CleanUnusedComponents — off by default — then sweeps up.
    //
    // PathFilter drops whole path items rather than single operations: a path carrying both an
    // excluded and a published tag would keep both. Every path in this document has exactly one tag,
    // so that does not arise today, but it is worth re-checking if that stops being true.
    $keepPattern = sprintf(
        '/^(?!(?:%s)$)/',
        implode('|', array_map(
            static fn (string $tag): string => preg_quote($tag, '/'),
            $excludedTags,
        )),
    );

    $builder->withGenerator(static function (Generator $generator) use ($keepPattern): void {
        $generator->setConfig([
            'pathFilter'            => ['tags' => [$keepPattern]],
            'cleanUnusedComponents' => ['enabled' => true],
        ]);
    });
}

$result  = $builder->build();
$openApi = $result->openApi();

if ($openApi === null) {
    fwrite(STDERR, 'The scan of ' . implode(', ', SOURCE_PATHS) . ' produced no OpenAPI document.' . PHP_EOL);
    exit(1);
}

/**
 * `info.x-generated`: when this document was built.
 *
 * OpenAPI has no field for build time, so it goes in as a specification extension on the info
 * object — the one part of the document that describes the document rather than the API. Absent or
 * empty config omits it, which is also how to keep the output reproducible.
 */
$generatedTimezone = $documentConfig['generated_timezone'] ?? null;
$infoExtensions    = null;

if (is_string($generatedTimezone) && $generatedTimezone !== '') {
    try {
        $generatedAt = new DateTimeImmutable('now', new DateTimeZone($generatedTimezone));
    } catch (DateInvalidTimeZoneException) {
        fwrite(
            STDERR,
            sprintf('Unknown `openapi.generated_timezone` value "%s".', $generatedTimezone) . PHP_EOL,
        );
        exit(1);
    }

    // ATOM carries the offset, so the timestamp stays unambiguous wherever it is read.
    $infoExtensions = ['generated' => $generatedAt->format(DateTimeInterface::ATOM)];
}

$openApi->info = new OA\Info(
    version: $documentConfig['info']['version'] ?? null,
    title: $documentConfig['info']['title'] ?? null,
    x: $infoExtensions,
);

$serverDescription = $documentConfig['server_description'] ?? null;
if ($serverDescription !== null && ! is_string($serverDescription)) {
    fwrite(STDERR, 'The `openapi.server_description` config key must be a string.' . PHP_EOL);
    exit(1);
}

// The project's own URL first, then `openapi.servers` — each URL once.
try {
    $servers = OpenApiServers::fromConfig($baseUrl, $serverDescription, $documentConfig['servers'] ?? []);
} catch (InvalidArgumentException $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}

$openApi->servers = array_map(
    static fn (array $server): OA\Server => new OA\Server(
        url: $server['url'],
        description: $server['description'] ?? Undefined::UNDEFINED,
    ),
    $servers,
);

if (isset($documentConfig['external_docs']['url'])) {
    $openApi->externalDocs = new OA\ExternalDocumentation(
        description: $documentConfig['external_docs']['description'] ?? Undefined::UNDEFINED,
        url: $documentConfig['external_docs']['url'],
    );
}

$tags = [];
foreach ($documentConfig['tags'] ?? [] as $name => $description) {
    // An excluded tag has no operations left to describe, so declaring it would advertise an empty
    // section of the API.
    if (in_array((string) $name, $excludedTags, true)) {
        continue;
    }

    $tags[] = new OA\Tag(name: (string) $name, description: $description);
}

if ($tags !== []) {
    $openApi->tags = $tags;
}

$securitySchemes = [];
foreach ($documentConfig['security_schemes'] ?? [] as $name => $scheme) {
    $securitySchemes[] = new OA\SecurityScheme(
        securityScheme: (string) $name,
        type: $scheme['type'] ?? null,
        name: $scheme['name'] ?? null,
        in: $scheme['in'] ?? null,
        bearerFormat: $scheme['bearer_format'] ?? null,
        scheme: $scheme['scheme'] ?? null,
    );
}

if ($securitySchemes !== []) {
    $openApi->components->securitySchemes = $securitySchemes;
}

$result->saveAs($outputFile);

// The scan validates before the root is injected below, so a missing OA\Info is expected here.
$expectedWarning = 'Required @OA\\Info() not found';

foreach ($result->warnings() as $warning) {
    if (str_contains($warning, $expectedWarning)) {
        continue;
    }

    fwrite(STDERR, 'warning: ' . $warning . PHP_EOL);
}

$errors = $result->errors();
foreach ($errors as $error) {
    fwrite(STDERR, 'error: ' . $error . PHP_EOL);
}

echo sprintf('Wrote %s (%d paths).' . PHP_EOL, $outputFile, count((array) $openApi->paths));

exit($errors === [] ? 0 : 1);
