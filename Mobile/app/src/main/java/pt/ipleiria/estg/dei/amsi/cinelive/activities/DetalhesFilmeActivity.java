package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityDetalhesFilmeBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmeListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.SessaoListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.FilmesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.SessoesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class DetalhesFilmeActivity extends AppCompatActivity {

    ActivityDetalhesFilmeBinding binding;
    PreferencesManager preferences;
    FilmesManager filmesManager;
    SessoesManager sessoesManager;
    Sessao sessaoSelecionada;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityDetalhesFilmeBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // Obter o filmes manager
        filmesManager = FilmesManager.getInstance();

        // Obter o sessões manager
        sessoesManager = SessoesManager.getInstance();

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Carregar o filme
        loadFilme();
    }

    private void loadFilme() {
        filmesManager.getFilme(this, getIntent().getIntExtra("id", -1), new FilmeListener() {
            @Override
            public void onSuccess(Filme filme) {
                setFilme(filme);
                loadSessoes(filme);
            }

            @Override
            public void onError() {
                Toast.makeText(DetalhesFilmeActivity.this, "erro", Toast.LENGTH_SHORT).show();
                finish();
            }
        });

    }

    private void setFilme(Filme filme) {
        getSupportActionBar().setTitle(filme.getTitulo());

        // Carregar Poster
        Glide.with(DetalhesFilmeActivity.this)
                .load(preferences.getApiHost() + filme.getPosterUrl())
                .placeholder(R.drawable.poster_placeholder)
                .diskCacheStrategy(DiskCacheStrategy.ALL)
                .into(binding.ivPoster);

        // Dados do filmes
        binding.tvTitulo.setText(filme.getTitulo());
        binding.tvRating.setText(filme.getRating());
        binding.tvGeneros.setText(filme.getGeneros());
        binding.tvEstreia.setText(filme.getEstreia());
        binding.tvDuracao.setText(filme.getDuracao());
        binding.tvIdioma.setText(filme.getIdioma());
        binding.tvRealizacao.setText(filme.getRealizacao());
        binding.tvSinopse.setText(filme.getSinopse());
    }

    private void loadSessoes(Filme filme) {
        sessoesManager.getSessoes(this, filme.getId(), new SessaoListener() {
            @Override
            public void onSuccess(Map<String, List<Sessao>> sessoesPorData) {

                List<String> datas = new ArrayList<>(sessoesPorData.keySet());

                // Preenche spinner com datas reais
                binding.spinnerData.setAdapter(
                        new ArrayAdapter<>(DetalhesFilmeActivity.this,
                                android.R.layout.simple_spinner_dropdown_item,
                                datas)
                );

                binding.spinnerData.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                    @Override
                    public void onItemSelected(AdapterView<?> parent, View view, int pos, long id) {

                        String data = datas.get(pos);
                        List<Sessao> sessoes = sessoesPorData.get(data);

                        List<String> horas = new ArrayList<>();
                        for (Sessao s : sessoes) horas.add(s.getHoraInicio());

                        binding.lvHoras.setAdapter(new ArrayAdapter<>(DetalhesFilmeActivity.this,
                                android.R.layout.simple_list_item_1, horas));

                        binding.lvHoras.setOnItemClickListener((p, v2, pos2, i2) -> {

                            Sessao sessao = sessoes.get(pos2);

                            Intent intent = new Intent(DetalhesFilmeActivity.this,
                                    ComprarBilhetesActivity.class);

                            intent.putExtra("sessao_id", sessao.getId());
                            startActivity(intent);
                        });
                    }

                    @Override
                    public void onNothingSelected(AdapterView<?> parent) {}
                });
            }

            @Override
            public void onError() {
                Toast.makeText(DetalhesFilmeActivity.this, "Erro ao carregar sessões", Toast.LENGTH_SHORT).show();
            }
        });

    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}