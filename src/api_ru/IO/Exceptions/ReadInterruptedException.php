<?php

namespace IO\Console\Exceptions;

/**
 * Console::ReadLine() и Console::ReadKey() выбрасывают данное исключение, если Console::InterruptRead() был вызван
 */
class ReadInterruptedException extends \Exception
{

}