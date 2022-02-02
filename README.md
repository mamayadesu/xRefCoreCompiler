# xRefCoreCompiler
Compiler of xRefCore

This program packs your application into PHAR with using xRefCore. You should not download xRefCore, just use this application. It is enough to have 'app.json' and '/Program/Main.php' in project directory. xRefCoreCompiler will pack the application itself.<br><br>

1. Run application<br>
2. Input path to your project. Project directory must contain at least `/Program/Main.php`. Or you can put application to project directory and start. xRefCoreCompiler will try to detected your project.<br>
3. Fill data about application. Then it's recommended to save application config to project directory<br>
4. Your PHAR-application will be saved to project directory

<h3>Hints</h3>
* Use `make.cmd` to fast build. Put the file to your project and replace your path to xRefCoreCompiler.phar
* If you are using PHPStorm, it's recommended to copy `Core` folder or create a symbal link. Download `link.cmd` and put your path to `Core`
* It's recommended to use next project structure:<br>
<code>
— PHPStorm
 |
 |— ProjectName
   |— Core <b>(or symbal link)</b>
   |— ProjectName
     |— app.json
     |— Program
       |— Main.php
       |— <i>anything else...</i>
     |- make.cmd
   |— link.cmd
</code>