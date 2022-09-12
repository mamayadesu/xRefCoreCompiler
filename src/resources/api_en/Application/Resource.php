<?php

namespace Application;

use Application\Exceptions\ResourceException;

/**
 * This class is using to load and save resources of application. All resources have to be in "resources" directory of application source files.
 */
final class Resource
{
    /**
     * Returns content of resource
     *
     * @param string $pathToResource Path to your resource from "resources" directory
     * @return string Content of resource
     * @throws ResourceException The resource does not exist or it is a directory
     */
    public static function GetResource(string $pathToResource) : string
    {}

    /**
     * Returns an external path of resource
     *
     * @param string $pathToResource
     * @return string
     * @throws ResourceException
     */
    public static function GetExternalPathToResource(string $pathToResource) : string
    {}

    /**
     * Saves resource to target directory
     *
     * @param string $pathToResource
     * @param string|null $savePath
     * @param string|null $newFilename
     * @return void
     * @throws ResourceException
     */
    public static function SaveResource(string $pathToResource, ?string $savePath = null, ?string $newFilename = null) : void
    {}

    /**
     * Saves whole directory to target external directory
     *
     * @param string $pathToResourcesDirectory
     * @param string|null $savePath
     * @return void
     * @throws ResourceException
     */
    public static function SaveResourcesDirectory(string $pathToResourcesDirectory, ?string $savePath = null) : void
    {}
}