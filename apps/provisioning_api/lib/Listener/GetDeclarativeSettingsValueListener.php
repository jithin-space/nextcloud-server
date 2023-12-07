<?php

declare(strict_types=1);

namespace OCA\Provisioning_API\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Settings\GetDeclarativeSettingsValueEvent;

/**
 * @template-implements IEventListener<GetDeclarativeSettingsValueEvent>
 */
class GetDeclarativeSettingsValueListener implements IEventListener {

	public function __construct() {
	}

	public function handle(Event $event): void {
		if (!($event instanceof GetDeclarativeSettingsValueEvent)) {
			// Unrelated
			return;
		}

		$event->setValue("TODO");
	}
}
