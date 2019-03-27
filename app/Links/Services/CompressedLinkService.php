<?php

namespace App\Links\Services;

use App\Links\Exceptions\ErrorGeneratingHash;
use App\Links\Exceptions\ErrorSavingModel;
use App\Links\Exceptions\InvalidCompressingLink;
use App\Links\Exceptions\ValidationError;
use App\Links\Exceptions\WrongFactoryAttributes;
use App\Links\Generators\LinkHashGenerator;
use App\UserInterface;
use Illuminate\Support\Facades\Validator;
use App\Links\CompressedLinkInterface;
use App\Links\Exceptions\LinkNotFound;
use App\Links\Factories\CompressedLinkFactoryInterface;
use App\Links\Repositories\CompressedLinkRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class CompressedLinkService implements CompressedLinkServiceInterface
{

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var CompressedLinkRepositoryInterface
     */
    private $repository;

    /**
     * @var CompressedLinkFactoryInterface
     */
    private $modelFactory;

    /**
     * @var LinkHashGenerator
     */
    private $hashGenerator;

    public function __construct(
        CompressedLinkRepositoryInterface $repository,
        CompressedLinkFactoryInterface $modelFactory,
        LinkHashGenerator $hashGenerator
    )
    {
        $this->repository = $repository;
        $this->modelFactory = $modelFactory;
        $this->hashGenerator = $hashGenerator;
    }

    public function getAll(): Collection
    {
        return $this->repository->all();

    }

    /**
     * @param int $id
     * @return CompressedLinkInterface
     * @throws LinkNotFound
     */
    public function get(int $id): CompressedLinkInterface
    {
        try {
            $result = $this->repository->find($id);
        } catch (ModelNotFoundException $e) {
            throw new LinkNotFound();
        }

        if (!$result) {
            throw new LinkNotFound();
        }

        return $result;
    }

    /**
     * @param array $attributes
     * @return CompressedLinkInterface
     * @throws ValidationError
     */
    public function store(array $attributes): CompressedLinkInterface
    {
        /** @var CompressedLinkInterface $compressedLink */
        $compressedLink = $this->modelFactory->make($attributes);
        $this->assertValid($compressedLink);
        $compressedLink->user()->associate($this->user);
        return $this->repository->save($compressedLink);
    }

    /**
     * @param int $id
     * @param array $attributes
     * @return CompressedLinkInterface
     * @throws LinkNotFound
     * @throws ValidationError
     */
    public function update(int $id, array $attributes): CompressedLinkInterface
    {
        $compressedLink = $this->repository->find($id);
        if (!$compressedLink) {
            throw new LinkNotFound();
        }
        $compressedLink->fill($attributes);
        $this->assertValid($compressedLink);
        return $this->repository->save($compressedLink);
    }

    /**
     * @param int $id
     * @return bool
     * @throws LinkNotFound
     */
    public function delete(int $id): bool
    {
        try {
            $result = $this->repository->delete($id);
        } catch (ModelNotFoundException $e) {
            throw new LinkNotFound();
        }

        if (!$result) {
            throw new LinkNotFound();
        }

        return true;
    }

    /**
     * @param CompressedLinkInterface $compressedLink
     * @throws ValidationError
     */
    public function assertValid(CompressedLinkInterface $compressedLink)
    {
        $validator = Validator::make($compressedLink->getAttributes(), $compressedLink->getRules());
        if (!$validator->passes()) {
            throw new ValidationError((string)$validator->getMessageBag());
        }
    }

    public function setUser(UserInterface $user): CompressedLinkServiceInterface
    {
        $this->user = $user;
        $this->repository->setUser($user);
        return $this;
    }

    /**
     * @param string $hash
     * @return string
     * @throws LinkNotFound
     */
    public function convertToFull(string $hash): string
    {
        try {
            $id = $this->hashGenerator->getNumberByHash($hash);
            $linkModel = $this->repository->find($id);
        } catch (ModelNotFoundException|ErrorGeneratingHash $e) {
            throw new LinkNotFound();
        }

        if (!$linkModel) {
            throw new LinkNotFound();
        }

        return $linkModel->link;
    }

    /**
     * @param string $fullLink
     * @return string
     * @throws InvalidCompressingLink
     */
    public function buildCompressed(string $fullLink): string
    {
        try {
            $compressedLink = $this->modelFactory->make(['link' => $fullLink]);
            $compressedLink->user()->associate($this->user);
            $this->assertValid($compressedLink);
            $this->repository->save($compressedLink);
        } catch (WrongFactoryAttributes|ErrorSavingModel $e) {
            throw new InvalidCompressingLink();
        }

        $hash = $this->hashGenerator->getHashByNumber($compressedLink->getKey());

        return $this->buildCompressedUrl($hash);

    }

    private function buildCompressedUrl(string $hash): string
    {
        return config('compressor.domain') . '/' . $hash;
    }

}
