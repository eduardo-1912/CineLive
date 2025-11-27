package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
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
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ItemLugarBinding;

public class ComprarBilhetesActivity extends AppCompatActivity {

    private ActivityComprarBilhetesBinding binding;
    private final List<String> lugaresSelecionados = new ArrayList<>();
    private double precoBilhete, total;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityComprarBilhetesBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets sb = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(sb.left, sb.top, sb.right, sb.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.comprar_bilhetes);

        // Obter dados da sessão/filme
        Intent intent = getIntent();
        int idSessao = intent.getIntExtra("sessao_id", -1);

        // Preencher dados da sessão
        binding.tvTitulo.setText(intent.getStringExtra("titulo"));
        binding.tvRating.setText(intent.getStringExtra("rating"));
        binding.tvDuracao.setText(intent.getStringExtra("duracao"));

        // TODO: REPLACE MOCK DATA
        binding.tvNomeCinema.setText("CineLive Leiria");
        binding.tvNomeSala.setText("Sala 3");
        binding.tvDataSessao.setText("29/11/2025");
        binding.tvHorario.setText("10:00 - 12:32");

        // TODO: REPLACE MOCK DATA
        precoBilhete = 8;
        int numFilas = 10;
        int numColunas = 12;
        List<String> ocupados = Arrays.asList("A5", "A6", "A7", "C3", "E4");

        // Mapa de lugares
        gerarMapaLugares(numFilas, numColunas, ocupados);
        atualizarResumo();

        // Botão Pagar
        binding.btnPagar.setOnClickListener(v -> {
            // TODO: DO SOMETHING
        });
    }

    private void gerarMapaLugares(int numFilas, int numColunas, List<String> ocupados) {

        // Criar fila
        for (int i = 0; i < numFilas; i++) {
            TableRow fila = new TableRow(this);
            char letra = (char)('A' + i);

            // Criar lugar da fila
            for (int col = 1; col <= numColunas; col++) {

                // Inflate do item_lugar.xml
                ItemLugarBinding lugarBinding = ItemLugarBinding.inflate(getLayoutInflater(), fila, false);

                // Atribuir valor ao lugar
                String lugar = letra + String.valueOf(col);
                lugarBinding.btnLugar.setText(lugar);

                // Bloquear lugares ocupados
                if (ocupados.contains(lugar)) {
                    lugarBinding.btnLugar.setEnabled(false);
                    lugarBinding.btnLugar.setChecked(false);
                }

                // Atualizar seleção de lugares e resumo
                else {
                    lugarBinding.btnLugar.setOnClickListener(v -> {
                        if (lugarBinding.btnLugar.isChecked()) {
                            lugaresSelecionados.add(lugar);
                        } else {
                            lugaresSelecionados.remove(lugar);
                        }

                        atualizarResumo();
                    });
                }

                // Adicionar o lugar à fila
                fila.addView(lugarBinding.getRoot());
            }

            // Adicionar fila à tabela
            binding.mapaLugares.addView(fila);
        }
    }

    private void atualizarResumo() {
        boolean hasLugares = !lugaresSelecionados.isEmpty();

        // Lugares
        binding.tvLugares.setText(hasLugares ? String.join(", ", lugaresSelecionados) : "-");

        // Total
        total = lugaresSelecionados.size() * precoBilhete;
        binding.tvTotal.setText(String.format("%.2f€", total));

        // Botão Pagar
        binding.btnPagar.setEnabled(hasLugares);
        binding.btnPagar.setText(hasLugares ? R.string.btn_pagar : R.string.btn_selecione_lugares);
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}
