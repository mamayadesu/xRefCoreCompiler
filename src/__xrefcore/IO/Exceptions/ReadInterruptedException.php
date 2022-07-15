<?php

namespace IO\Console\Exceptions;

/**
 * Throws by Console::ReadLine() and Console::ReadKey() if Console::InterruptRead() was called
 */
class ReadInterruptedException extends \Exception
{

}