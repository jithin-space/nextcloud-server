<?php

declare(strict_types=1);

namespace OCA\Provisioning_API\Listener;

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

		$event->registerSchema('provisioning_api', [
			'id' => 'test_form2',
			'priority' => 0,
			'title' => 'Test form',
			'section_type' => 'personal',
			'section_id' => 'test_section',
			'storage_type' => 'external',
			'fields' => [
				[
					'id' => 'test_field2',
					'title' => 'Test',
					'type' => 'string',
				],
			],
		]);
	}
}
