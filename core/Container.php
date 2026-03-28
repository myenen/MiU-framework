<?php

declare(strict_types=1);

namespace Core;

use Closure;
use Core\View\View;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/**
 * Singleton baglamalari ve reflection tabanli otomatik baglama kullanan hafif servis kapsayicisi.
 */
final class Container
{
    private array $bindings = [];
    private array $instances = [];

    /**
     * Verilen servis kimligi icin singleton fabrika kaydi yapar.
     *
     * @param string $id Servis kimligi ya da sinif adi.
     * @param Closure $factory Servis nesnesini donduren fabrika.
     */
    public function singleton(string $id, Closure $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    /**
     * Verilen servis kimliginin cozulup cozulmeyecegini kontrol eder.
     *
     * @param string $id Servis kimligi ya da sinif adi.
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->bindings)
            || class_exists($id);
    }

    /**
     * Kapsayicidan bir servisi cozer.
     *
     * @param string $id Servis kimligi ya da sinif adi.
     * @return mixed
     */
    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (array_key_exists($id, $this->bindings)) {
            return $this->instances[$id] = $this->bindings[$id]($this);
        }

        if (class_exists($id)) {
            $instance = $this->build($id);

            if ($this->shouldCacheAutoBuilt($id)) {
                $this->instances[$id] = $instance;
            }

            return $instance;
        }

        throw new RuntimeException("Container binding not found: {$id}");
    }

    /**
     * Kurucu bagimliliklarini yinelemeli cozumleyerek bir sinif nesnesi olusturur.
     *
     * @param string $class Tam nitelikli sinif adi.
     * @return object
     */
    private function build(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Class is not instantiable: {$class}");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getParameters() === []) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new RuntimeException(sprintf(
                    'Unable to resolve parameter $%s for %s',
                    $parameter->getName(),
                    $class
                ));
            }

            $dependencyClass = $type->getName();

            if ($dependencyClass === View::class) {
                $dependencies[] = $this->resolveViewForClass($class);
                continue;
            }

            $dependencies[] = $this->get($dependencyClass);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Controller namespace'ine gore dogru View servisini cozer.
     *
     * @param string $class Controller sinif adi.
     * @return View
     */
    private function resolveViewForClass(string $class): View
    {
        return match (true) {
            str_starts_with($class, 'App\\Controllers\\Admin\\') => $this->get('view.admin'),
            str_starts_with($class, 'App\\Controllers\\Api\\') => $this->get('view.site'),
            default => $this->get('view.site'),
        };
    }

    /**
     * Reflection ile uretilen sinifin kapsayicida cache'lenip cache'lenmeyecegini belirtir.
     *
     * @param string $class Tam nitelikli sinif adi.
     * @return bool
     */
    private function shouldCacheAutoBuilt(string $class): bool
    {
        return str_starts_with($class, 'App\\Controllers\\')
            || str_starts_with($class, 'App\\Services\\');
    }
}
