# xRefCoreCompiler
Compiler of xRefCore

This program packs your application into PHAR with using xRefCore framework. You should not download xRefCore, just use this application. It is enough to have 'app.json' and '/Program/Main.php' in project directory. xRefCoreCompiler will pack the application itself.<br><br>

1. Run application<br>
2. Input path to your project. Project directory must contain at least `/Program/Main.php`. Or you can put application to project directory and start. xRefCoreCompiler will try to detected your project.<br>
3. Fill data about application. Then it's recommended to save application config to project directory<br>
4. Your PHAR-application will be saved to project directory<br>
<br>
# Installation
<h4>Linux</h4>
<code>wget -O - https://raw.githubusercontent.com/mamayadesu/xRefCoreCompiler/main/INSTALLATION%20SCRIPTS/install.sh | bash</code>
<h4>Windows (PowerShell only)</h4>
<i>You must run PowerShell as Administrator</i><br>
<code>iex ((New-Object System.Net.WebClient).DownloadString('https://raw.githubusercontent.com/mamayadesu/xRefCoreCompiler/main/INSTALLATION%20SCRIPTS/install.ps1'))</code>