<?php
/**
 *
 * This file is part of phpFastCache.
 *
 * @license MIT License (MIT)
 *
 * For full copyright and license information, please see the docs/CREDITS.txt file.
 *
 * @author Khoa Bui (khoaofgod)  <khoaofgod@gmail.com> https://www.phpfastcache.com
 * @author Georges.L (Geolim4)  <contact@geolim4.com>
 *
 */
declare(strict_types=1);

namespace Phpfastcache\Drivers\Files;

use Phpfastcache\Core\Pool\{
    DriverBaseTrait, ExtendedCacheItemPoolInterface, IO\IOHelperTrait
};
use Phpfastcache\Exceptions\{
    PhpfastcacheInvalidArgumentException
};
use Phpfastcache\Util\Directory;
use Psr\Cache\CacheItemInterface;

/**
 * Class Driver
 * @package phpFastCache\Drivers
 * @property Config $config Config object
 * @method Config getConfig() Return the config object
 */
class Driver implements ExtendedCacheItemPoolInterface
{
    use IOHelperTrait;
    use DriverBaseTrait {
        DriverBaseTrait::__construct as private __parentConstruct;
    }
    /**
     *
     */
    const FILE_DIR = 'files';

    /**
     * Driver constructor.
     * @param Config $config
     * @param string $instanceId
     */
    public function __construct(Config $config, string $instanceId)
    {
        $this->__parentConstruct($config, $instanceId);
    }

    /**
     * @return bool
     */
    public function driverCheck(): bool
    {
        return \is_writable($this->getPath()) || @\mkdir($this->getPath(), $this->getDefaultChmod(), true);
    }

    /**
     * @return bool
     */
    protected function driverConnect(): bool
    {
        return true;
    }

    /**
     * @param \Psr\Cache\CacheItemInterface $item
     * @return null|array
     */
    protected function driverRead(CacheItemInterface $item)
    {
        /**
         * Check for Cross-Driver type confusion
         */
        $file_path = $this->getFilePath($item->getKey(), true);
        if (!\file_exists($file_path)) {
            return null;
        }

        $content = $this->readfile($file_path);

        return $this->decode($content);

    }

    /**
     * @param \Psr\Cache\CacheItemInterface $item
     * @return bool
     * @throws PhpfastcacheInvalidArgumentException
     */
    protected function driverWrite(CacheItemInterface $item): bool
    {
        /**
         * Check for Cross-Driver type confusion
         */
        if ($item instanceof Item) {
            $file_path = $this->getFilePath($item->getKey());
            $data = $this->encode($this->driverPreWrap($item));

            /**
             * Force write
             */
            try {
                return $this->writefile($file_path, $data, $this->getConfig()->isSecureFileManipulation());
            } catch (\Exception $e) {
                return false;
            }
        }

        throw new PhpfastcacheInvalidArgumentException('Cross-Driver type confusion detected');
    }

    /**
     * @param \Psr\Cache\CacheItemInterface $item
     * @return bool
     * @throws PhpfastcacheInvalidArgumentException
     */
    protected function driverDelete(CacheItemInterface $item): bool
    {
        /**
         * Check for Cross-Driver type confusion
         */
        if ($item instanceof Item) {
            $file_path = $this->getFilePath($item->getKey(), true);
            if (\file_exists($file_path) && @\unlink($file_path)) {
                \clearstatcache(true, $file_path);
                $dir = \dirname($file_path);
                if (!(new \FilesystemIterator($dir))->valid()) {
                    \rmdir($dir);
                }
                return true;
            }

            return false;
        }

        throw new PhpfastcacheInvalidArgumentException('Cross-Driver type confusion detected');
    }

    /**
     * @return bool
     */
    protected function driverClear(): bool
    {
        return Directory::rrmdir($this->getPath(true));
    }
}
