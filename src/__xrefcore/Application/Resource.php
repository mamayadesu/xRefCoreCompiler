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
    {
        try
        {
            $realPath = self::GetExternalPathToResource($pathToResource);
        }
        catch (ResourceException $exception)
        {
            // Recreating exception to change stack trace
            $e = new ResourceException($exception->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }

        return file_get_contents($realPath);
    }

    /**
     * Returns an external path of resource
     *
     * @param string $pathToResource
     * @return string
     * @throws ResourceException
     */
    public static function GetExternalPathToResource(string $pathToResource) : string
    {
        $pathToResource = str_replace("\\", "/", $pathToResource);

        $pathToResourceSplit = explode("/", $pathToResource);
        foreach ($pathToResourceSplit as $item)
        {
            if ($item == "." || $item == "..")
            {
                $e = new ResourceException("'.' and '..' are not allowed");
                $e->__xrefcoreexception = true;
                throw $e;
            }
        }

        $realPath = "phar://" . str_replace("\\", "/", Application::GetExecutableFileName()) . "/resources/" . $pathToResource;

        if (!file_exists($realPath))
        {
            $e = new ResourceException("The required resource does not exist.");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        if (is_dir($realPath))
        {
            $e = new ResourceException("The required resource is directory.");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        return $realPath;
    }

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
    {
        if ($savePath === null)
        {
            $savePath = Application::GetExecutableDirectory();
        }

        try
        {
            $realPath = self::GetExternalPathToResource($pathToResource);
        }
        catch (ResourceException $exception)
        {
            // Recreating exception to change stack trace
            $e = new ResourceException($exception->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $filename = $newFilename === null ? basename($pathToResource) : $newFilename;
        @copy($realPath, $savePath . $filename);
    }

    /**
     * Saves whole directory to target external directory
     *
     * @param string $pathToResourcesDirectory
     * @param string|null $savePath
     * @return void
     * @throws ResourceException
     */
    public static function SaveResourcesDirectory(string $pathToResourcesDirectory, ?string $savePath = null) : void
    {
        $pathToResourcesDirectory = str_replace("\\", "/", $pathToResourcesDirectory);
        $pathToResourcesDirectorySplit = explode("/", $pathToResourcesDirectory);
        foreach ($pathToResourcesDirectorySplit as $item)
        {
            if ($item == "." || $item == "..")
            {
                $e = new ResourceException("'.' and '..' are not allowed");
                $e->__xrefcoreexception = true;
                throw $e;
            }
        }
        if ($savePath === null)
        {
            $savePath = Application::GetExecutableDirectory();
        }

        if (!in_array(substr($savePath, -1), ["/", "\\"]))
        {
            $savePath .= DIRECTORY_SEPARATOR;
        }
        $executableFilename = str_replace("\\", "/", Application::GetExecutableFileName());
        $realPath = "phar://" . $executableFilename . "/resources/" . $pathToResourcesDirectory;

        if (!file_exists($realPath))
        {
            $e = new ResourceException("The required resource directory '" . $realPath . "' does not exist.");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        if (!is_dir($realPath))
        {
            $e = new ResourceException("The required resource is file.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $dirname = basename($pathToResourcesDirectory);
        @mkdir($savePath);
        $d = dir($realPath);
        while (($entry = $d->read()) != false)
        {
            if ($entry == "." || $entry == "..")
            {
                continue;
            }
            if (is_file($realPath . "/" . $entry))
            {
                copy($realPath . "/" . $entry, $savePath . $entry);
            }
            else
            {
                self::SaveResourcesDirectory($pathToResourcesDirectory . "/" . $entry, $savePath . $entry);
            }
        }
        $d->close();
    }
}