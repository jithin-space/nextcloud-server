<?php

namespace OCP\Settings;

use OCP\EventDispatcher\Event;
use OCP\IUser;

/**
 * @psalm-import-type DeclarativeSettingsValueTypes from IDeclarativeSettingsForm
 */
class SetDeclarativeSettingsValueEvent extends Event {
	/**
	 * @param DeclarativeSettingsValueTypes $value
	 * @since 29.0.0
	 */
	public function __construct(
		private IUser $user,
		private string $app,
		private string $fieldId,
		private mixed $value,
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

	public function getValue(): mixed {
		return $this->value;
	}
}
