using System;
using System.Net.Sockets;
using System.Net;
using System.Text.RegularExpressions;
using System.IO;
using System.Text;
using System.Diagnostics;

namespace PhpWinSize
{
    class Program
    {
        static void Main(string[] args)
        {
            int port, localPort;
            try
            {
                port = Convert.ToInt32(args[0]);
                localPort = Convert.ToInt32(args[1]);
            }
            catch (Exception e)
            {
                return;
            }
            Send(Process.GetCurrentProcess().Id.ToString(), port);

            UdpClient receiver = new UdpClient(localPort);
            IPEndPoint remote_ip = null;

            byte[] data;
            string message, result;
            string[] a;

            while (true)
            {
                data = receiver.Receive(ref remote_ip);

                message = Encoding.UTF8.GetString(data);
                a = message.Split(' ');
                port = Convert.ToInt32(a[0]);
                result = Console.WindowWidth + "\n" + Console.WindowHeight;
                Send(result, port);
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
    }
}
