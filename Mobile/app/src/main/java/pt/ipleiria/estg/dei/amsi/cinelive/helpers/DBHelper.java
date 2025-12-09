package pt.ipleiria.estg.dei.amsi.cinelive.helpers;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

public class DBHelper extends SQLiteOpenHelper {
    private static final String DB_NAME = "cinelive.db";
    private static final int DB_VERSION = 1;

    public DBHelper(Context context) {
        super(context, DB_NAME, null, DB_VERSION);
    }

    @Override
    public void onConfigure(SQLiteDatabase db) {
        super.onConfigure(db);
        db.setForeignKeyConstraintsEnabled(true);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        String compra = "CREATE TABLE " + CompraDBHelper.TABLE_NAME + " (" +
            CompraDBHelper.ID + " INTEGER PRIMARY KEY, " +
            CompraDBHelper.DATA + " TEXT, " +
            CompraDBHelper.TOTAL + " TEXT, " +
            CompraDBHelper.ESTADO + " TEXT, " +
            CompraDBHelper.PAGAMENTO + " TEXT, " +
            CompraDBHelper.FILME_TITULO + " TEXT, " +
            CompraDBHelper.CINEMA_NOME + " TEXT, " +
            CompraDBHelper.SALA_NOME + " TEXT, " +
            CompraDBHelper.SESSAO_DATA + " TEXT, " +
            CompraDBHelper.SESSAO_HORA_INICIO + " TEXT, " +
            CompraDBHelper.SESSAO_HORA_FIM + " TEXT, " +
            CompraDBHelper.LUGARES + " TEXT" +
        ");";

        String bilhete = "CREATE TABLE " + BilheteDBHelper.TABLE_NAME + " (" +
            BilheteDBHelper.ID + " INTEGER PRIMARY KEY, " +
            BilheteDBHelper.COMPRA_ID + " INTEGER, " +
            BilheteDBHelper.CODIGO + " TEXT, " +
            BilheteDBHelper.LUGAR + " TEXT, " +
            BilheteDBHelper.PRECO + " TEXT, " +
            BilheteDBHelper.ESTADO + " TEXT, " +
            "FOREIGN KEY(" + BilheteDBHelper.COMPRA_ID + ") REFERENCES " +
            CompraDBHelper.TABLE_NAME + "(" + CompraDBHelper.ID + ") ON DELETE CASCADE" +
        ");";

        db.execSQL(compra);
        db.execSQL(bilhete);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL("DROP TABLE IF EXISTS " + CompraDBHelper.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + BilheteDBHelper.TABLE_NAME);
        onCreate(db);
    }
}
