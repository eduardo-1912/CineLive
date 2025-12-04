package pt.ipleiria.estg.dei.amsi.cinelive.helpers;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

import java.util.ArrayList;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;

public class BilheteDBHelper extends SQLiteOpenHelper {
    private static final String DB_NAME = "cinelive.db";
    private static final int DB_VERSION = 1;

    private static final String TABLE_NAME = "bilhete";

    private static final String ID = "id";
    private static final String COMPRA_ID = "compra_id";
    private static final String CODIGO = "codigo";
    private static final String LUGAR = "lugar";
    private static final String PRECO = "preco";
    private static final String ESTADO = "estado";

    public BilheteDBHelper(Context context) {
        super(context, DB_NAME, null, DB_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        String sql = "CREATE TABLE " + TABLE_NAME + " (" +
                ID + " INTEGER PRIMARY KEY, " +
                COMPRA_ID + " INTEGER REFERENCES compra(id), " +
                CODIGO + " TEXT, " +
                LUGAR + " TEXT, " +
                PRECO + " TEXT, " +
                ESTADO + " TEXT" +
                ");";
        db.execSQL(sql);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_NAME);
        onCreate(db);
    }

    // CRUD #region
    public void addBilhete(Bilhete bilhete) {
        SQLiteDatabase db = this.getWritableDatabase();
        ContentValues values = new ContentValues();

        values.put(ID, bilhete.getId());
        values.put(COMPRA_ID, bilhete.getCompraId());
        values.put(CODIGO, bilhete.getCodigo());
        values.put(LUGAR, bilhete.getLugar());
        values.put(PRECO, bilhete.getPreco());
        values.put(ESTADO, bilhete.getEstado());

        db.insertWithOnConflict(TABLE_NAME, null, values, SQLiteDatabase.CONFLICT_REPLACE);
    }

    public void deleteBilhetesPorCompra(int compraId) {
        SQLiteDatabase db = this.getWritableDatabase();
        db.delete(TABLE_NAME, COMPRA_ID + " = ?", new String[]{String.valueOf(compraId)});
    }

    public void deleteAllBilhetes() {
        SQLiteDatabase db = this.getWritableDatabase();
        db.delete(TABLE_NAME, null, null);
    }

    public ArrayList<Bilhete> getBilhetesPorCompraId(int compraId) {
        ArrayList<Bilhete> bilhetes = new ArrayList<>();
        SQLiteDatabase db = this.getReadableDatabase();

        Cursor cursor = db.rawQuery(
                "SELECT * FROM " + TABLE_NAME + " WHERE " + COMPRA_ID + " = ?",
                new String[]{String.valueOf(compraId)}
        );

        if (cursor.moveToFirst()) {
            do {
                bilhetes.add(new Bilhete(
                        cursor.getInt(cursor.getColumnIndexOrThrow(ID)),
                        cursor.getInt(cursor.getColumnIndexOrThrow(COMPRA_ID)),
                        cursor.getString(cursor.getColumnIndexOrThrow(CODIGO)),
                        cursor.getString(cursor.getColumnIndexOrThrow(LUGAR)),
                        cursor.getString(cursor.getColumnIndexOrThrow(PRECO)),
                        cursor.getString(cursor.getColumnIndexOrThrow(ESTADO))
                ));
            }
            while (cursor.moveToNext());
        }

        cursor.close();
        return bilhetes;
    }
    // endregion
}
