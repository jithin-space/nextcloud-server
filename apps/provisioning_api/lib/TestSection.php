<?php

namespace OCA\Provisioning_API;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class TestSection implements IIconSection {
	public function __construct(
		protected string $appName,
		protected IURLGenerator $urlGenerator,
		protected IL10N $l,
	) {
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath($this->appName, 'app.svg');
	}

	public function getID(): string {
		return 'test_section';
	}

	public function getName(): string {
		return 'Test section';
	}

	public function getPriority(): int {
		return 1;
	}
}
