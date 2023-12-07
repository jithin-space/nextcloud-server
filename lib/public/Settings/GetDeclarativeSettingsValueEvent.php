<?php

namespace OCP\Settings;

use Exception;
use OCP\EventDispatcher\Event;
use OCP\IUser;

/**
 * @psalm-import-type DeclarativeSettingsValueTypes from IDeclarativeSettingsForm
 */
class GetDeclarativeSettingsValueEvent extends Event {
	/**
	 * @var ?DeclarativeSettingsValueTypes
	 */
	private mixed $value = null;

	/**
	 * @since 29.0.0
	 */
	public function __construct(
		private IUser $user,
		private string $app,
		private string $fieldId,
	) {
		parent::__construct();
	}

	public function getUser(): IUser {
		return $this->user;
	}

	public function getApp(): string {
		return $this->app;
	}

	public function getFieldId(): string {
		return $this->fieldId;
	}

	/**
	 * @param DeclarativeSettingsValueTypes $value
	 * @throws Exception
	 */
	public function setValue(mixed $value): void {
		if ($this->value !== null) {
			throw new Exception('Value already set');
		}

		$this->value = $value;
	}

	/**
	 * @return DeclarativeSettingsValueTypes
	 * @throws Exception
	 */
	public function getValue(): mixed {
		if ($this->value === null) {
			throw new Exception('Value not set');
		}

		return $this->value;
	}
}
