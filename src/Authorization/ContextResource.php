<?php
namespace App\Authorization;

use Authorization\Policy\ResolverInterface;

class ContextResource {

	protected $_resource;
	protected $_context;

	public function __construct($resource, $context) {
		if (!is_array($context)) {
			throw new \InvalidArgumentException('Second parameter to ContextResource constructor must be an array.');
		}

		$this->_resource = $resource;
		$this->_context = $context;
	}

	public function getResolver(ResolverInterface $resolver) {
		return $resolver->getPolicy($this->_resource);
	}

	public function resource() {
		return $this->_resource;
	}

	public function has($name) {
		return array_key_exists($name, $this->_context) && $this->_context[$name] !== null;
	}

	public function context($name) {
		if (!$this->has($name)) {
			return null;
		}

		return $this->_context[$name];
	}

	/**
	 * Get data from the resource using object access.
	 *
	 * @param string $name Name of the context to get
	 * @return mixed
	 */
	public function __get($name) {
		if ($name == 'resource') {
			return $this->resource();
		}
		return $this->context($name);
	}

	/**
	 * Check whether data is available from the resource using object access.
	 * This is required in order for empty($resource->x) calls to work correctly.
	 *
	 * @param string $name Name of the context to get
	 * @return mixed
	 */
	public function __isset($name) {
		return isset($this->_context[$name]);
	}

	/**
	 * Set context data into the resource using object access.
	 *
	 * @param string $name Name of the context to set
	 * @param mixed $value The value to set
	 * @return void
	 */
	public function __set($name, $value) {
		$this->_context[$name] = $value;
	}

}
