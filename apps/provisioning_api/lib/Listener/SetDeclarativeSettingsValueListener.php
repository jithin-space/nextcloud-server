<?php

declare(strict_types=1);

namespace OCA\Provisioning_API\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Settings\SetDeclarativeSettingsValueEvent;

/**
 * @template-implements IEventListener<SetDeclarativeSettingsValueEvent>
 */
class SetDeclarativeSettingsValueListener implements IEventListener {

	public function __construct() {
	}

	public function handle(Event $event): void {
		if (!($event instanceof SetDeclarativeSettingsValueEvent) || $event->getApp() !== "provisioning_api") {
			// Unrelated
			return;
		}

		error_log($event->getValue());
	}
}
