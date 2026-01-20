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
import pt.ipleiria.estg.dei.amsi.cinelive.managers.DataManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class DetalhesFilmeActivity extends AppCompatActivity {

    ActivityDetalhesFilmeBinding binding;
    PreferencesManager preferences;
    DataManager manager;

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
        getSupportActionBar().setTitle(R.string.title_detalhes_filme);

        // Obter o manager
        manager = DataManager.getInstance(getApplicationContext());

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Carregar o filme
        loadFilme();

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);
            if (!ConnectionUtils.hasInternet(this)) {
                ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
                return;
            }

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
        manager.getFilme(this, getIntent().getIntExtra("filmeId", -1), new FilmeListener() {
            @Override
            public void onSuccess(Filme filme) {
                setFilme(filme);

                // Carregar sessões se filme estiver em exibição
                if (filme.hasSessoes()) loadSessoes(filme);
                else binding.mainFlipper.setDisplayedChild(1); // Main Content
            }

            @Override
            public void onError() {
                Toast.makeText(getApplicationContext(), R.string.msg_erro_carregar_filme, Toast.LENGTH_SHORT).show();
                finish();
            }
        });
    }

    private void setFilme(Filme filme) {
        // Carregar o poster
        Glide.with(getApplicationContext())
            .load(preferences.getApiHost() + filme.getPosterUrl())
            .placeholder(R.drawable.poster_placeholder)
            .diskCacheStrategy(DiskCacheStrategy.ALL)
            .into(binding.ivPoster);

        // Preencher dados
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

        // Obter sessões à API
        manager.getSessoes(this, filme.getId(), new SessoesListener() {
            @Override
            public void onSuccess(Map<String, List<Sessao>> sessoesPorData) {
                // Lista de datas
                List<String> datas = new ArrayList<>(sessoesPorData.keySet());

                // Configurar o adapter com as datas das sessões
                binding.spinnerData.setAdapter(new ArrayAdapter<>(
                    getApplicationContext(), android.R.layout.simple_spinner_dropdown_item, datas
                ));

                binding.mainSessoes.setVisibility(View.VISIBLE);
                binding.mainFlipper.setDisplayedChild(1); // Main Content

                // Se clicou numa data --> atualizar sessões
                setOnDataSelectedListener(filme, datas, sessoesPorData);
            }

            @Override
            public void onError() {
                Toast.makeText(getApplicationContext(), R.string.msg_erro_carregar_sessoes, Toast.LENGTH_SHORT).show();
                binding.mainFlipper.setDisplayedChild(1); // Main Content
            }
        });
    }

    private void setOnDataSelectedListener(Filme filme, List<String> datas, Map<String, List<Sessao>> sessoesPorData) {
        binding.spinnerData.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int posData, long id) {
                // Obter as sessões da data selecionada
                List<Sessao> sessoes = sessoesPorData.get(datas.get(posData));

                // Obter as horas
                List<String> horas = new ArrayList<>();
                for (Sessao sessao : sessoes) horas.add(sessao.getHoraInicio());

                // Configurar o adapter com as horas
                binding.lvHoras.setAdapter(new ArrayAdapter<>(getApplicationContext(), android.R.layout.simple_list_item_1, horas));

                // Se clicou numa hora --> ir para a sessão da hora
               setOnHoraSelectedListener(filme, sessoes);
            }
            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
    }

    private void setOnHoraSelectedListener(Filme filme, List<Sessao> sessoes) {
        binding.lvHoras.setOnItemClickListener((p, viewHora, posHora, idSessao) -> {
            // Verificar se tem internet
            if (!ConnectionUtils.hasInternet(getApplicationContext())) {
                ErrorUtils.showToast(getApplicationContext(), ErrorUtils.Type.NO_INTERNET);
                return;
            }

            // Verificar se tem sessão iniciada
            if (!manager.isLoggedIn(this)) {
                startActivity(new Intent(getApplicationContext(), LoginActivity.class));
                return;
            }

            // Obter a sessão correspondente
            Sessao sessao = sessoes.get(posHora);

            // Ir para comprar bilhetes
            Intent intent = new Intent(getApplicationContext(), ComprarBilhetesActivity.class);
            intent.putExtra("sessaoId", sessao.getId());
            intent.putExtra("tituloFilme", filme.getTitulo());
            intent.putExtra("ratingFilme", filme.getRating());
            intent.putExtra("duracaoFilme", filme.getDuracao());
            startActivityForResult(intent, 1);
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        // O utilizador comprou bilhetes
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