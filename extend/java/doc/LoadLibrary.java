package java.doc;

import com.sun.jna.Native;

public class LoadLibrary {

    private static TdxLibrary tdxLibrary;

    /**
     * trade.dll
     *
     * @param url 文件位置
     * @return
     */
    public LoadLibrary(String url) {
        this.tdxLibrary = Native.loadLibrary(url, TdxLibrary.class);
    }

    public void OpenTdx() {
        tdxLibrary.OpenTdx();
    }

    public void CloseTdx() {
        tdxLibrary.CloseTdx();
    }

    public static String[] Logon(String IP, short Port, String Version, short YybID, String AccountNo, String TradeAccount, String JyPassword, String TxPassword) {
        byte[] ErrInfo = new byte[256];
        int ClientID = tdxLibrary.Logon(IP, Port, Version, YybID, AccountNo, TradeAccount, JyPassword, TxPassword, ErrInfo);
        String[] result = new String[2];
        result[0] = String.valueOf(ClientID);
        result[1] = Native.toString(ErrInfo, "GBK");
        return result;
    }

    public String[] QueryData(int ClientID, int Category) {
        byte[] Result = new byte[1024 * 1024];
        byte[] ErrInfo = new byte[256];
        tdxLibrary.QueryData(ClientID, Category, Result, ErrInfo);
        String[] result = new String[2];
        result[0] = Native.toString(Result, "GBK");
        result[1] = Native.toString(ErrInfo, "GBK");
        return result;
    }

    public String[] SendOrder(int ClientID, int Category, int PriceType, String Gddm, String Zqdm, float Price, int Quantity) {
        byte[] Result = new byte[1024 * 1024];
        byte[] ErrInfo = new byte[256];
        tdxLibrary.SendOrder(ClientID, Category, PriceType, Gddm, Zqdm, Price, Quantity, Result, ErrInfo);
        String[] result = new String[2];
        result[0] = Native.toString(Result, "GBK");
        result[1] = Native.toString(ErrInfo, "GBK");
        return result;
    }

    public String[] CancelOrder(int ClientID, String ExchangeID, String hth) {
        byte[] Result = new byte[1024 * 1024];
        byte[] ErrInfo = new byte[256];
        tdxLibrary.CancelOrder(ClientID, ExchangeID, hth, Result, ErrInfo);
        String[] result = new String[2];
        result[0] = Native.toString(Result, "GBK");
        result[1] = Native.toString(ErrInfo, "GBK");
        return result;
    }

    public String[] QueryHistoryData(int ClientID, int Category, String StartDate, String EndDate) {
        byte[] Result = new byte[1024 * 1024];
        byte[] ErrInfo = new byte[256];
        tdxLibrary.QueryHistoryData(ClientID, Category, StartDate, EndDate, Result, ErrInfo);
        String[] result = new String[2];
        result[0] = Native.toString(Result, "GBK");
        result[1] = Native.toString(ErrInfo, "GBK");
        return result;
    }

}
