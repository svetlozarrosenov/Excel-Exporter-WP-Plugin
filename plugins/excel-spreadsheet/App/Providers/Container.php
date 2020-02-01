<?php
namespace App\Providers;

class Container {
	protected $containers = [];

	public function __construct( array $containers ) {
        $this->containers = $containers;
    }

	public function instantiate( string $key ) {
        if ( !array_key_exists($key, $this->containers ) ) {
            throw new \Exception('No container found');
        }

        return $this->containers[ $key ]();
    }

	public function instantiateAnObjectThroughtContainer( string $className ) {
        try {
            return $this->instantiate( $className );
        } catch ( \Exception $e ) {
            $reflectionClass = new \ReflectionClass( $className );
            $constructorMethod = $reflectionClass->getConstructor();
            $parameters = $constructorMethod->getParameters();
            $arguments = [];
            foreach ( $parameters as $parameter ) {
                $type = $parameter->getType();
                $typeName = $type->__toString();
                $arguments[] = $this->instantiate( $typeName );
            }
            return new $className( ...$arguments );
        }
    }
}

