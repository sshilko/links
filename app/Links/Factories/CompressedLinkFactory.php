<?php

namespace App\Links\Factories;

use App\Links\CompressedLink;
use App\Links\CompressedLinkInterface;
use App\Links\Exceptions\WrongFactoryAttributes;

/**
 * Class CompressedLinkFactory
 * @package App\Links
 */
class CompressedLinkFactory implements CompressedLinkFactoryInterface
{
    /**
     * @param array $attributes
     * @return CompressedLinkInterface
     * @throws WrongFactoryAttributes
     */
    public function make(array $attributes): CompressedLinkInterface
    {
        if (!$attributes['link']) {
            throw new WrongFactoryAttributes();
        }

        $compressedLink = new CompressedLink();
        $compressedLink->fill($attributes);

        return $compressedLink;
    }
}
