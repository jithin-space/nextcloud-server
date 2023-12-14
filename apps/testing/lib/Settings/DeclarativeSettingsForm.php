<?php

declare(strict_types=1);

namespace OCA\Testing\Settings;

use OCP\Settings\IDeclarativeSettingsForm;

class DeclarativeSettingsForm implements IDeclarativeSettingsForm {
	public function getSchema(): array {
		return [
			'id' => 'test_declarative_form',
			'priority' => 10,
			'section_type' => 'admin',
			'section_id' => 'additional',
			'storage_type' => 'external',
			'title' => 'Test declarative settings class', // NcSettingsSection name
			'description' => 'This form is registered with a DeclarativeSettingsForm class', // NcSettingsSection description
			'fields' => [
				[
					'id' => 'field_1', // configkey
					'title' => 'Default text field', // label
					'description' => 'Set some simple text setting', // hint
					'type' => 'text', // text, password, email, tel, url, number
				],
				[
					'id' => 'field_1_1',
					'title' => 'Email field',
					'description' => 'Set email config',
					'type' => 'email',
				],
				[
					'id' => 'field_1_2',
					'title' => 'Tel field',
					'description' => 'Set tel config',
					'type' => 'tel',
				],
				[
					'id' => 'field_1_3',
					'title' => 'Url (website) field',
					'description' => 'Set url config',
					'type' => 'url',
				],
				[
					'id' => 'field_1_4',
					'title' => 'Number field',
					'description' => 'Set number config',
					'type' => 'number',
				],
				[
					'id' => 'field_2',
					'title' => 'Password',
					'description' => 'Set some secure value setting',
					'type' => 'password',
				],
				[
					'id' => 'field_3',
					'title' => 'Selection',
					'description' => 'Select some option setting',
					'type' => 'select', // select, radio, multi-select
					'options' => ['foo', 'bar', 'baz'],
				],
				[
					'id' => 'field_4',
					'title' => 'Toggle something',
					'description' => 'Select checkbox option setting',
					'type' => 'checkbox', // checkbox, multiple-checkbox
					'label' => 'Verify something if enabled'
				],
				[
					'id' => 'field_5',
					'title' => 'Multiple checkbox toggles, describing one setting, values are comma separated and concatenated to one string',
					'description' => 'Select checkbox option setting',
					'type' => 'multi-checkbox', // checkbox, multi-checkbox
					'label' => 'Select multiple toggles',
					'options' => [
						[
							'name' => 'Foo',
							'value' => 'foo',
						],
						[
							'name' => 'Bar',
							'value' => 'bar',
						],
						[
							'name' => 'Baz',
							'value' => 'baz',
						],
						[
							'name' => 'Qux',
							'value' => 'qux',
						],
					],
				],
				[
					'id' => 'field_6',
					'title' => 'Radio toggles, describing one setting like single select',
					'description' => 'Select radio option setting',
					'type' => 'radio', // radio, radio-button (NcCheckboxRadioSwitch button-variant)
					'label' => 'Select single toggle',
					'options' => [
						[
							'name' => 'First radio', // NcCheckboxRadioSwitch display name
							'value' => 'foo' // NcCheckboxRadioSwitch value
						],
						[
							'name' => 'Second radio',
							'value' => 'bar'
						],
						[
							'name' => 'Second radio',
							'value' => 'baz'
						],
					],
				],
				[
					'id' => 'some_real_setting',
					'title' => 'Choose init status check background job interval',
					'description' => 'How often AppAPI should check for initialization status',
					'type' => 'radio', // radio, radio-button (NcCheckboxRadioSwitch button-variant)
					'options' => [
						[
							'name' => 'Each 40 minutes', // NcCheckboxRadioSwitch display name
							'value' => '40m' // NcCheckboxRadioSwitch value
						],
						[
							'name' => 'Each 60 minutes',
							'value' => '60m'
						],
						[
							'name' => 'Each 120 minutes',
							'value' => '120m'
						],
						[
							'name' => 'Each day',
							'value' => 60 * 24 . 'm'
						],
					],
				],
			],
		];
	}
}
