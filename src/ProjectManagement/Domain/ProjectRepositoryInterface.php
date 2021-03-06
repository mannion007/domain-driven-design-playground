<?php

namespace Mannion007\BestInvestments\ProjectManagement\Domain;

interface ProjectRepositoryInterface
{
    public function getByReference(ProjectReference $reference): Project;
    public function getBelongingTo(ClientId $clientId): array;
    public function save(Project $project): void;
}
