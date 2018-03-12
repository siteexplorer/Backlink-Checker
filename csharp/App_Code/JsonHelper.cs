namespace System
{
    using System;
    using System.Collections;
    using System.Collections.Generic;
    using System.Collections.Specialized;
    using System.Web;
    using System.Web.UI;
    using System.Web.UI.WebControls;
    using System.IO;
    using System.Data;
    using System.Data.SqlClient;
    using System.Text;
    using System.Text.RegularExpressions;
    using System.Net;

    public enum JsonType
    {
        String = 0,
        Object = 1,
        Array = 2
    }
    public class NameJson
    {
        public string Name;
        public Json Json;
        public NameJson(string n, Json j)
        {
            Name = n;
            Json = j;
        }
        public override string ToString()
        {
            return Json.ToString();
        }
    }
    public class JsonCollection : NameObjectCollectionBase
    {
        public JsonCollection()
        {
        }
        public String[] AllKeys
        {
            get
            {
                return (this.BaseGetAllKeys());
            }
        }
        public Json this[String key]
        {
            get
            {
                return (Json)(this.BaseGet(key));
            }
            set
            {
                this.BaseSet(key, value);
            }
        }
        public NameJson this[int index]
        {
            get
            {
                return (new NameJson(this.BaseGetKey(index), (Json)this.BaseGet(index)));
            }
        }
        public void Add(String key, Json value)
        {
            this.BaseAdd(key, value);
        }
    }
    public class Json
    {
        JsonType type_ = JsonType.String;
        object data_ = null;
        JsonCollection JData
        {
            get { return (JsonCollection)data_; }
        }
        public JsonType Type
        {
            get
            {
                return type_;
            }
        }
        public string Value
        {
            get
            {
                return (string)data_;
            }
        }
        public Int32 Count
        {
            get
            {
                return ((JsonCollection)data_).Count;
            }
        }
        public String[] Names
        {
            get
            {
                String[] items = new String[Count];
                for (int i = 0; i < Count; i++)
                {
                    items[i] = this[i].Name;
                }
                return items;
            }
        }
        public Json[] Jsons
        {
            get
            {
                Json[] items = new Json[Count];
                for (int i = 0; i < Count; i++)
                {
                    items[i] = this[i].Json;
                }
                return items;
            }
        }
        public NameJson[] Items
        {
            get
            {
                NameJson[] items = new NameJson[Count];
                for (int i = 0; i < Count; i++)
                {
                    items[i] = this[i];
                }
                return items;
            }
        }
        static string J(string s)
        {
            s = s.Replace("\\", "\\\\");
            s = s.Replace("/", "\\/");
            s = s.Replace("\"", "\\\"");
            s = s.Replace("\n", "\\n");
            s = s.Replace("\r", "\\r");
            s = s.Replace("\t", "\\t");
            s = s.Replace("\b", "\\b");
            s = s.Replace("\f", "\\f");
            return s;
        }
        public override string ToString()
        {
            return data_.ToString();
        }
        public string ToJsonString()
        {
            switch (type_)
            {
                case JsonType.Object:
                    {
                        StringBuilder sb = new StringBuilder();
                        sb.Append("{");
                        for (int i = 0; i < JData.Count; i++)
                        {
                            sb.Append("\"" + J(JData[i].Name) + "\":" + JData[i].Json.ToJsonString() + ",");
                        }
                        if (sb.Length > 1) sb.Length--;
                        sb.Append("}");
                        return sb.ToString();
                    }
                case JsonType.Array:
                    {
                        StringBuilder sb = new StringBuilder();
                        sb.Append("[");
                        for (int i = 0; i < JData.Count; i++)
                        {
                            sb.Append(JData[i].Json.ToJsonString() + ",");
                        }
                        if (sb.Length > 1) sb.Length--;
                        sb.Append("]");
                        return sb.ToString();
                    }
                default:
                    return "\"" + J((string)data_) + "\"";
            }
        }
        public NameJson this[int index]
        {
            get
            {
                return ((JsonCollection)data_)[index];
            }
        }
        public Json this[string name]
        {
            get
            {
                return ((JsonCollection)data_)[name];
            }
        }
        void Add(string name, Json data)
        {
            if (type_ == JsonType.Array)
            {
                if (name != null)
                {
                    throw new Exception("JsonType.Array: name is not null=>" + Trim(name) + this.ToJsonString());
                }
                name = ((JsonCollection)data_).Count.ToString();
                ((JsonCollection)data_).Add(name, data);
            }
            else
            {
                if (name == null)
                {
                    throw new Exception("JsonType.Object: name is  null." + this.ToJsonString());
                }
                ((JsonCollection)data_).Add(Trim(name), data);
            }
        }
        public Json()
        {
        }
        public Json(string value)
        {
            data_ = Trim(value);
        }
        static string Trim(string s)
        {
            if (s[0] == '\"')
            {
                return s.Trim(new char[] { '"' });
            }
            if (s[0] == '\'')
            {
                return s.Trim(new char[] { '"' });
            }
            return s;
        }
        static bool IsEnclosed(StringBuilder sb, int skip)
        {
            if (sb.Length == skip) return false;
            if (sb.Length > 0)
            {
                string s = sb.ToString().Trim();
                if (s.Length > 0)
                {
                    if (s[0] == '\"')
                    {
                        if (s.Length < 2) return false;
                        if (s[s.Length - 1] != '\"') return false;
                    }
                    if (s[0] == '\'')
                    {
                        if (s.Length < 2) return false;
                        if (s[s.Length - 1] != '\'') return false;
                    }
                }
            }
            return true;
        }
        static void Parse(Json json, string s, ref int idx)
        {
            string name = null;
            StringBuilder sb = new StringBuilder();
            int skip = -1;
            while (idx < s.Length)
            {
                if (s[idx] == ']' || s[idx] == '}')
                {
                    if (!IsEnclosed(sb, skip))
                    {
                        sb.Append(s[idx]);
                        idx++;
                        continue;
                    }
                    break;
                }
                switch (s[idx])
                {
                    case '[':
                        {
                            if (!IsEnclosed(sb, skip))
                            {
                                sb.Append(s[idx]);
                                break;
                            }
                            Json json_ = new Json();
                            json_.type_ = JsonType.Array;
                            json_.data_ = new JsonCollection();
                            ++idx;
                            Parse(json_, s, ref idx);
                            json.Add(name, json_);
                            name = null;
                        }
                        break;
                    case '{':
                        {
                            if (!IsEnclosed(sb, skip))
                            {
                                sb.Append(s[idx]);
                                break;
                            }
                            Json json_ = new Json();
                            json_.type_ = JsonType.Object;
                            json_.data_ = new JsonCollection();
                            ++idx;
                            Parse(json_, s, ref idx);
                            json.Add(name, json_);
                            name = null;
                        }
                        break;
                    case ',':
                        {
                            if (!IsEnclosed(sb, skip))
                            {
                                sb.Append(s[idx]);
                                break;
                            }
                            if (name != null)
                            {
                                json.Add(name, new Json(sb.ToString().Trim()));
                                name = null;
                            }
                            sb.Length = 0;
                            skip = -1;
                        }
                        break;
                    case ':':
                        if (!IsEnclosed(sb, skip))
                        {
                            sb.Append(s[idx]);
                            break;
                        }
                        if (name == null)
                        {
                            name = sb.ToString().Trim();
                            if (name.Length == 0)
                            {
                                name = null;
                                sb.Length = 0;
                                skip = -1;
                            }
                            else
                            {
                                if ((name[0] != '\'' && name[0] != '\"') ||
                                    (name[0] == '"' && name[name.Length - 1] == '"') ||
                                    (name[0] == '\'' && name[name.Length - 1] == '\''))
                                {
                                    sb.Length = 0;
                                    skip = -1;
                                }
                                else
                                {
                                    name = null;
                                    sb.Append(s[idx]);
                                }
                            }
                        }
                        else
                        {
                            sb.Append(s[idx]);
                        }
                        break;
                    case '\\':
                        switch (s[++idx])
                        {
                            case 'r':
                                sb.Append('\r');
                                break;
                            case 'n':
                                sb.Append('\n');
                                break;
                            case 't':
                                sb.Append('\t');
                                break;
                            case 'b':
                                sb.Append('\b');
                                break;
                            case 'f':
                                sb.Append('\f');
                                break;
                            case '\'':
                                sb.Append('\'');
                                skip = sb.Length;
                                break;
                            case '\"':
                                sb.Append('\"');
                                skip = sb.Length;
                                break;
                            default:
                                sb.Append(s[idx]);
                                break;
                        }
                        break;
                    default:
                        sb.Append(s[idx]);
                        break;
                }
                ++idx;
            }
            if (json.Type == JsonType.String)
            {
                if (name != null)
                {
                    json.data_ = sb.ToString();
                }
            }
            else
            {
                string val = sb.ToString().Trim();
                if (val.Length > 0 || name != null)
                {
                    json.Add(name, new Json(val));
                }
            }
        }
        public static Json Parse(string s)
        {
            Json json = new Json();
            json.type_ = JsonType.Object;
            json.data_ = new JsonCollection();
            int idx = s.IndexOf('{') + 1;
            Parse(json, s, ref idx);
            return json;
        }
    }
}

