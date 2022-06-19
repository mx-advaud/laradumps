<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Support\IdeHandle;

abstract class Payload
{
    /**
     * @var string
     */
    private $notificationId;

    /**
     * @var mixed[]
     */
    private $backtrace = [];

    /**
     * @var mixed[]
     */
    protected $typesWithTrace = [
        'table',
        'validate',
        'query',
        'queries',
        'dump',
        'events',
        'diff',
        'model',
    ];

    abstract public function type(): string;

    public function trace(array $backtrace): void
    {
        $this->backtrace = $backtrace;
    }

    public function notificationId(string $notificationId): void
    {
        $this->notificationId = $notificationId;
    }

    public function content(): array
    {
        return [];
    }

    public function ideHandle(): array
    {
        $trace = new IdeHandle($this->backtrace);

        return $trace->ideHandle();
    }

    public function customHandle(): array
    {
        return [];
    }

    public function toArray(): array
    {
        $ideHandle = $this->customHandle();
        if (in_array($this->type(), $this->typesWithTrace)) {
            $ideHandle = $this->ideHandle();
        }

        return [
            'id'   => $this->notificationId,
            'type' => $this->type(),
            'meta' => [
                'laradumps_version' => $this->getInstalledVersion(),
            ],
            'content'   => $this->content(),
            'ideHandle' => $ideHandle,
        ];
    }

    public function getInstalledVersion(): ?string
    {
        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                return \Composer\InstalledVersions::getVersion('laradumps/laradumps');
            } catch (\Exception $exception) {
                return '0.0.0';
            }
        }

        return '0.0.0';
    }
}
