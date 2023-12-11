<?php

namespace OCA\Provisioning_API;

use OCP\Settings\IDeclarativeSettingsForm;

class DeclarativeSettingsForm implements IDeclarativeSettingsForm {
	public function getSchema(): array {
		return [
			'id' => 'test_form',
			'priority' => 0,
			'title' => 'Test form',
			'section_type' => 'personal',
			'section_id' => 'test_section',
			'storage_type' => 'external',
			'fields' => [
				[
					'id' => 'test_field',
					'title' => 'Test',
					'type' => 'string',
				],
			],
		];
	}
}
