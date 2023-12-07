<?php

declare(strict_types=1);

namespace OCA\Provisioning_API\Listener;

use OCA\Provisioning_API\DeclarativeSettingsForm;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Settings\RegisterDeclarativeSettingsFormEvent;

/**
 * @template-implements IEventListener<RegisterDeclarativeSettingsFormEvent>
 */
class RegisterDeclarativeSettingsListener implements IEventListener {

	public function __construct() {
	}

	public function handle(Event $event): void {
		if (!($event instanceof RegisterDeclarativeSettingsFormEvent)) {
			// Unrelated
			return;
		}

		$event->registerSchema('provisioning_api', (new DeclarativeSettingsForm())->getSchema());
	}
}
