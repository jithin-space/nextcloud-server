<?php

/**
 * @copyright Copyright (c) 2024 Kate Döen <kate.doeen@nextcloud.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCP\AppFramework\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route {

	public const TYPE_OCS = 'ocs';

	public const TYPE_INDEX = 'routes';

	/**
	 * @param string $type Either Route::TYPE_OCS or Route::TYPE_INDEX.
	 * @psalm-param Route::TYPE_* $type
	 * @param string $verb HTTP method of the route.
	 * @psalm-param 'GET'|'HEAD'|'POST'|'PUT'|'DELETE'|'OPTIONS'|'PATCH' $verb
	 * @param string $url The path of the route.
	 * @param ?array<string, string> $requirements Array of regexes mapped to the path parameters.
	 * @param ?array<string, mixed> $defaults Array of default values mapped to the path parameters.
	 * @param ?string $root Custom root. For OCS all apps are allowed, but for index.php only some can use it.
	 * @param ?string $postfix Postfix for the route name.
	 */
	public function __construct(
		protected string $type,
		protected string $verb,
		protected string $url,
		protected ?array $requirements = null,
		protected ?array $defaults = null,
		protected ?string $root = null,
		protected ?string $postfix = null,
	) {
	}

	/**
	 * @return array{
	 *     verb: string,
	 *     url: string,
	 *     requirements?: array<string, string>,
	 *     defaults?: array<string, mixed>,
	 *     root?: string,
	 *     postfix?: string,
	 * }
	 */
	public function toArray() {
		$route = [
			'verb' => $this->verb,
			'url' => $this->url,
		];

		if ($this->requirements !== null) {
			$route['requirements'] = $this->requirements;
		}
		if ($this->defaults !== null) {
			$route['defaults'] = $this->defaults;
		}
		if ($this->root !== null) {
			$route['root'] = $this->root;
		}
		if ($this->postfix !== null) {
			$route['postfix'] = $this->postfix;
		}

		return $route;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getVerb(): ?string {
		return $this->verb;
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getRequirements(): ?array {
		return $this->requirements;
	}

	public function getDefaults(): ?array {
		return $this->defaults;
	}

	public function getRoot(): ?string {
		return $this->root;
	}

	public function getPostfix(): ?string {
		return $this->postfix;
	}
}
