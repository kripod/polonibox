using MySql.Data.MySqlClient;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Globalization;
using System.IO;
using System.Web;

namespace PoloniexTrollboxDataDecoder
{
    class Program
    {
        private static readonly MySqlConnection MySqlConnection = new MySqlConnection(
            "Server=localhost;" +
            "Database=polonibox;" +
            "Uid=root;" +
            "Pwd=root;" +
            "CharSet=utf8mb4;" +
            "UseCompression=true;"
        );

        private static readonly CultureInfo InvariantCulture = CultureInfo.InvariantCulture;

        static void Main()
        {
            Dictionary<ulong, TrollboxMessage> trollboxMessages;
            var jsonSerializer = new JsonSerializer();

            Console.WriteLine("Reading input...");

            using (var streamReader = new StreamReader("trollbox.json")) {
                using (var jsonTextReader = new JsonTextReader(streamReader)) {
                    trollboxMessages = jsonSerializer.Deserialize<Dictionary<ulong, TrollboxMessage>>(jsonTextReader);
                }
            }

            Console.WriteLine("Uploading output to the MySQL server...");

            var queryString = string.Empty;
            var queryLines = 0;

            MySqlConnection.Open();
            foreach (var messageContainer in trollboxMessages) {
                var message = messageContainer.Value;
                if (message.SenderName == "Banhammer") {
                    message.SenderReputation = null;
                }

                queryString += string.Format(
                    InvariantCulture,
                    "REPLACE INTO trollboxData VALUES ({0}, {1}, {2}, {3}, {4});",
                    messageContainer.Key,
                    "'" + message.MessageDate + "'",
                    "'" + MySqlHelper.EscapeString(HttpUtility.HtmlDecode(message.MessageText)) + "'",
                    "'" + MySqlHelper.EscapeString(message.SenderName) + "'",
                    message.SenderReputation as object ?? "NULL"
                );

                queryLines += 1;

                if (queryLines >= 10000) {
                    using (var command = new MySqlCommand(queryString, MySqlConnection)) {
                        command.ExecuteNonQuery();
                    }

                    queryString = string.Empty;
                    queryLines = 0;
                }
            }

            if (queryString.Length != 0) {
                using (var command = new MySqlCommand(queryString, MySqlConnection)) {
                    command.ExecuteNonQuery();
                }
            }
        }
    }
}
