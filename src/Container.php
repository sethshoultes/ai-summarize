<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize;

/**
 * Simple dependency injection container.
 */
class Container {
	/**
	 * @var array<string, callable|object>
	 */
	private array $services = [];

	/**
	 * @var array<string, mixed>
	 */
	private array $parameters = [];

	/**
	 * Register a service.
	 *
	 * @param string   $id       Service identifier.
	 * @param callable $factory Service factory callable.
	 */
	public function addService( string $id, callable $factory ): void {
		$this->services[ $id ] = $factory;
	}

	/**
	 * Register a parameter.
	 *
	 * @param string $id    Parameter identifier.
	 * @param mixed  $value Parameter value.
	 */
	public function addParameter( string $id, $value ): void {
		$this->parameters[ $id ] = $value;
	}

	/**
	 * Get a service or parameter.
	 *
	 * @param string $id Service or parameter identifier.
	 * @return mixed
	 */
	public function get( string $id ) {
		if ( isset( $this->parameters[ $id ] ) ) {
			return $this->parameters[ $id ];
		}

		if ( isset( $this->services[ $id ] ) ) {
			if ( is_callable( $this->services[ $id ] ) ) {
				$this->services[ $id ] = call_user_func( $this->services[ $id ] );
			}
			return $this->services[ $id ];
		}

		throw new \Exception( "Service or parameter '{$id}' not found" );
	}

	/**
	 * Check if a service or parameter exists.
	 *
	 * @param string $id Service or parameter identifier.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->services[ $id ] ) || isset( $this->parameters[ $id ] );
	}
}
