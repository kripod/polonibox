using Jojatekok.PoloniexAPI;
using MySql.Data.MySqlClient;
using System;
using System.Globalization;
using System.Threading;

#if DEBUG
using System.IO;
#endif

namespace PoloniBox
{
    class Program
    {
#if !DEBUG
        private static readonly MySqlConnection MySqlConnection = new MySqlConnection(
            "Server=localhost;" +
            "Database=polonibox;" +
            "Uid=root;" +
            "Pwd=root;" +
            "CharSet=utf8mb4;" +
            "UseCompression=true;"
        );
#endif

        private static readonly CultureInfo InvariantCulture = CultureInfo.InvariantCulture;
        private static readonly string NewLineString = Environment.NewLine;

        private static string _unexecutedCommands = "";
        private static string UnexecutedCommands {
            get { return _unexecutedCommands; }
            set { _unexecutedCommands = value; }
        }

        static void Main()
        {
            var poloniexClient = new PoloniexClient("", "");
            poloniexClient.Live.OnTrollboxMessage += PoloniexClient_OnTrollboxMessage;

#if !DEBUG
            Console.Out.WriteLine("Connecting to the MySQL DB...");
            MySqlConnection.Open();
#endif
            Console.Out.WriteLine("Connecting to the Poloniex trollbox...");
            poloniexClient.Live.Start();
            poloniexClient.Live.SubscribeToTrollboxAsync();
            Console.Out.WriteLine("Startup successful!");

            // Wait infinitely
            while (true) {
                using (var manualResetEvent = new ManualResetEvent(false)) {
                    manualResetEvent.WaitOne();
                }
            }
        }

        static void PoloniexClient_OnTrollboxMessage(object sender, TrollboxMessageEventArgs e)
        {
            var queryString = string.Format(
                InvariantCulture,
                "REPLACE INTO trollboxData VALUES ({0}, {1}, {2}, {3}, {4});",
                e.MessageNumber,
                "'" + DateTime.UtcNow.ToString("yyyy-MM-dd HH:mm:ss") + "'",
                "'" + MySqlHelper.EscapeString(e.MessageText) + "'",
                "'" + MySqlHelper.EscapeString(e.SenderName) + "'",
                e.SenderReputation as object ?? "NULL"
            );

#if !DEBUG
            try {
                using (var command = new MySqlCommand(UnexecutedCommands + queryString, MySqlConnection)) {
                    Console.Out.WriteLine("Executing MySQL command:" + NewLineString + command.CommandText);
                    command.ExecuteNonQuery();
                }
                UnexecutedCommands = "";

            } catch (Exception ex) {
                Console.Error.WriteLine("MySQL command could not be executed:" + NewLineString + ex);
                UnexecutedCommands += NewLineString + queryString;

                // Reconnect to the MySQL DB
                MySqlConnection.Close();
                MySqlConnection.Open();
            }
#else
            using (var writer = new StreamWriter("UnexecutedCommands.txt", true)) {
                writer.WriteLine(queryString);
            }
#endif
        }
    }
}
