<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Exception\NotFoundException;

class TransformerResolver
{
    /** @var EntityTransformerInterface[] */
    private $entityTransformers = [];

    public function addEntityTransformer(EntityTransformerInterface $entityTransformer): void
    {
        $this->entityTransformers[] = $entityTransformer;
    }

    public function resolve(string $class): EntityTransformerInterface
    {
        foreach ($this->entityTransformers as $entityTransformer) {
            if ($entityTransformer->supports($class)) {
                return $entityTransformer;
            }
        }
        throw new NotFoundException("Unable to resolve data transformer for class {$class}");
    }
}
