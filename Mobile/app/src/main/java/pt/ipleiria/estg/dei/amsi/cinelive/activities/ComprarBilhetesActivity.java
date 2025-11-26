package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.graphics.Color;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TableLayout;
import android.widget.TableRow;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityComprarBilhetesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Lugar;

public class ComprarBilhetesActivity extends AppCompatActivity {

    ActivityComprarBilhetesBinding binding;
    private List<Lugar> lugaresSelecionados = new ArrayList<>();


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityComprarBilhetesBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setTitle(R.string.comprar_bilhetes);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        int numFilas = 10;
        int numColunas = 12;
        List<String> ocupados = Arrays.asList("A5", "A6", "A7", "C3", "E4");

        gerarMapaTableLayout(numFilas, numColunas, ocupados);
        atualizarResumo();


    }

    private void gerarMapaTableLayout(int numFilas, int numColunas, List<String> ocupados) {

        TableLayout tabela = binding.tableMapaLugares;
        tabela.removeAllViews(); // limpar antes

        for (int i = 0; i < numFilas; i++) {

            char letra = (char) ('A' + i);
            TableRow row = new TableRow(this);

            for (int col = 1; col <= numColunas; col++) {

                String codigo = letra + String.valueOf(col);

                Button btn = new Button(this);
                btn.setText(codigo);

                // tamanho fixo
                TableRow.LayoutParams params = new TableRow.LayoutParams(
                        110, // largura px
                        110  // altura px
                );
                params.setMargins(8, 8, 8, 8);
                btn.setLayoutParams(params);

                // Estado ocupado
                if (ocupados.contains(codigo)) {
                    btn.setBackgroundColor(Color.parseColor("#888888")); // cinzento
                    btn.setEnabled(false);
                }
                // Estado livre
                else {
                    btn.setBackgroundColor(com.google.android.material.R.attr.colorSurfaceContainer); // verde

                    btn.setOnClickListener(v -> {
                        alternarSelecionado(btn, codigo);
                        atualizarResumo();
                    });
                }

                row.addView(btn);
            }

            tabela.addView(row);
        }
    }


    private void alternarSelecionado(Button btn, String codigo) {

        boolean selecionado = btn.getTag() != null;

        if (selecionado) {
            btn.setTag(null);
            btn.setBackgroundColor(Color.parseColor("#4CAF50")); // verde
            removerLugarSelecionado(codigo);
        } else {
            btn.setTag("sel");
            btn.setBackgroundColor(Color.parseColor("#FFEB3B")); // amarelo
            adicionarLugarSelecionado(codigo);
        }
    }

    private void adicionarLugarSelecionado(String codigo) {
        lugaresSelecionados.add(new Lugar(
                String.valueOf(codigo.charAt(0)),
                Integer.parseInt(codigo.substring(1)),
                1
        ));
    }

    private void removerLugarSelecionado(String codigo) {
        lugaresSelecionados.removeIf(l ->
                (l.fila + l.numero).equals(codigo)
        );
    }


    private void atualizarResumo() {

        // gerar "A5, A6, A7"
        StringBuilder sb = new StringBuilder();
        double total = 0;

        for (Lugar l : lugaresSelecionados) {
            sb.append(l.fila).append(l.numero).append(", ");
            total += 4.00; // TODO: preço falso
        }

        String lista = sb.length() > 0 ? sb.substring(0, sb.length() - 2) : "-";

        binding.tvLugares.setText(lista);
        binding.tvTotal.setText(String.format("%.2f€", total)); //TODO: APENAS STRING

        binding.btnPagar.setEnabled(!lugaresSelecionados.isEmpty());
    }


    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}