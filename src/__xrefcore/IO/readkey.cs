using System;
using System.Text;
using System.Net.Sockets;
using System.Text.RegularExpressions;
using System.IO;
using System.Threading;

namespace PhpReadkey
{
    class Program
    {
        static string Input(string[] args)
        {
            Console.InputEncoding = Encoding.GetEncoding(866);
            int port = Convert.ToInt32(args[0]), action = Convert.ToInt32(args[1]);
            string result = "";
            switch (action)
            {
                case 1:
                    ConsoleKeyInfo key = Console.ReadKey(true);
                    if (RemoveWhitespaces(key.KeyChar.ToString()).Length < RemoveWhitespaces(key.Key.ToString()).Length)
                    {
                        string keyChar = RemoveWhitespaces(key.Key.ToString()).ToLower();
                        if (keyChar.ToLower() == "d0")
                        {
                            keyChar = "0";
                        }
                        else if (keyChar.Length == 2 && keyChar.Substring(0, 1).ToLower() == "d")
                        {
                            keyChar = keyChar.Replace("d", "");
                        }
                        else if (keyChar.ToLower() == "oem2")
                        {
                            keyChar = "divide";
                        }
                        else if (keyChar.ToLower() == "d8")
                        {
                            keyChar = "multiply";
                        }
                        keyChar = keyChar.Replace("oem", "");
                        result = keyChar;
                    }
                    else
                    {
                        result = key.KeyChar.ToString();
                    }
                    break;

                case 2:
                    result = GetHiddenConsoleInput(true);
                    break;

                case 3:
                    result = Console.ReadLine();
                    break;
            }
            return result;
        }

        static void Main(string[] args)
        {
            int port, action;
            try
            {
                port = Convert.ToInt32(args[0]);
                action = Convert.ToInt32(args[1]);
            }
            catch (Exception e)
            {
                return;
            }

            string result = Input(args);
            Byte[] sendBytes;
            try
            {
                sendBytes = Encoding.UTF8.GetBytes(result);
            }
            catch (Exception e)
            {
                return;
            }
            try
            {
                (new UdpClient()).Send(sendBytes, sendBytes.Length, "127.0.0.1", port);
            }
            catch (Exception e)
            {
                Console.WriteLine(e.ToString());
            }
        }

        private static string RemoveWhitespaces(string str)
        {
            return Regex.Replace(str, @"\s+", "");
        }

        private static string GetHiddenConsoleInput(bool hideInput)
        {
            string input = "";
            while (true)
            {
                ConsoleKeyInfo key = Console.ReadKey(hideInput);
                if (key.Key == ConsoleKey.Enter) break;
                if (key.Key == ConsoleKey.Backspace && input.Length > 0) input = input.Substring(0, input.Length - 1);
                else if (key.Key != ConsoleKey.Backspace) input += key.KeyChar;
            }
            return input;
        }
    }
}
