package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TableRow;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.google.android.material.dialog.MaterialAlertDialogBuilder;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityComprarBilhetesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ItemLugarBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.SessaoListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.DataManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class ComprarBilhetesActivity extends AppCompatActivity {

    private ActivityComprarBilhetesBinding binding;
    private DataManager manager;
    int sessaoId;
    private final List<String> lugaresSelecionados = new ArrayList<>();
    private double precoBilhete, total;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        // Obter o manager
        manager = DataManager.getInstance();

        // Verificar se tem sessão iniciada
        if (!manager.isLoggedIn(this)) {
            ErrorUtils.showToast(this, ErrorUtils.Type.INVALID_LOGIN);
            finish();
        }

        binding = ActivityComprarBilhetesBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets sb = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(sb.left, sb.top, sb.right, sb.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.title_comprar_bilhetes);

        // Obter Intent
        Intent intent = getIntent();
        sessaoId = intent.getIntExtra("sessaoId", -1);

        // Preencher dados do filme
        binding.tvTitulo.setText(intent.getStringExtra("tituloFilme"));
        binding.tvRating.setText(intent.getStringExtra("ratingFilme"));
        binding.tvDuracao.setText(intent.getStringExtra("duracaoFilme"));

        // Carregar a sessão
        loadSessao();

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);
            if (!ConnectionUtils.hasInternet(this)) {
                ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
                return;
            }

            loadSessao();
        });
    }

    private void loadSessao() {
        binding.mainFlipper.setDisplayedChild(0); // Main Loading

        // Verificar se tem internet
        if (!ConnectionUtils.hasInternet(this)) {
            ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
            finish();
        }

        // Obter sessão da API
        manager.getSessao(this, sessaoId, new SessaoListener() {
            @Override
            public void onSuccess(Sessao sessao) {
                setSessao(sessao);
            }

            @Override
            public void onError() {
                Toast.makeText(getApplicationContext(), R.string.msg_erro_carregar_sessao, Toast.LENGTH_SHORT).show();
                finish();
            }
        });
    }

    private void setSessao(Sessao sessao) {
        binding.mainFlipper.setDisplayedChild(1); // Main Content

        // Preencher campos
        binding.tvNomeCinema.setText(sessao.getNomeCinema());
        binding.tvNomeSala.setText(sessao.getNomeSala());
        binding.tvDataSessao.setText(sessao.getData());
        binding.tvHoraInicio.setText(sessao.getHoraInicio());
        binding.tvHoraFim.setText(sessao.getHoraFim());
        precoBilhete = sessao.getPrecoBilhete();

        // Mapa de lugares
        binding.mapaLugares.removeAllViews();
        lugaresSelecionados.clear();
        gerarMapaLugares(sessao.getNumFilas(), sessao.getNumColunas(), sessao.getLugaresOcupados());
        atualizarResumo();

        // Pagar
        binding.btnPagar.setOnClickListener(v -> {
            String[] options = {
                getString(R.string.label_cartao),
                getString(R.string.label_mbway),
                getString(R.string.label_multibanco)
            };
            String[] metodos = {"cartao", "mbway", "multibanco"};

            // Modal de pagamento
            new MaterialAlertDialogBuilder(this).setTitle(R.string.title_escolha_pagamento)
                .setItems(options, (dialog, which) -> {
                    // Enviar pedido de compra à API
                    createCompra(new Compra(sessaoId, metodos[which], lugaresSelecionados));
                }
            ).setNegativeButton(R.string.btn_cancelar, null).show();
        });
    }

    private void gerarMapaLugares(int numFilas, int numColunas, List<String> lugaresOcupados) {
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
                if (lugaresOcupados.contains(lugar)) {
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

    private void createCompra(Compra compra) {
        manager.createCompra(this, compra, new StandardListener() {
            @Override
            public void onSuccess() {
                Toast.makeText(getApplicationContext(), R.string.msg_sucesso_criar_compra, Toast.LENGTH_SHORT).show();
                manager.clearCacheCompras();
                setResult(RESULT_OK);
                finish();
            }

            @Override
            public void onError() {
                Toast.makeText(getApplicationContext(), R.string.msg_erro_criar_compra, Toast.LENGTH_LONG).show();
            }
        });
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}
