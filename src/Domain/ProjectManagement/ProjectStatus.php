<?php

namespace Mannion007\BestInvestments\Domain\ProjectManagement;

class ProjectStatus
{
    const DRAFT = 'draft';
    const ACTIVE = 'active';
    const ON_HOLD = 'on_hold';
    const CLOSED = 'closed';

    private $status;

    private function __construct($status)
    {
        $this->status = $status;
    }

    public static function active(): ProjectStatus
    {
        return new self(self::ACTIVE);
    }

    public static function draft(): ProjectStatus
    {
        return new self(self::DRAFT);
    }

    public static function onHold(): ProjectStatus
    {
        return new self(self::ON_HOLD);
    }

    public static function closed(): ProjectStatus
    {
        return new self(self::CLOSED);
    }

    public function is($status): bool
    {
        return $status === $this->status;
    }

    public function isNot($status): bool
    {
        return !$this->is($status);
    }
}