<?php

namespace Application;

use Application\Exceptions\ResourceException;

/**
 * Данный класс используется для загрузки и сохранения ресурсов приложения. Все ресурсы приложения должны находиться в папке исходных файлов проекта "resources".
 */
final class Resource
{
    /**
     * Возвращает содержимое ресурса
     *
     * @param string $pathToResource Путь к ресурсу из директории "resources"
     * @return string Содержимое ресурса
     * @throws ResourceException Ресурс не существует или является директорией
     */
    public static function GetResource(string $pathToResource) : string
    {}

    /**
     * Возвращает внешний путь ресурса
     *
     * @param string $pathToResource
     * @return string
     * @throws ResourceException
     */
    public static function GetExternalPathToResource(string $pathToResource) : string
    {}

    /**
     * Сохраняет ресурс в указанную директорию
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
     * Сохраняет директорию ресурсов в указанную внешнюю директорию
     *
     * @param string $pathToResourcesDirectory
     * @param string|null $savePath
     * @return void
     * @throws ResourceException
     */
    public static function SaveResourcesDirectory(string $pathToResourcesDirectory, ?string $savePath = null) : void
    {}
}