<?php
/**
 * @copyright Copyright (c) 2023 Kate Döen <kate.doeen@nextcloud.com>
 *
 * @author Kate Döen <kate.doeen@nextcloud.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Settings;

use Exception;
use OC\AppFramework\Bootstrap\Coordinator;
use OC\AppFramework\Middleware\Security\Exceptions\NotAdminException;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\Server;
use OCP\Settings\DeclarativeSettingsTypes;
use OCP\Settings\GetDeclarativeSettingsValueEvent;
use OCP\Settings\IDeclarativeManager;
use OCP\Settings\IDeclarativeSettingsForm;
use OCP\Settings\RegisterDeclarativeSettingsFormEvent;
use OCP\Settings\SetDeclarativeSettingsValueEvent;

/**
 * @psalm-import-type DeclarativeSettingsValueTypes from IDeclarativeSettingsForm
 * @psalm-import-type DeclarativeSettingsStorageType from IDeclarativeSettingsForm
 * @psalm-import-type DeclarativeSettingsSectionType from IDeclarativeSettingsForm
 * @psalm-import-type DeclarativeSettingsFormSchemaWithValues from IDeclarativeSettingsForm
 * @psalm-import-type DeclarativeSettingsFormSchemaWithoutValues from IDeclarativeSettingsForm
 */
class DeclarativeManager implements IDeclarativeManager {
	public function __construct(
		private IEventDispatcher $eventDispatcher,
		private IGroupManager $groupManager,
		private Coordinator $coordinator,
		private IConfig $config,
	) {
	}

	/**
	 * @var array<string, list<DeclarativeSettingsFormSchemaWithoutValues>>
	 */
	private array $appSchemas = [];

	/**
	 * @inheritdoc
	 */
	public function registerSchema(string $app, array $schema): void {
		$this->appSchemas[$app] ??= [];


		foreach ($this->appSchemas[$app] as $otherSchema) {
			if ($otherSchema['id'] === $schema['id']) {
				throw new Exception('Duplicate form IDs detected: ' . $schema['id']);
			}
		}

		$fieldIDs = array_map(fn ($field) => $field['id'], $schema['fields']);
		$otherFieldIDs = array_merge(...array_map(fn ($schema) => array_map(fn ($field) => $field['id'], $schema['fields']), $this->appSchemas[$app]));
		$intersectionFieldIDs = array_intersect($fieldIDs, $otherFieldIDs);
		if (count($intersectionFieldIDs) > 0) {
			throw new Exception('Non unique field IDs detected: ' . join(', ', $intersectionFieldIDs));
		}

		$this->appSchemas[$app][] = $schema;
	}

	/**
	 * @inheritdoc
	 */
	public function loadSchemas(): void {
		$declarativeSettings = $this->coordinator->getRegistrationContext()->getDeclarativeSettings();
		foreach ($declarativeSettings as $declarativeSetting) {
			/** @var IDeclarativeSettingsForm $declarativeSettingObject */
			$declarativeSettingObject = Server::get($declarativeSetting->getService());
			$this->registerSchema($declarativeSetting->getAppId(), $declarativeSettingObject->getSchema());
		}

		$this->eventDispatcher->dispatchTyped(new RegisterDeclarativeSettingsFormEvent($this));
	}

	/**
	 * @inheritdoc
	 */
	public function getFormIDs(string $type, string $section): array {
		/** @var array<string, list<string>> $formIds */
		$formIds = [];

		foreach ($this->appSchemas as $app => $schemas) {
			$ids = [];
			foreach ($schemas as $schema) {
				if ($schema['section_type'] === $type && $schema['section_id'] === $section) {
					$ids[] = $schema['id'];
				}
			}

			if (!empty($ids)) {
				$formIds[$app] = array_merge($formIds[$app] ?? [], $ids);
			}
		}

		return $formIds;
	}

	/**
	 * @inheritdoc
	 * @throws Exception
	 */
	public function getFormsWithValues(IUser $user, string $type, string $section): array {
		$forms = [];

		foreach ($this->appSchemas as $app => $schemas) {
			foreach ($schemas as $schema) {
				if ($schema['section_type'] === $type && $schema['section_id'] === $section) {
					$s = $schema;
					$s['app'] = $app;

					foreach ($s['fields'] as &$field) {
						$field['value'] = $this->getValue($user, $app, $schema['id'], $field['id']);
					}

					/** @var DeclarativeSettingsFormSchemaWithValues $s */
					$forms[] = $s;
				}
			}
		}

		return $forms;
	}

