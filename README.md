# xRefCoreCompiler
Compiler of xRefCore

This program packs your application into PHAR with using xRefCore. You should not place xRefCore files in the project folder. It is enough to have the file '/Program/Main.php' in project directory. xRefCoreCompiler will pack the application itself.<br><br>

1. Run application<br>
2. Input path to your project. Project directory must contain at least `/Program/Main.php`. Or you can put application to project directory and start. xRefCoreCompiler will try to detected your project.<br>
3. Fill data about application. Then it's recommended to save application config to project directory<br>
4. Your PHAR-application will be saved to project directory