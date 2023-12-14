<?php

declare(strict_types=1);

namespace OCA\Testing\Listener;

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

		$event->registerSchema('testing', [
			'id' => 'test_declarative_form_event',
			'priority' => 10,
			'section_type' => 'admin',
			'section_id' => 'additional',
			'storage_type' => 'external',
			'title' => 'Test declarative settings event', // NcSettingsSection name
			'description' => 'This form is registered via the RegisterDeclarativeSettingsFormEvent', // NcSettingsSection description
			'fields' => [
				[
					'id' => 'event_field_1',
					'title' => 'Why is 42 this answer to all questions?',
					'description' => 'Hint: It\'s not',
					'type' => 'text',
				],
				[
					'id' => 'feature_rating',
					'title' => 'How would you rate this feature?',
					'description' => 'Your vote is not anonymous',
					'type' => 'radio', // radio, radio-button (NcCheckboxRadioSwitch button-variant)
					'label' => 'Select single toggle',
					'options' => [
						[
							'name' => 'Awesome', // NcCheckboxRadioSwitch display name
							'value' => '1' // NcCheckboxRadioSwitch value
						],
						[
							'name' => 'Very awesome',
							'value' => '2'
						],
						[
							'name' => 'Super awesome',
							'value' => '3'
						],
					],
				],
			],
		]);
	}
}
