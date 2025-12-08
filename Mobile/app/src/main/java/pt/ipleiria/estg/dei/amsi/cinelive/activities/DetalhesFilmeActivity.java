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
import java.util.List;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityDetalhesFilmeBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmeListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.SessoesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.FilmesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.SessoesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class DetalhesFilmeActivity extends AppCompatActivity {

    ActivityDetalhesFilmeBinding binding;
    PreferencesManager preferences;
    FilmesManager filmesManager;
    SessoesManager sessoesManager;

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

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);
            loadFilme();
        });
    }

    private void loadFilme() {
        binding.mainFlipper.setDisplayedChild(0); // Main Loading

        // Verificar se tem internet
        if (!ConnectionUtils.hasInternet(this)) {
            ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
            finish();
        }

        // Obter o filme à API
        filmesManager.getFilme(this, getIntent().getIntExtra("id", -1), new FilmeListener() {
            @Override
            public void onSuccess(Filme filme) {
                setFilme(filme);
                if (filme.hasSessoes()) loadSessoes(filme);
            }

            @Override
            public void onError() {
                Toast.makeText(DetalhesFilmeActivity.this, R.string.msg_erro_carregar_filme, Toast.LENGTH_SHORT).show();
                finish();
            }
        });
    }

    private void setFilme(Filme filme) {
        binding.mainFlipper.setDisplayedChild(1); // Main Content

        // Atualizar title da toolbar
        getSupportActionBar().setTitle(filme.getTitulo());

        // Carregar o poster
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
        // Verificar se tem internet
        if (!ConnectionUtils.hasInternet(this)) {
            ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
            finish();
        }

        // Mostrar conteúdo
        binding.mainSessoes.setVisibility(View.VISIBLE);

        // Obter sessões à API
        sessoesManager.getSessoes(this, filme.getId(), new SessoesListener() {
            @Override
            public void onSuccess(Map<String, List<Sessao>> sessoesPorData) {
                // Lista de datas
                List<String> datas = new ArrayList<>(sessoesPorData.keySet());

                // Preencher o spinner com as datas
                binding.spinnerData.setAdapter(
                    new ArrayAdapter<>(DetalhesFilmeActivity.this, android.R.layout.simple_spinner_dropdown_item, datas)
                );

                // Se clicou numa data --> atualizar sessões
                binding.spinnerData.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                    @Override
                    public void onItemSelected(AdapterView<?> parent, View view, int posData, long id) {
                        // Verificar se tem internet
                        if (!ConnectionUtils.hasInternet(getApplicationContext())) {
                            ErrorUtils.showToast(getApplicationContext(), ErrorUtils.Type.NO_INTERNET);
                            finish();
                        }

                        // Obter as sessões da data selecionada
                        List<Sessao> sessoes = sessoesPorData.get(datas.get(posData));

                        // Obter as horas
                        List<String> horas = new ArrayList<>();
                        for (Sessao sessao : sessoes) horas.add(sessao.getHoraInicio());

                        // Colocar as horas na ListView
                        binding.lvHoras.setAdapter(new ArrayAdapter<>(DetalhesFilmeActivity.this, android.R.layout.simple_list_item_1, horas));

                        // Se clicou numa hora --> ir para essa sessão
                        binding.lvHoras.setOnItemClickListener((p, viewHora, posHora, idSessao) -> {
                            // Obter a sessão correspondente
                            Sessao sessao = sessoes.get(posHora);

                            // Ir para comprar bilhetes
                            Intent intent = new Intent(DetalhesFilmeActivity.this, ComprarBilhetesActivity.class);
                            intent.putExtra("id", sessao.getId());
                            intent.putExtra("titulo", filme.getTitulo());
                            intent.putExtra("rating", filme.getRating());
                            intent.putExtra("duracao", filme.getDuracao());
                            startActivityForResult(intent, 1);
                        });
                    }
                    @Override
                    public void onNothingSelected(AdapterView<?> parent) {}
                });
            }

            @Override
            public void onError() {
                Toast.makeText(DetalhesFilmeActivity.this, R.string.msg_erro_carregar_sessoes, Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == 1 && resultCode == RESULT_OK) {
            finish();
        }
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}