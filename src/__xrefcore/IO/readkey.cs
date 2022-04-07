using System;
using System.Text;
using System.Net.Sockets;

namespace PhpReadkey
{
    class Program
    {
        static void Main(string[] args)
        {
            int port;
            try
            {
                port = Convert.ToInt32(args[0]);
            }
            catch (Exception e)
            {
                return;
            }
            ConsoleKey key = Console.ReadKey(true).Key;
            string pressedKey = key.ToString();

            Byte[] sendBytes = Encoding.ASCII.GetBytes(pressedKey);
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
