package pt.ipleiria.estg.dei.amsi.cinelive.helpers;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;

public class BilheteDBHelper {
    // region Columns
    public static final String TABLE_NAME = "bilhete";
    public static final String ID = "id";

    public static final String COMPRA_ID = "compra_id";
    public static final String CODIGO = "codigo";
    public static final String LUGAR = "lugar";
    public static final String PRECO = "preco";
    public static final String ESTADO = "estado";
    // endregion

    private final DBHelper dbHelper;

    public BilheteDBHelper(Context context) {
        dbHelper = new DBHelper(context);
    }

    // region CRUD
    public void saveBilhetes(int compraId, List<Bilhete> bilhetes) {
        SQLiteDatabase db = dbHelper.getWritableDatabase();
        db.delete(TABLE_NAME, COMPRA_ID + " = ?", new String[]{String.valueOf(compraId)});

        for (Bilhete b : bilhetes) {
            ContentValues values = new ContentValues();
            values.put(ID, b.getId());
            values.put(COMPRA_ID, compraId);
            values.put(CODIGO, b.getCodigo());
            values.put(LUGAR, b.getLugar());
            values.put(PRECO, b.getPreco());
            values.put(ESTADO, b.getEstado());

            db.insert(TABLE_NAME, null, values);
        }

        db.close();
    }

    public List<Bilhete> getBilhetesByCompraId(int compraId) {
        List<Bilhete> list = new ArrayList<>();
        SQLiteDatabase db = dbHelper.getReadableDatabase();

        Cursor cursor = db.query(TABLE_NAME, null, COMPRA_ID + " = ?",
            new String[]{String.valueOf(compraId)}, null, null, null
        );

        while (cursor.moveToNext()) {
            list.add(new Bilhete(
                cursor.getInt(cursor.getColumnIndexOrThrow(ID)),
                compraId,
                cursor.getString(cursor.getColumnIndexOrThrow(CODIGO)),
                cursor.getString(cursor.getColumnIndexOrThrow(LUGAR)),
                cursor.getString(cursor.getColumnIndexOrThrow(PRECO)),
                cursor.getString(cursor.getColumnIndexOrThrow(ESTADO))
            ));
        }

        cursor.close();
        db.close();
        return list;
    }

    public boolean hasBilhetes(int compraId) {
        SQLiteDatabase db = dbHelper.getReadableDatabase();

        Cursor cursor = db.rawQuery("SELECT 1 FROM " + TABLE_NAME + " WHERE " + COMPRA_ID + " = ? LIMIT 1",
            new String[]{String.valueOf(compraId)}
        );

        boolean exists = cursor.moveToFirst();

        cursor.close();
        db.close();
        return exists;
    }
    // endregion
}
