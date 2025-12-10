package pt.ipleiria.estg.dei.amsi.cinelive.helpers;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

public class CompraDBHelper {
    // region Columns
    public static final String TABLE_NAME = "compra";
    public static final String ID = "id";
    public static final String DATA = "data";
    public static final String TOTAL = "total";
    public static final String ESTADO = "estado";
    public static final String PAGAMENTO = "pagamento";
    public static final String FILME_TITULO = "filme_titulo";
    public static final String CINEMA_NOME = "cinema_nome";
    public static final String SALA_NOME = "sala_nome";
    public static final String SESSAO_DATA = "sessao_data";
    public static final String SESSAO_HORA_INICIO = "sessao_hora_inicio";
    public static final String SESSAO_HORA_FIM = "sessao_hora_fim";
    public static final String LUGARES = "lugares";
    // endregion

    private final DBHelper dbHelper;

    public CompraDBHelper(Context context) {
        dbHelper = new DBHelper(context);
    }

    // region CRUD
    public void saveCompra(Compra compra) {
        SQLiteDatabase db = dbHelper.getWritableDatabase();
        ContentValues values = new ContentValues();

        values.put(ID, compra.getId());
        values.put(DATA, compra.getData());
        values.put(TOTAL, compra.getTotal());
        values.put(ESTADO, compra.getEstado());
        values.put(PAGAMENTO, compra.getPagamento());
        values.put(FILME_TITULO, compra.getTituloFilme());
        values.put(CINEMA_NOME, compra.getNomeCinema());
        values.put(SALA_NOME, compra.getNomeSala());
        values.put(SESSAO_DATA, compra.getDataSessao());
        values.put(SESSAO_HORA_INICIO, compra.getHoraInicioSessao());
        values.put(SESSAO_HORA_FIM, compra.getHoraFimSessao());
        values.put(LUGARES, compra.getLugares());

        db.insertWithOnConflict(TABLE_NAME, null, values, SQLiteDatabase.CONFLICT_REPLACE);
        db.close();
    }

    public void saveCompras(List<Compra> compras) {
        SQLiteDatabase db = dbHelper.getWritableDatabase();

        for (Compra compra : compras) {
            ContentValues values = new ContentValues();
            values.put(ID, compra.getId());
            values.put(DATA, compra.getData());
            values.put(TOTAL, compra.getTotal());
            values.put(ESTADO, compra.getEstado());
            values.put(PAGAMENTO, compra.getPagamento());
            values.put(FILME_TITULO, compra.getTituloFilme());
            values.put(CINEMA_NOME, compra.getNomeCinema());
            values.put(SALA_NOME, compra.getNomeSala());
            values.put(SESSAO_DATA, compra.getDataSessao());
            values.put(SESSAO_HORA_INICIO, compra.getHoraInicioSessao());
            values.put(SESSAO_HORA_FIM, compra.getHoraFimSessao());
            values.put(LUGARES, compra.getLugares());

            db.insertWithOnConflict(TABLE_NAME, null, values, SQLiteDatabase.CONFLICT_REPLACE);
        }

        db.close();
    }

    public List<Compra> getCompras() {
        List<Compra> compras = new ArrayList<>();
        SQLiteDatabase db = dbHelper.getReadableDatabase();

        Cursor cursor = db.query(TABLE_NAME, null, null,
            null,null, null, ID + " DESC");

        while (cursor.moveToNext()) {
            compras.add(new Compra(
                cursor.getInt(cursor.getColumnIndexOrThrow(ID)),
                cursor.getString(cursor.getColumnIndexOrThrow(DATA)),
                cursor.getString(cursor.getColumnIndexOrThrow(TOTAL)),
                cursor.getString(cursor.getColumnIndexOrThrow(ESTADO)),
                cursor.getString(cursor.getColumnIndexOrThrow(PAGAMENTO)),
                cursor.getString(cursor.getColumnIndexOrThrow(FILME_TITULO)),
                cursor.getString(cursor.getColumnIndexOrThrow(CINEMA_NOME)),
                cursor.getString(cursor.getColumnIndexOrThrow(SALA_NOME)),
                cursor.getString(cursor.getColumnIndexOrThrow(SESSAO_DATA)),
                cursor.getString(cursor.getColumnIndexOrThrow(SESSAO_HORA_INICIO)),
                cursor.getString(cursor.getColumnIndexOrThrow(SESSAO_HORA_FIM)),
                cursor.getString(cursor.getColumnIndexOrThrow(LUGARES))
            ));
        }

        cursor.close();
        db.close();
        return compras;
    }

    public void delete() {
        SQLiteDatabase db = dbHelper.getWritableDatabase();
        db.delete(TABLE_NAME, null, null);
        db.close();
    }
    // endregion
}
