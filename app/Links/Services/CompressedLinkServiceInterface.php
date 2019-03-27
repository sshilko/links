<?php

namespace App\Links\Services;

use App\Links\CompressedLinkInterface;
use App\Links\Exceptions\ErrorSavingLink;
use App\Links\Exceptions\InvalidCompressingLink;
use App\Links\Exceptions\LinkNotFound;
use App\Links\Exceptions\ValidationError;
use App\UserInterface;
use Illuminate\Support\Collection;

interface CompressedLinkServiceInterface
{

    public function getAll(): Collection;

    /**
     * @param int $id
     * @return CompressedLinkInterface
     * @throws LinkNotFound
     */
    public function get(int $id): CompressedLinkInterface;

    /**
     * @param array $attributes
     * @return CompressedLinkInterface
     * @throws ErrorSavingLink
     * @throws InvalidCompressingLink
     * @throws ValidationError
     */
    public function store(array $attributes): CompressedLinkInterface;

    /**
     * @param int $id
     * @param array $attributes
     * @return CompressedLinkInterface
     * @throws ErrorSavingLink
     * @throws LinkNotFound
     * @throws ValidationError
     */
    public function update(int $id, array $attributes): CompressedLinkInterface;

    /**
     * @param int $id
     * @return bool
     * @throws LinkNotFound
     */
    public function delete(int $id): bool;

    public function setUser(UserInterface $user) : CompressedLinkServiceInterface;

    public function assertValid(CompressedLinkInterface $compressedLink);

    /**
     * @param string $hash
     * @return string
     * @throws LinkNotFound
     */
    public function convertToFull(string $hash): string;

    /**
     * @param string $fullLink
     * @return string
     * @throws InvalidCompressingLink
     */
    public function buildCompressed(string $fullLink): string;

}