	/**
	 * @return DeclarativeSettingsStorageType
	 */
	private function getStorageType(string $app, string $fieldId): string {
		if (array_key_exists($app, $this->appSchemas)) {
			foreach ($this->appSchemas[$app] as $schema) {
				foreach ($schema['fields'] as $field) {
					if ($field['id'] == $fieldId) {
						if (array_key_exists('storage_type', $field)) {
							return $field['storage_type'];
						}
					}
				}

				if (array_key_exists('storage_type', $schema)) {
					return $schema['storage_type'];
				}
			}
		}

		return DeclarativeSettingsTypes::STORAGE_TYPE_INTERNAL;
	}

	/**
	 * @return DeclarativeSettingsSectionType
	 * @throws Exception
	 */
	private function getSectionType(string $app, string $fieldId): string {
		if (array_key_exists($app, $this->appSchemas)) {
			foreach ($this->appSchemas[$app] as $schema) {
				foreach ($schema['fields'] as $field) {
					if ($field['id'] == $fieldId) {
						return $schema['section_type'];
					}
				}
			}
		}

		throw new Exception('Unknown fieldId "' . $fieldId . '"');
	}

	/**
	 * @psalm-param DeclarativeSettingsSectionType $sectionType
	 * @throws NotAdminException
	 */
	private function assertAuthorized(IUser $user, string $sectionType): void {
		if ($sectionType === 'admin' && !$this->groupManager->isAdmin($user->getUID())) {
			throw new NotAdminException('Logged in user does not have permission to access these settings.');
		}
	}

	/**
	 * @return DeclarativeSettingsValueTypes
	 * @throws Exception
	 * @throws NotAdminException
	 */
	private function getValue(IUser $user, string $app, string $formId, string $fieldId): mixed {
		$sectionType = $this->getSectionType($app, $fieldId);
		$this->assertAuthorized($user, $sectionType);

		$storageType = $this->getStorageType($app, $fieldId);
		switch ($storageType) {
			case DeclarativeSettingsTypes::STORAGE_TYPE_EXTERNAL:
				$event = new GetDeclarativeSettingsValueEvent($user, $app, $formId, $fieldId);
				$this->eventDispatcher->dispatchTyped($event);
				return $event->getValue();
			case DeclarativeSettingsTypes::STORAGE_TYPE_INTERNAL:
				return $this->getInternalValue($user, $app, $formId, $fieldId);
			default:
				throw new Exception('Unknown storage type "' . $storageType . '"');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function setValue(IUser $user, string $app, string $formId, string $fieldId, mixed $value): void {
		$sectionType = $this->getSectionType($app, $fieldId);
		$this->assertAuthorized($user, $sectionType);

		$storageType = $this->getStorageType($app, $fieldId);
		switch ($storageType) {
			case DeclarativeSettingsTypes::STORAGE_TYPE_EXTERNAL:
				$this->eventDispatcher->dispatchTyped(new SetDeclarativeSettingsValueEvent($user, $app, $formId, $fieldId, $value));
				break;
			case DeclarativeSettingsTypes::STORAGE_TYPE_INTERNAL:
				$this->saveInternalValue($user, $app, $formId, $fieldId, $value);
				break;
			default:
				throw new Exception('Unknown storage type "' . $storageType . '"');
		}
	}

	private function getInternalValue(IUser $user, string $app, string $formId, string $fieldId): mixed {
		$sectionType = $this->getSectionType($app, $fieldId);
		$defaultValue = $this->getDefaultValue($app, $formId, $fieldId);
		switch ($sectionType) {
			case DeclarativeSettingsTypes::SECTION_TYPE_ADMIN:
				return $this->config->getAppValue($app, $fieldId, $defaultValue);
			case DeclarativeSettingsTypes::SECTION_TYPE_PERSONAL:
				return $this->config->getUserValue($user->getUID(), $app, $fieldId, $defaultValue);
			default:
				throw new Exception('Unknown section type "' . $sectionType . '"');
		}
	}

	private function saveInternalValue(IUser $user, string $app, string $formId, string $fieldId, mixed $value): void {
		$sectionType = $this->getSectionType($app, $fieldId);
		switch ($sectionType) {
			case DeclarativeSettingsTypes::SECTION_TYPE_ADMIN:
				$this->config->setAppValue($app, $fieldId, $value);
				break;
			case DeclarativeSettingsTypes::SECTION_TYPE_PERSONAL:
				$this->config->setUserValue($user->getUID(), $app, $fieldId, $value);
				break;
			default:
				throw new Exception('Unknown section type "' . $sectionType . '"');
		}
	}

	private function getDefaultValue(string $app, string $formId, string $fieldId): mixed {
		foreach ($this->appSchemas[$app] as $schema) {
			if ($schema['id'] === $formId) {
				foreach ($schema['fields'] as $field) {
					if ($field['id'] === $fieldId) {
						if (isset($field['default'])) {
							if (is_array($field['default']) || is_numeric($field['default'])) {
								return json_encode($field['default']);
							}
							return $field['default'];
						}
					}
				}
			}
		}
		return null;
	}
}
