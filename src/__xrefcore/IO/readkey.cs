using System;
using System.Text;
using System.Net.Sockets;
using System.Net;
using System.Text.RegularExpressions;
using System.IO;
using System.Threading;
using System.Diagnostics;

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
            int port, localPort;
            bool debugEnabled = false;
            try
            {
                port = Convert.ToInt32(args[0]);
                localPort = Convert.ToInt32(args[1]);
            }
            catch (Exception e)
            {
                return;
            }

            try
            {
                debugEnabled = args[2] == "debug";
            }
            catch (Exception e)
            {
                debugEnabled = false;
            }

            Send(Process.GetCurrentProcess().Id.ToString(), port);

            UdpClient receiver = new UdpClient(localPort);
            receiver.Client.ReceiveTimeout = 250;
            IPEndPoint remote_ip = null;

            byte[] data;
            string message, result;
            string[] a;

            int previousPort = 0;
            while (true)
            {
                if (debugEnabled)
                {
                    Console.WriteLine("##### START #####");
                }
                if (debugEnabled)
                {
                    Console.WriteLine("Receiving");
                }
                try
                {
                    data = receiver.Receive(ref remote_ip);
                }
                catch (System.Net.Sockets.SocketException e)
                {
                    if (debugEnabled)
                    {
                        Console.WriteLine("Failed to receive.");
                    }
                    Input(new string[2] { previousPort.ToString(), "1" });
                    Send("", port);
                    continue;
                }
                if (debugEnabled)
                {
                    Console.WriteLine("Received!");
                }

                message = Encoding.UTF8.GetString(data);
                if (debugEnabled)
                {
                    Console.WriteLine("message: " + message);
                }
                a = message.Split(' ');
                port = Convert.ToInt32(a[0]);
                if (previousPort == 0)
                {
                    previousPort = port;
                }
                result = Input(a);

                if (debugEnabled)
                {
                    Console.WriteLine("Sending '" + result + "'");
                }

                Send(result, port);
                if (debugEnabled)
                {
                    Console.WriteLine("###### END ######");
                }
            }
        }

        private static void Send(string data, int port)
        {
            Byte[] sendBytes;
            try
            {
                sendBytes = Encoding.UTF8.GetBytes(data);
            }
            catch (Exception e)
            {
                Console.WriteLine("Failed to get bytes. " + e.ToString());
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
