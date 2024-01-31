<?php
declare(strict_types=1);

/*
 * @copyright 2024 Anna Larch <anna.larch@gmx.net>
 *
 * @author Anna Larch <anna.larch@gmx.net>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace OCA\DAV\DAV\Sharing;

class SharingService {
	private readonly string $resourceType;
	public function __construct(private SharingMapper $mapper) {}

	public function setResourceType(string $resourceType) {
		$this->resourceType = $resourceType;
	}

	public function getResourceType(): string {
		return $this->resourceType;
	}

	public function shareWith(IShareable $shareable, string $principal): void {
		// remove the share if it already exists
		$this->mapper->deleteShare($shareable->getResourceId(), $this->getResourceType(), $principal);
		$access = Backend::ACCESS_READ;
		if (isset($element['readOnly'])) {
			$access = $element['readOnly'] ? Backend::ACCESS_READ : Backend::ACCESS_READ_WRITE;
		}
		$this->mapper->share($shareable->getResourceId(), $this->getResourceType(), $access, $principal);
	}

	public function unshare(IShareable $shareable, string $principal): void {
		$this->mapper->unshare($shareable->getResourceId(), $this->getResourceType(), $principal);
	}

	public function deleteShare(IShareable $shareable, string $principal): void {
		$this->mapper->deleteShare($shareable->getResourceId(), $this->getResourceType(), $principal);
	}

	public function deleteAllShares(int $resourceId): void {
		$this->mapper->deleteAllShares($resourceId);
	}

	public function deleteAllSharesByUser(string $principaluri): void {
		$this->mapper->deleteAllSharesByUser($principaluri);
	}

	public function getShares(int $resourceId) {
		return $this->mapper->getSharesForId($resourceId, $this->getResourceType());
	}

	/**
	 * @param array $oldShares
	 * @return bool
	 */
	public function hasGroupShare(array $oldShares): bool {
		$group = false;
		foreach ($oldShares as $share) {
			if($share['{http://owncloud.org/ns}group-share'] === true) {
				$group = true;
				break;
			}
		}
		return $group;
	}

	public function getPrincipal(IShareable $shareable, $element): ?string {
		$parts = explode(':', $element, 2);
		if ($parts[0] !== 'principal') {
			return null;
		}

		// don't share with owner
		if ($shareable->getOwner() === $parts[1]) {
			return null;
		}
		return $parts[1];
	}

}

