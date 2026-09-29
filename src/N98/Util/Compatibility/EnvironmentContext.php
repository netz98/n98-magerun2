<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility;

/**
 * A fully evaluated (product, edition, deployment context, app version, db family, db version)
 * tuple, as used for either the "current" (detected) or "target" (optionally overridden) evaluation.
 */
class EnvironmentContext
{
    public const CONTEXT_UNKNOWN = 'unknown';

    private string $product;

    private string $edition;

    private string $deploymentContext;

    private bool $deploymentContextIsCertain;

    private string $appVersion;

    private string $dbFamily;

    private string $dbVersion;

    public function __construct(
        string $product,
        string $edition,
        string $deploymentContext,
        bool $deploymentContextIsCertain,
        string $appVersion,
        string $dbFamily,
        string $dbVersion
    ) {
        $this->product = $product;
        $this->edition = $edition;
        $this->deploymentContext = $deploymentContext;
        $this->deploymentContextIsCertain = $deploymentContextIsCertain;
        $this->appVersion = $appVersion;
        $this->dbFamily = $dbFamily;
        $this->dbVersion = $dbVersion;
    }

    /**
     * Builds a target context from this (detected) context, applying only the overrides given.
     */
    public function withOverrides(?string $appVersion, ?string $dbFamily, ?string $dbVersion): self
    {
        return new self(
            $this->product,
            $this->edition,
            $this->deploymentContext,
            $this->deploymentContextIsCertain,
            $appVersion ?? $this->appVersion,
            $dbFamily !== null ? strtolower($dbFamily) : $this->dbFamily,
            $dbVersion ?? $this->dbVersion
        );
    }

    public function product(): string
    {
        return $this->product;
    }

    public function edition(): string
    {
        return $this->edition;
    }

    public function deploymentContext(): string
    {
        return $this->deploymentContext;
    }

    public function deploymentContextIsCertain(): bool
    {
        return $this->deploymentContextIsCertain;
    }

    public function appVersion(): string
    {
        return $this->appVersion;
    }

    public function dbFamily(): string
    {
        return $this->dbFamily;
    }

    public function dbVersion(): string
    {
        return $this->dbVersion;
    }

    public function toArray(): array
    {
        return [
            'product' => $this->product,
            'edition' => $this->edition,
            'context' => $this->deploymentContext,
            'contextCertain' => $this->deploymentContextIsCertain,
            'appVersion' => $this->appVersion,
            'dbFamily' => $this->dbFamily,
            'dbVersion' => $this->dbVersion,
        ];
    }
}
