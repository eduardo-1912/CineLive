package pt.ipleiria.estg.dei.amsi.cinelive.helpers;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

import java.util.ArrayList;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

public class CompraDBHelper extends SQLiteOpenHelper {
    private static final String DB_NAME = "cinelive.db";
    private static final int DB_VERSION = 1;

    private static final String TABLE_NAME = "compra";

    private static final String ID = "id";
    private static final String DATA = "data";
    private static final String TOTAL = "total";
    private static final String ESTADO = "estado";
    private static final String FILME_TITULO = "filme_titulo";
    private static final String CINEMA_NOME = "cinema_nome";
    private static final String SALA_NOME = "sala_nome";
    private static final String SESSAO_DATA = "sessao_data";
    private static final String SESSAO_HORA_INICIO = "sessao_hora_inicio";
    private static final String SESSAO_HORA_FIM = "sessao_hora_fim";
    private static final String LUGARES = "lugares";

    public CompraDBHelper (Context context) {
        super(context, DB_NAME, null, DB_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        String sql = "CREATE TABLE " + TABLE_NAME + " (" +
                ID + " INTEGER PRIMARY KEY, " +
                DATA + " TEXT, " +
                TOTAL + " TEXT, " +
                ESTADO + " TEXT, " +
                FILME_TITULO + " TEXT, " +
                CINEMA_NOME + " TEXT, " +
                SALA_NOME + " TEXT, " +
                SESSAO_DATA + " TEXT, " +
                SESSAO_HORA_INICIO + " TEXT, " +
                SESSAO_HORA_FIM + " TEXT, " +
                LUGARES + " TEXT" +
                ");";
        db.execSQL(sql);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_NAME);
        onCreate(db);
    }

    // CRUD #region
    public void addCompra(Compra compra) {
        SQLiteDatabase db = this.getWritableDatabase();
        ContentValues values = new ContentValues();

        values.put(ID, compra.getId());
        values.put(DATA, compra.getDataCompra());
        values.put(TOTAL, compra.getTotal());
        values.put(ESTADO, compra.getEstado());
        values.put(FILME_TITULO, compra.getTituloFilme());
        values.put(CINEMA_NOME, compra.getNomeCinema());
        values.put(SALA_NOME, compra.getNomeSala());
        values.put(SESSAO_DATA, compra.getDataSessao());
        values.put(SESSAO_HORA_INICIO, compra.getHoraInicioSessao());
        values.put(SESSAO_HORA_FIM, compra.getHoraFimSessao());
        values.put(LUGARES, compra.getLugares());

        db.insertWithOnConflict(TABLE_NAME, null, values, SQLiteDatabase.CONFLICT_REPLACE);
    }

    public void deleteAllCompras() {
        SQLiteDatabase db = this.getWritableDatabase();
        db.delete(TABLE_NAME, null, null);
    }

    public ArrayList<Compra> getAllCompras() {
        ArrayList<Compra> compras = new ArrayList<>();
        SQLiteDatabase db = this.getReadableDatabase();

        Cursor cursor = db.rawQuery("SELECT * FROM " + TABLE_NAME + " ORDER BY id DESC", null);

        if (cursor.moveToFirst()) {
            do {
                compras.add(new Compra(
                    cursor.getInt(cursor.getColumnIndexOrThrow(ID)),
                    cursor.getString(cursor.getColumnIndexOrThrow(FILME_TITULO)),
                    cursor.getString(cursor.getColumnIndexOrThrow(DATA)),
                    cursor.getString(cursor.getColumnIndexOrThrow(CINEMA_NOME)),
                    cursor.getString(cursor.getColumnIndexOrThrow(SALA_NOME)),
                    cursor.getString(cursor.getColumnIndexOrThrow(ESTADO)),
                    cursor.getString(cursor.getColumnIndexOrThrow(TOTAL)),
                    cursor.getString(cursor.getColumnIndexOrThrow(SESSAO_DATA)),
                    cursor.getString(cursor.getColumnIndexOrThrow(SESSAO_HORA_INICIO)),
                    cursor.getString(cursor.getColumnIndexOrThrow(SESSAO_HORA_FIM)),
                    cursor.getString(cursor.getColumnIndexOrThrow(LUGARES))
                ));
            }
            while (cursor.moveToNext());
        }

        cursor.close();
        return compras;
    }
    // endregion
}
