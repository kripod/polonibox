using Newtonsoft.Json;

namespace PoloniexTrollboxDataDecoder
{
    public class TrollboxMessage
    {
        [JsonProperty("username")]
        public string SenderName { get; internal set; }
        [JsonProperty("reputation")]
        public ulong? SenderReputation { get; internal set; }

        [JsonProperty("date")]
        public string MessageDate { get; internal set; }
        [JsonProperty("message")]
        public string MessageText { get; internal set; }
    }
}
