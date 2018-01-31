<?php

namespace App\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class HashingVersionStrategy implements VersionStrategyInterface {
    public function getVersion($path): string {
        return \substr(hash_file('sha256', $path), 0, 16);
    }

    public function applyVersion($path): string {
        $version = $this->getVersion($path);

        if (!$version) {
            return $path;
        }

        return \sprintf('%s?%s', $path, $this->getVersion($path));
    }
}
