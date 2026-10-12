<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\OpenApi;

use OpenApi\Annotations\Operation;
use OpenApi\Undefined;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;

use function array_filter;
use function basename;
use function class_exists;
use function count;
use function dirname;
use function glob;
use function implode;
use function is_string;
use function sprintf;
use function strtoupper;

/**
 * Every documented operation must carry an explicit `operationId`, and no two may share one.
 *
 * The attributes are read through reflection, before swagger-php processes them. In the generated document
 * the key is always present, because swagger-php's OperationId processor fills in a hash for any operation
 * without one, so only the source attributes show whether an ID was written.
 */
final class OperationIdTest extends TestCase
{
    public function testTheTestSeesTheWholeSpecification(): void
    {
        $this->assertNotEmpty($this->openApiClasses(), 'No OpenAPI class was found under src/*/src/OpenAPI.php.');
        $this->assertNotEmpty($this->operations(), 'No OpenAPI operation was found in the OpenAPI classes.');
    }

    public function testEveryOperationHasAnOperationId(): void
    {
        $missing = [];
        foreach ($this->operations() as $operation) {
            if (! $this->isSet($operation['operationId'])) {
                $missing[] = $operation['label'];
            }
        }

        $this->assertSame(
            [],
            $missing,
            sprintf("These operations have no operationId:\n%s", implode("\n", $missing)),
        );
    }

    public function testOperationIdsAreUniqueAcrossTheProject(): void
    {
        $labelsById = [];
        foreach ($this->operations() as $operation) {
            if ($this->isSet($operation['operationId'])) {
                $labelsById[$operation['operationId']][] = $operation['label'];
            }
        }

        $duplicates = [];
        foreach (array_filter($labelsById, static fn (array $labels): bool => count($labels) > 1) as $id => $labels) {
            $duplicates[] = sprintf('%s is used by %s', $id, implode(', ', $labels));
        }

        $this->assertSame(
            [],
            $duplicates,
            sprintf("These operationIds are not unique:\n%s", implode("\n", $duplicates)),
        );
    }

    /**
     * @phpstan-assert-if-true non-empty-string $operationId
     */
    private function isSet(mixed $operationId): bool
    {
        return is_string($operationId) && $operationId !== '' && ! Undefined::isDefault($operationId);
    }

    /**
     * Finds the module OpenAPI classes: src/<Module>/src/OpenAPI.php is Api\<Module>\OpenAPI.
     *
     * @return list<class-string>
     */
    private function openApiClasses(): array
    {
        $classes = [];
        foreach (glob(dirname(__DIR__, 4) . '/src/*/src/OpenAPI.php') ?: [] as $file) {
            $class = sprintf('Api\\%s\\OpenAPI', basename(dirname($file, 2)));
            $this->assertTrue(class_exists($class), sprintf('%s was found at %s but cannot be loaded.', $class, $file));
            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * @return list<array{label: string, operationId: mixed}>
     */
    private function operations(): array
    {
        $operations = [];
        foreach ($this->openApiClasses() as $class) {
            $attributes = (new ReflectionClass($class))
                ->getAttributes(Operation::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                /** @var Operation $operation */
                $operation = $attribute->newInstance();

                $operations[] = [
                    'label'       => strtoupper((string) $operation->method) . ' ' . $operation->path,
                    'operationId' => $operation->operationId,
                ];
            }
        }

        return $operations;
    }
}
