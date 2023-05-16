<?php

namespace HttpServer;

class ServerEvents extends \Data\Enum
{
    const Start = "start";
    const Shutdown = "shutdown";
    const Throwable = "throwable";
    const Request = "request";
}