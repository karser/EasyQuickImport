<?php declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\SheetScheduler\TransformerResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddEntityTransformers implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->attachHandlers($container, TransformerResolver::class, 'entity.transformer');
    }

    private function attachHandlers(ContainerBuilder $container, string $service_name, string $tag): void
    {
        if (!$container->has($service_name)) {
            return;
        }
        $manager = $container->findDefinition($service_name);
        foreach ($container->findTaggedServiceIds($tag) as $id => $attr) {
            $manager->addMethodCall('addEntityTransformer', array(new Reference($id)));
        }
    }
}
